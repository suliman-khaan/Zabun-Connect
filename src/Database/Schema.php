<?php

namespace ZabunConnect\Database;

defined( 'ABSPATH' ) || exit;

class Schema {

    /**
     * Table name without prefix.
     */
    public const TABLE_NAME = 'zabun_listings';

    /**
     * Get full table name with WordPress prefix.
     *
     * @return string
     */
    public static function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Create or update the database tables using dbDelta.
     */
    public static function create_tables(): void {
        global $wpdb;

        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            external_id varchar(100) NOT NULL,
            title varchar(255) NOT NULL,
            property_type varchar(100) DEFAULT NULL,
            status varchar(50) DEFAULT 'for_sale',
            price decimal(12,2) DEFAULT NULL,
            price_frequency varchar(20) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            postal_code varchar(20) DEFAULT NULL,
            address varchar(255) DEFAULT NULL,
            bedrooms int(11) DEFAULT 0,
            bathrooms int(11) DEFAULT 0,
            living_area decimal(10,2) DEFAULT NULL,
            land_area decimal(10,2) DEFAULT NULL,
            epc_value varchar(50) DEFAULT NULL,
            featured_image text DEFAULT NULL,
            gallery_images longtext DEFAULT NULL,
            raw_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY external_id (external_id),
            KEY status (status),
            KEY price (price),
            KEY city (city)
        ) {$charset_collate};";

        dbDelta( $sql );

        update_option( 'zabun_connect_db_version', ZABUN_CONNECT_DB_VERSION );
    }

    /**
     * Drop plugin tables (used during uninstall).
     */
    public static function drop_tables(): void {
        global $wpdb;
        $table_name = self::get_table_name();
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );
        delete_option( 'zabun_connect_db_version' );
    }
}
