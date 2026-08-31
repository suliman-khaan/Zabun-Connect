<?php

namespace ZabunConnect\Sync;

use ZabunConnect\Api\ZabunClient;
use ZabunConnect\Api\ZabunException;
use ZabunConnect\Database\Schema;
use ZabunConnect\I18n\I18n;

defined( 'ABSPATH' ) || exit;

class SyncListings {

    /**
     * Singleton instance.
     *
     * @var SyncListings|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return SyncListings
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register AJAX action for manual sync.
     */
    public function init(): void {
        add_action( 'wp_ajax_zabun_manual_sync', [ $this, 'handle_ajax_sync' ] );
    }

    /**
     * Handle AJAX manual sync request.
     */
    public function handle_ajax_sync(): void {
        check_ajax_referer( 'zabun_connect_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized access.', 'zabun-connect' ) ], 403 );
        }

        try {
            $stats = $this->sync();
            wp_send_json_success( [
                'message' => sprintf(
                    __( 'Sync completed successfully! Processed %d listings (Inserted: %d, Updated: %d).', 'zabun-connect' ),
                    $stats['total_fetched'],
                    $stats['inserted'],
                    $stats['updated']
                ),
                'stats'   => $stats,
            ] );
        } catch ( ZabunException $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'API Sync Error: %s', 'zabun-connect' ), $e->getMessage() ),
            ], 500 );
        } catch ( \Throwable $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'Sync Error: %s', 'zabun-connect' ), $e->getMessage() ),
            ], 500 );
        }
    }

    /**
     * Execute sync process across all pages from Zabun API.
     *
     * @param array|null $items Optional raw items to import (used for testing/direct payload).
     * @return array Sync statistics.
     * @throws ZabunException
     */
    public function sync( ?array $items = null ): array {
        global $wpdb;

        @set_time_limit( 300 );
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        }

        $table_name      = Schema::get_table_name();
        $sync_start_time = current_time( 'mysql' );
        $synced_ids      = [];
        $stats           = [
            'total_fetched' => 0,
            'inserted'      => 0,
            'updated'       => 0,
            'failed'        => 0,
        ];

        // If items are passed directly, process that array without API pagination
        if ( null !== $items ) {
            $stats['total_fetched'] = count( $items );
            $this->process_items_batch( $items, null, $table_name, $stats );
        } else {
            $client          = new ZabunClient();
            $page            = 0;
            $limit           = 100;
            $total_api_count = null;

            do {
                $response = $client->fetch_listings( [
                    'page'  => $page,
                    'limit' => $limit,
                ] );

                if ( null === $total_api_count ) {
                    if ( isset( $response['count'] ) && is_numeric( $response['count'] ) ) {
                        $total_api_count = (int) $response['count'];
                    } elseif ( isset( $response['total'] ) && is_numeric( $response['total'] ) ) {
                        $total_api_count = (int) $response['total'];
                    } elseif ( isset( $response['paging']['total'] ) && is_numeric( $response['paging']['total'] ) ) {
                        $total_api_count = (int) $response['paging']['total'];
                    }
                }

                $batch = isset( $response['data'] ) 
                    ? (array) $response['data'] 
                    : ( isset( $response['items'] ) ? (array) $response['items'] : ( isset( $response['properties'] ) ? (array) $response['properties'] : (array) $response ) );

                if ( empty( $batch ) ) {
                    break;
                }

                $batch_count = count( $batch );
                $stats['total_fetched'] += $batch_count;

                $this->process_items_batch( $batch, $client, $table_name, $stats, $synced_ids );

                $page++;

                // Stop if batch was smaller than limit or we have reached the total API count
                if ( $batch_count < $limit || ( null !== $total_api_count && $stats['total_fetched'] >= $total_api_count ) ) {
                    break;
                }

                // Safeguard against infinite loops (max 50 pages = 5,000 listings)
                if ( $page >= 50 ) {
                    break;
                }
            } while ( true );
        }

        // Clean up any stale records not present in this active sync feed
        if ( ! empty( $synced_ids ) ) {
            $existing_ids = (array) $wpdb->get_col( "SELECT external_id FROM {$table_name}" );
            $to_delete    = array_diff( $existing_ids, $synced_ids );
            if ( ! empty( $to_delete ) ) {
                $chunks = array_chunk( $to_delete, 100 );
                foreach ( $chunks as $chunk ) {
                    $placeholders = implode( ',', array_fill( 0, count( $chunk ), '%s' ) );
                    $wpdb->query(
                        $wpdb->prepare(
                            "DELETE FROM {$table_name} WHERE external_id IN ($placeholders)",
                            $chunk
                        )
                    );
                }
            }
        }

        update_option( 'zabun_connect_last_sync', current_time( 'mysql' ) );

        $total_cached = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        update_option( 'zabun_connect_cached_count', $total_cached );
        $stats['total_cached'] = $total_cached;

        return $stats;
    }

    /**
     * Process and upsert a batch of items into the database.
     *
     * @param array $batch
     * @param ZabunClient|null $client
     * @param string $table_name
     * @param array &$stats
     * @param array &$synced_ids
     */
    private function process_items_batch( array $batch, ?ZabunClient $client, string $table_name, array &$stats, array &$synced_ids = [] ): void {
        global $wpdb;

        foreach ( $batch as $raw_item ) {
            if ( ! is_array( $raw_item ) ) {
                continue;
            }

            $mapped = $this->map_item( $raw_item );
            if ( empty( $mapped['external_id'] ) ) {
                $stats['failed']++;
                continue;
            }

            $synced_ids[] = (string) $mapped['external_id'];

            $existing_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE external_id = %s",
                    $mapped['external_id']
                )
            );

            if ( $existing_id ) {
                $updated = $wpdb->update(
                    $table_name,
                    $mapped,
                    [ 'id' => $existing_id ]
                );

                if ( false !== $updated ) {
                    $stats['updated']++;
                } else {
                    $stats['failed']++;
                }
            } else {
                $inserted = $wpdb->insert(
                    $table_name,
                    $mapped
                );

                if ( false !== $inserted ) {
                    $stats['inserted']++;
                } else {
                    $stats['failed']++;
                }
            }
        }
    }

    /**
     * Extract string from multilingual array or string.
     *
     * @param mixed $val Value which may be string or ['nl'=>'...', 'en'=>'...', 'fr'=>'...'].
     * @param string $default
     * @return string
     */
    public function extract_multilingual_string( $val, string $default = '' ): string {
        return I18n::extract( $val, null, $default );
    }

    /**
     * Map raw API response item to database schema fields.
     *
     * @param array $raw
     * @return array
     */
    public function map_item( array $raw ): array {
        $external_id = (string) ( $raw['property_id'] ?? $raw['id'] ?? $raw['external_id'] ?? $raw['reference'] ?? '' );
        
        // Multilingual Title Resolution
        $title = $this->extract_multilingual_string( $raw['title'] ?? $raw['name'] ?? $raw['headline'] ?? null );
        
        // Property Type Resolution
        $type = $this->extract_multilingual_string(
            $raw['type_label'] ?? $raw['property_type'] ?? $raw['type_name'] ?? $raw['category'] ?? ( $raw['type'] ?? '' )
        );
        if ( empty( $type ) && ! empty( $raw['type_id'] ) ) {
            $type_map = [
                1 => 'House', 2 => 'Apartment', 3 => 'Villa', 4 => 'Office',
                5 => 'Commercial', 6 => 'Land', 22 => 'Warehouse'
            ];
            $type = $type_map[ (int) $raw['type_id'] ] ?? 'Property';
        }

        // Status & Transaction Type Resolution
        $status = 'for_sale';
        $trans_id = (int) ( $raw['transaction_id'] ?? 0 );
        $status_id = (int) ( $raw['status_id'] ?? 1 );

        if ( $trans_id === 2 || ( isset( $raw['transaction_type'] ) && stripos( $raw['transaction_type'], 'rent' ) !== false ) ) {
            $status = 'for_rent';
        } elseif ( $trans_id === 1 || ( isset( $raw['transaction_type'] ) && stripos( $raw['transaction_type'], 'sale' ) !== false ) ) {
            $status = 'for_sale';
        }

        if ( $status_id === 2 || ( isset( $raw['status'] ) && stripos( $raw['status'], 'sold' ) !== false ) ) {
            $status = 'sold';
        } elseif ( $status_id === 3 || ( isset( $raw['status'] ) && stripos( $raw['status'], 'rented' ) !== false ) ) {
            $status = 'rented';
        }

        // Price Resolution
        $price = null;
        if ( isset( $raw['price'] ) && is_numeric( $raw['price'] ) ) {
            $price = (float) $raw['price'];
        } elseif ( isset( $raw['financials']['price'] ) && is_numeric( $raw['financials']['price'] ) ) {
            $price = (float) $raw['financials']['price'];
        } elseif ( isset( $raw['sale_price'] ) && is_numeric( $raw['sale_price'] ) ) {
            $price = (float) $raw['sale_price'];
        } elseif ( isset( $raw['rent_price'] ) && is_numeric( $raw['rent_price'] ) ) {
            $price = (float) $raw['rent_price'];
        }

        $freq = (string) ( $raw['price_frequency'] ?? $raw['frequency'] ?? ( $status === 'for_rent' ? 'month' : '' ) );

        // Address & City Resolution
        $city = '';
        $postal_code = '';
        $address = '';

        if ( ! empty( $raw['address'] ) && is_array( $raw['address'] ) ) {
            $addr = $raw['address'];
            $city = (string) ( $addr['city_geo']['city'] ?? $addr['city_geo']['city_full'] ?? $addr['city'] ?? ( $addr['municipality'] ?? '' ) );
            $postal_code = (string) ( $addr['city_geo']['zip'] ?? $addr['postal_code'] ?? ( $addr['zip'] ?? '' ) );

            $street = $this->extract_multilingual_string( $addr['street_translated'] ?? $addr['street_lang'] ?? ( $addr['street'] ?? '' ) );
            $number = (string) ( $addr['number'] ?? '' );
            $box    = (string) ( $addr['box'] ?? '' );
            $address = trim( $street . ' ' . $number . ( ! empty( $box ) ? ' / ' . $box : '' ) );
        }

        if ( empty( $city ) ) {
            $city = (string) ( $raw['city'] ?? $raw['locality'] ?? $raw['municipality'] ?? '' );
        }
        if ( empty( $postal_code ) ) {
            $postal_code = (string) ( $raw['postal_code'] ?? $raw['zip'] ?? '' );
        }
        if ( empty( $address ) && is_string( $raw['address'] ?? null ) ) {
            $address = (string) $raw['address'];
        }

        // If title was empty, create a clean descriptive title from Type & Location or Reference
        if ( empty( $title ) ) {
            $title = ! empty( $type ) ? $type : __( 'Property', 'zabun-connect' );
            if ( ! empty( $city ) ) {
                $title .= ' in ' . $city;
            } elseif ( ! empty( $raw['reference'] ) ) {
                $title .= ' (' . $raw['reference'] . ')';
            }
        }

        // Rooms & Surface Area
        $bedrooms = (int) (
            $raw['bedrooms'] 
            ?? $raw['rooms']['bedrooms'] 
            ?? $raw['number_of_bedrooms'] 
            ?? $raw['nr_bedrooms'] 
            ?? $raw['nb_bedrooms'] 
            ?? $raw['slaapkamers'] 
            ?? $raw['aantal_slaapkamers'] 
            ?? 0
        );
        $bathrooms = (int) (
            $raw['bathrooms'] 
            ?? $raw['rooms']['bathrooms'] 
            ?? $raw['number_of_bathrooms'] 
            ?? $raw['nr_bathrooms'] 
            ?? $raw['nb_bathrooms'] 
            ?? $raw['badkamers'] 
            ?? $raw['aantal_badkamers'] 
            ?? 0
        );
        
        $living_area = null;
        if ( isset( $raw['living_area'] ) && is_numeric( $raw['living_area'] ) ) {
            $living_area = (float) $raw['living_area'];
        } elseif ( isset( $raw['surface_habitable'] ) && is_numeric( $raw['surface_habitable'] ) ) {
            $living_area = (float) $raw['surface_habitable'];
        } elseif ( isset( $raw['area_build'] ) && is_numeric( $raw['area_build'] ) && (float) $raw['area_build'] > 0 ) {
            $living_area = (float) $raw['area_build'];
        } elseif ( isset( $raw['surface'] ) && is_numeric( $raw['surface'] ) ) {
            $living_area = (float) $raw['surface'];
        }

        $land_area = null;
        if ( isset( $raw['area_ground'] ) && is_numeric( $raw['area_ground'] ) ) {
            $land_area = (float) $raw['area_ground'];
        } elseif ( isset( $raw['land_area'] ) && is_numeric( $raw['land_area'] ) ) {
            $land_area = (float) $raw['land_area'];
        } elseif ( isset( $raw['surface_terrain'] ) && is_numeric( $raw['surface_terrain'] ) ) {
            $land_area = (float) $raw['surface_terrain'];
        }

        // EPC Value
        $epc_value = (string) ( $raw['custom_epc_label'] ?? $raw['epc_value'] ?? $raw['epc'] ?? $raw['energy_label'] ?? '' );
        if ( empty( $epc_value ) && ! empty( $raw['epc_value_total'] ) ) {
            $epc_value = $raw['epc_value_total'] . ' kWh/m²';
        }

        // Images & Gallery Resolution
        $featured_image = '';
        $gallery = [];

        if ( ! empty( $raw['photo_url'] ) && is_string( $raw['photo_url'] ) ) {
            $featured_image = $raw['photo_url'];
            $gallery[]      = $raw['photo_url'];
        }

        $raw_media = $raw['pictures'] ?? $raw['images'] ?? $raw['photos'] ?? $raw['media'] ?? [];
        if ( is_array( $raw_media ) ) {
            foreach ( $raw_media as $m ) {
                $img_url = '';
                if ( is_string( $m ) ) {
                    $img_url = $m;
                } elseif ( is_array( $m ) ) {
                    $img_url = (string) ( $m['url'] ?? $m['media_url'] ?? $m['photo_url'] ?? ( $m['file_url'] ?? '' ) );
                }

                if ( ! empty( $img_url ) ) {
                    if ( ! in_array( $img_url, $gallery, true ) ) {
                        $gallery[] = $img_url;
                    }
                    if ( empty( $featured_image ) ) {
                        $featured_image = $img_url;
                    }
                }
            }
        }

        return [
            'external_id'    => sanitize_text_field( $external_id ),
            'title'          => sanitize_text_field( $title ),
            'property_type'  => sanitize_text_field( $type ),
            'status'         => sanitize_text_field( strtolower( $status ) ),
            'price'          => $price,
            'price_frequency'=> sanitize_text_field( $freq ),
            'city'           => sanitize_text_field( $city ),
            'postal_code'    => sanitize_text_field( $postal_code ),
            'address'        => sanitize_text_field( $address ),
            'bedrooms'       => $bedrooms,
            'bathrooms'      => $bathrooms,
            'living_area'    => $living_area,
            'land_area'      => $land_area,
            'epc_value'      => sanitize_text_field( $epc_value ),
            'featured_image' => esc_url_raw( $featured_image ),
            'gallery_images' => wp_json_encode( $gallery ),
            'raw_data'       => wp_json_encode( $raw ),
            'updated_at'     => current_time( 'mysql' ),
        ];
    }
}
