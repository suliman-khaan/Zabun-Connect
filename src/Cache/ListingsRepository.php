<?php

namespace ZabunConnect\Cache;

use ZabunConnect\Database\Schema;

defined( 'ABSPATH' ) || exit;

class ListingsRepository {

    /**
     * Singleton instance.
     *
     * @var ListingsRepository|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return ListingsRepository
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get filtered listings.
     *
     * @param array $args Query filters, sorting, and pagination.
     * @return array List of formatted property records.
     */
    public function get_listings( array $args = [] ): array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        list( $where_clauses, $params ) = $this->build_where_clauses( $args );

        $where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';

        // Ordering
        $allowed_orderby = [
            'date'        => 'created_at',
            'price'       => 'price',
            'title'       => 'title',
            'bedrooms'    => 'bedrooms',
            'living_area' => 'living_area',
            'id'          => 'id',
        ];

        $orderby_key = $args['orderby'] ?? 'date';
        $orderby_col = $allowed_orderby[ $orderby_key ] ?? 'created_at';
        $order_dir   = strtoupper( $args['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC';

        // Pagination
        $limit  = isset( $args['limit'] ) ? max( 1, min( 100, (int) $args['limit'] ) ) : 12;
        $page   = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
        $offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : ( ( $page - 1 ) * $limit );

        $query = "SELECT * FROM {$table_name} {$where_sql} ORDER BY {$orderby_col} {$order_dir} LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $results = $wpdb->get_results(
            $wpdb->prepare( $query, $params ),
            ARRAY_A
        );

        if ( empty( $results ) ) {
            return [];
        }

        return array_map( [ $this, 'format_record' ], $results );
    }

    /**
     * Count total listings matching the given filters (for pagination).
     *
     * @param array $args Filter arguments.
     * @return int Total number of matching records.
     */
    public function count_listings( array $args = [] ): int {
        global $wpdb;
        $table_name = Schema::get_table_name();

        list( $where_clauses, $params ) = $this->build_where_clauses( $args );
        $where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';

        if ( ! empty( $params ) ) {
            $query = "SELECT COUNT(*) FROM {$table_name} {$where_sql}";
            return (int) $wpdb->get_var( $wpdb->prepare( $query, $params ) );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} {$where_sql}" );
    }

    /**
     * Get a single listing by its database primary ID.
     *
     * @param int $id
     * @return array|null
     */
    public function get_listing_by_id( int $id ): ?array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d LIMIT 1", $id ),
            ARRAY_A
        );

        return $row ? $this->format_record( $row ) : null;
    }

    /**
     * Get a single listing by Zabun CRM external ID.
     *
     * @param string $external_id
     * @return array|null
     */
    public function get_listing_by_external_id( string $external_id ): ?array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE external_id = %s LIMIT 1", $external_id ),
            ARRAY_A
        );

        return $row ? $this->format_record( $row ) : null;
    }

    /**
     * Get distinct cities present in cached listings.
     *
     * @return array List of unique city names.
     */
    public function get_distinct_cities(): array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        $results = $wpdb->get_col(
            "SELECT DISTINCT city FROM {$table_name} WHERE city IS NOT NULL AND city != '' ORDER BY city ASC"
        );

        return is_array( $results ) ? $results : [];
    }

    /**
     * Get distinct property types present in cached listings.
     *
     * @return array List of unique property types.
     */
    public function get_distinct_types(): array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        $results = $wpdb->get_col(
            "SELECT DISTINCT property_type FROM {$table_name} WHERE property_type IS NOT NULL AND property_type != '' ORDER BY property_type ASC"
        );

        return is_array( $results ) ? $results : [];
    }

    /**
     * Get distinct statuses present in cached listings.
     *
     * @return array List of unique status names.
     */
    public function get_distinct_statuses(): array {
        global $wpdb;
        $table_name = Schema::get_table_name();

        $results = $wpdb->get_col(
            "SELECT DISTINCT status FROM {$table_name} WHERE status IS NOT NULL AND status != '' ORDER BY status ASC"
        );

        return is_array( $results ) ? $results : [];
    }

    /**
     * Build parameterized WHERE clauses and parameters from filters.
     *
     * @param array $args
     * @return array [ $where_clauses, $params ]
     */
    private function build_where_clauses( array $args ): array {
        $clauses = [];
        $params  = [];

        if ( ! empty( $args['status'] ) && $args['status'] !== 'all' ) {
            $clauses[] = 'status = %s';
            $params[]  = strtolower( sanitize_text_field( $args['status'] ) );
        }

        if ( ! empty( $args['property_type'] ) && $args['property_type'] !== 'all' ) {
            $clauses[] = 'property_type = %s';
            $params[]  = sanitize_text_field( $args['property_type'] );
        }

        if ( ! empty( $args['city'] ) && $args['city'] !== 'all' ) {
            $clauses[] = 'city = %s';
            $params[]  = sanitize_text_field( $args['city'] );
        }

        if ( isset( $args['min_price'] ) && is_numeric( $args['min_price'] ) && (float) $args['min_price'] > 0 ) {
            $clauses[] = 'price >= %f';
            $params[]  = (float) $args['min_price'];
        }

        if ( isset( $args['max_price'] ) && is_numeric( $args['max_price'] ) && (float) $args['max_price'] > 0 ) {
            $clauses[] = 'price <= %f';
            $params[]  = (float) $args['max_price'];
        }

        if ( isset( $args['bedrooms'] ) && is_numeric( $args['bedrooms'] ) && (int) $args['bedrooms'] > 0 ) {
            $clauses[] = 'bedrooms >= %d';
            $params[]  = (int) $args['bedrooms'];
        }

        if ( isset( $args['bathrooms'] ) && is_numeric( $args['bathrooms'] ) && (int) $args['bathrooms'] > 0 ) {
            $clauses[] = 'bathrooms >= %d';
            $params[]  = (int) $args['bathrooms'];
        }

        if ( ! empty( $args['search'] ) || ! empty( $args['keyword'] ) ) {
            $search_term = '%' . $wpdb_like_escaped = sanitize_text_field( $args['search'] ?? $args['keyword'] ) . '%';
            $clauses[]   = '(title LIKE %s OR address LIKE %s OR city LIKE %s OR postal_code LIKE %s)';
            $params[]    = $search_term;
            $params[]    = $search_term;
            $params[]    = $search_term;
            $params[]    = $search_term;
        }

        return [ $clauses, $params ];
    }

    /**
     * Unpack and format a database row into array structure.
     *
     * @param array $row
     * @return array
     */
    private function format_record( array $row ): array {
        $gallery = [];
        if ( ! empty( $row['gallery_images'] ) ) {
            $decoded = json_decode( $row['gallery_images'], true );
            $gallery = is_array( $decoded ) ? $decoded : [];
        }

        $raw_data = [];
        if ( ! empty( $row['raw_data'] ) ) {
            $decoded = json_decode( $row['raw_data'], true );
            $raw_data = is_array( $decoded ) ? $decoded : [];
        }

        $row['gallery_images'] = $gallery;
        $row['raw_data']       = $raw_data;
        $row['price']          = isset( $row['price'] ) ? (float) $row['price'] : null;
        $row['bedrooms']       = (int) ( $row['bedrooms'] ?? 0 );
        $row['bathrooms']      = (int) ( $row['bathrooms'] ?? 0 );
        $row['living_area']    = isset( $row['living_area'] ) ? (float) $row['living_area'] : null;
        $row['land_area']      = isset( $row['land_area'] ) ? (float) $row['land_area'] : null;

        return $row;
    }
}
