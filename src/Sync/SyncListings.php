<?php

namespace ZabunConnect\Sync;

use ZabunConnect\Api\ZabunClient;
use ZabunConnect\Api\ZabunException;
use ZabunConnect\Database\Schema;

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
     * Execute sync process.
     *
     * @param array|null $items Optional raw items to import (used for testing/direct payload).
     * @return array Sync statistics.
     * @throws ZabunException
     */
    public function sync( ?array $items = null ): array {
        global $wpdb;

        if ( null === $items ) {
            $client = new ZabunClient();
            $response = $client->fetch_listings();
            // Zabun API returns either array directly or envelope with data/items
            $items = isset( $response['data'] ) 
                ? (array) $response['data'] 
                : ( isset( $response['items'] ) ? (array) $response['items'] : ( isset( $response['properties'] ) ? (array) $response['properties'] : (array) $response ) );
        }

        $table_name = Schema::get_table_name();
        $stats      = [
            'total_fetched' => count( $items ),
            'inserted'      => 0,
            'updated'       => 0,
            'failed'        => 0,
        ];

        foreach ( $items as $raw_item ) {
            if ( ! is_array( $raw_item ) ) {
                continue;
            }

            $mapped = $this->map_item( $raw_item );
            if ( empty( $mapped['external_id'] ) ) {
                $stats['failed']++;
                continue;
            }

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

        update_option( 'zabun_connect_last_sync', current_time( 'mysql' ) );

        $total_cached = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        update_option( 'zabun_connect_cached_count', $total_cached );

        return $stats;
    }

    /**
     * Map raw API response item to database schema fields.
     *
     * @param array $raw
     * @return array
     */
    public function map_item( array $raw ): array {
        $external_id = (string) ( $raw['id'] ?? $raw['external_id'] ?? $raw['property_id'] ?? '' );
        $title       = (string) ( $raw['title'] ?? $raw['name'] ?? $raw['headline'] ?? 'Untitled Property' );
        $type        = (string) ( $raw['property_type'] ?? $raw['type'] ?? $raw['category'] ?? '' );
        $status      = (string) ( $raw['status'] ?? $raw['state'] ?? 'for_sale' );
        $price       = isset( $raw['price'] ) ? (float) $raw['price'] : ( isset( $raw['sale_price'] ) ? (float) $raw['sale_price'] : ( isset( $raw['rent_price'] ) ? (float) $raw['rent_price'] : null ) );
        $freq        = (string) ( $raw['price_frequency'] ?? $raw['frequency'] ?? '' );
        
        $city        = (string) ( $raw['city'] ?? $raw['locality'] ?? $raw['municipality'] ?? ( $raw['address']['city'] ?? '' ) );
        $postal_code = (string) ( $raw['postal_code'] ?? $raw['zip'] ?? $raw['postcode'] ?? ( $raw['address']['postal_code'] ?? '' ) );
        $address     = (string) ( $raw['address']['street'] ?? ( is_string( $raw['address'] ?? null ) ? $raw['address'] : '' ) );
        
        $bedrooms    = (int) ( $raw['bedrooms'] ?? $raw['rooms'] ?? $raw['number_of_bedrooms'] ?? 0 );
        $bathrooms   = (int) ( $raw['bathrooms'] ?? $raw['number_of_bathrooms'] ?? 0 );
        $living_area = isset( $raw['living_area'] ) ? (float) $raw['living_area'] : ( isset( $raw['surface'] ) ? (float) $raw['surface'] : ( isset( $raw['area'] ) ? (float) $raw['area'] : null ) );
        $land_area   = isset( $raw['land_area'] ) ? (float) $raw['land_area'] : ( isset( $raw['plot_surface'] ) ? (float) $raw['plot_surface'] : null );
        $epc_value   = (string) ( $raw['epc_value'] ?? $raw['epc'] ?? $raw['energy_label'] ?? '' );
        
        // Image resolution
        $featured_image = '';
        if ( ! empty( $raw['featured_image'] ) ) {
            $featured_image = is_string( $raw['featured_image'] ) ? $raw['featured_image'] : ( $raw['featured_image']['url'] ?? '' );
        } elseif ( ! empty( $raw['images'][0] ) ) {
            $featured_image = is_string( $raw['images'][0] ) ? $raw['images'][0] : ( $raw['images'][0]['url'] ?? '' );
        } elseif ( ! empty( $raw['photos'][0] ) ) {
            $featured_image = is_string( $raw['photos'][0] ) ? $raw['photos'][0] : ( $raw['photos'][0]['url'] ?? '' );
        }

        $gallery = [];
        $raw_images = $raw['images'] ?? $raw['photos'] ?? $raw['gallery'] ?? [];
        if ( is_array( $raw_images ) ) {
            foreach ( $raw_images as $img ) {
                if ( is_string( $img ) && ! empty( $img ) ) {
                    $gallery[] = $img;
                } elseif ( is_array( $img ) && ! empty( $img['url'] ) ) {
                    $gallery[] = $img['url'];
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
        ];
    }
}
