<?php

namespace ZabunConnect\Admin;

use ZabunConnect\Database\Schema;
use ZabunConnect\Sync\SyncListings;
use ZabunConnect\Api\ZabunClient;

defined( 'ABSPATH' ) || exit;

class SettingsPage {

    /**
     * Singleton instance.
     *
     * @var SettingsPage|null
     */
    private static $instance = null;

    /**
     * Settings page hook suffix.
     *
     * @var string
     */
    private $hook_suffix = '';

    /**
     * Get singleton instance.
     *
     * @return SettingsPage
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize settings page and subcomponents.
     */
    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Initialize AJAX handlers
        ConnectionTest::instance()->init();
        SyncListings::instance()->init();
    }

    /**
     * Register options page under Settings menu.
     */
    public function register_menu(): void {
        $this->hook_suffix = add_options_page(
            __( 'Zabun Connect Settings', 'zabun-connect' ),
            __( 'Zabun Connect', 'zabun-connect' ),
            'manage_options',
            'zabun-connect',
            [ $this, 'render_page' ]
        );
    }

    /**
     * Enqueue admin assets on our settings page only.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( string $hook ): void {
        if ( $hook !== $this->hook_suffix ) {
            return;
        }

        wp_enqueue_style(
            'zabun-connect-admin',
            ZABUN_CONNECT_URL . 'assets/css/admin-settings.css',
            [],
            ZABUN_CONNECT_VERSION
        );

        wp_enqueue_script(
            'zabun-connect-admin',
            ZABUN_CONNECT_URL . 'assets/js/admin-settings.js',
            [ 'jquery' ],
            ZABUN_CONNECT_VERSION,
            true
        );

        wp_localize_script(
            'zabun-connect-admin',
            'zabunConnectAdmin',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'zabun_connect_admin_nonce' ),
                'i18n'    => [
                    'testing'     => __( 'Testing connection...', 'zabun-connect' ),
                    'testSuccess' => __( 'Connection verified successfully!', 'zabun-connect' ),
                    'testError'   => __( 'Connection failed. Please check your credentials.', 'zabun-connect' ),
                    'syncing'     => __( 'Syncing listings from Zabun CRM...', 'zabun-connect' ),
                    'syncSuccess' => __( 'Listings synchronized successfully!', 'zabun-connect' ),
                    'syncError'   => __( 'Sync failed. Please check your logs.', 'zabun-connect' ),
                    'networkError'=> __( 'Request failed due to a network error.', 'zabun-connect' ),
                    'justNow'     => __( 'Just now', 'zabun-connect' ),
                ],
            ]
        );
    }

    /**
     * Register plugin settings with WordPress Settings API.
     */
    public function register_settings(): void {
        register_setting(
            'zabun_connect_settings',
            'zabun_connect_api_key',
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_secret' ],
                'default'           => '',
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_client_id',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_server_id',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_x_client_id',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_media_id',
            [
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 14,
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_base_url',
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_base_url' ],
                'default'           => 'https://gateway-cmsapi.v2.zabun.be',
            ]
        );

        register_setting(
            'zabun_connect_settings',
            'zabun_connect_sync_interval',
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_interval' ],
                'default'           => 'hourly',
            ]
        );

        // API Section
        add_settings_section(
            'zabun_api_section',
            __( 'Zabun CRM API Credentials', 'zabun-connect' ),
            function () {
                echo '<p>' . esc_html__( 'Enter your Zabun API authentication credentials. (Obtained from Zabun support upon activation).', 'zabun-connect' ) . '</p>';
            },
            'zabun-connect'
        );

        add_settings_field(
            'zabun_connect_api_key',
            __( 'API Key (api_key)', 'zabun-connect' ),
            [ $this, 'render_api_key_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        add_settings_field(
            'zabun_connect_client_id',
            __( 'Client ID (client_id)', 'zabun-connect' ),
            [ $this, 'render_client_id_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        add_settings_field(
            'zabun_connect_server_id',
            __( 'Server ID (server_id)', 'zabun-connect' ),
            [ $this, 'render_server_id_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        add_settings_field(
            'zabun_connect_x_client_id',
            __( 'Company ID (X-CLIENT-ID)', 'zabun-connect' ),
            [ $this, 'render_x_client_id_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        add_settings_field(
            'zabun_connect_media_id',
            __( 'Media ID', 'zabun-connect' ),
            [ $this, 'render_media_id_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        add_settings_field(
            'zabun_connect_base_url',
            __( 'Base URL', 'zabun-connect' ),
            [ $this, 'render_base_url_field' ],
            'zabun-connect',
            'zabun_api_section'
        );

        // Sync Section
        add_settings_section(
            'zabun_sync_section',
            __( 'Sync Schedule', 'zabun-connect' ),
            function () {
                echo '<p>' . esc_html__( 'Automated background synchronization interval for property listings.', 'zabun-connect' ) . '</p>';
            },
            'zabun-connect'
        );

        add_settings_field(
            'zabun_connect_sync_interval',
            __( 'Sync Frequency', 'zabun-connect' ),
            [ $this, 'render_sync_interval_field' ],
            'zabun-connect',
            'zabun_sync_section'
        );
    }

    /**
     * Sanitize secret callback.
     * Prevents overwriting with masked placeholder.
     */
    public function sanitize_secret( $value ): string {
        $raw = sanitize_text_field( (string) $value );
        if ( empty( $raw ) || strpos( $raw, '•' ) !== false || strpos( $raw, '*' ) !== false ) {
            return (string) get_option( 'zabun_connect_api_key', '' );
        }
        return $raw;
    }

    public function sanitize_api_key( $value ): string {
        return $this->sanitize_secret( $value );
    }

    /**
     * Sanitize Base URL callback.
     */
    public function sanitize_base_url( $value ): string {
        $url = esc_url_raw( trim( (string) $value ) );
        return ! empty( $url ) ? untrailingslashit( $url ) : 'https://gateway-cmsapi.v2.zabun.be';
    }

    /**
     * Sanitize sync interval callback.
     */
    public function sanitize_interval( $value ): string {
        $allowed = [ 'hourly', 'twicedaily', 'daily' ];
        return in_array( $value, $allowed, true ) ? $value : 'hourly';
    }

    public function render_api_key_field(): void {
        $key = (string) get_option( 'zabun_connect_api_key', '' );
        $display = ! empty( $key ) ? str_repeat( '•', 16 ) . substr( $key, -4 ) : '';
        ?>
        <input type="text" id="zabun_connect_api_key" name="zabun_connect_api_key" 
               value="<?php echo esc_attr( $display ); ?>" 
               placeholder="<?php esc_attr_e( 'Enter Zabun API Key', 'zabun-connect' ); ?>" 
               class="regular-text" autocomplete="off" />
        <p class="description"><?php esc_html_e( 'Your unique API key issued by Zabun Support.', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_client_id_field(): void {
        $val = (string) get_option( 'zabun_connect_client_id', '' );
        ?>
        <input type="text" id="zabun_connect_client_id" name="zabun_connect_client_id" 
               value="<?php echo esc_attr( $val ); ?>" 
               placeholder="<?php esc_attr_e( 'e.g. 10001 or UUID', 'zabun-connect' ); ?>" 
               class="regular-text" autocomplete="off" />
        <p class="description"><?php esc_html_e( 'The client_id provided by Zabun support.', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_server_id_field(): void {
        $val = (string) get_option( 'zabun_connect_server_id', '' );
        ?>
        <input type="text" id="zabun_connect_server_id" name="zabun_connect_server_id" 
               value="<?php echo esc_attr( $val ); ?>" 
               placeholder="<?php esc_attr_e( 'e.g. 1 or UUID', 'zabun-connect' ); ?>" 
               class="regular-text" autocomplete="off" />
        <p class="description"><?php esc_html_e( 'The server_id provided by Zabun support.', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_x_client_id_field(): void {
        $val = (string) get_option( 'zabun_connect_x_client_id', '' );
        ?>
        <input type="text" id="zabun_connect_x_client_id" name="zabun_connect_x_client_id" 
               value="<?php echo esc_attr( $val ); ?>" 
               placeholder="<?php esc_attr_e( 'e.g. 235 or Company ID', 'zabun-connect' ); ?>" 
               class="regular-text" autocomplete="off" />
        <p class="description"><?php esc_html_e( 'Your company/agency ID (X-CLIENT-ID header).', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_media_id_field(): void {
        $val = (int) get_option( 'zabun_connect_media_id', 14 );
        ?>
        <input type="number" id="zabun_connect_media_id" name="zabun_connect_media_id" 
               value="<?php echo esc_attr( $val ); ?>" 
               class="small-text" />
        <p class="description"><?php esc_html_e( 'Media ID for your website (Default: 14 for "Website via API").', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_base_url_field(): void {
        $url = (string) get_option( 'zabun_connect_base_url', 'https://gateway-cmsapi.v2.zabun.be' );
        ?>
        <input type="url" id="zabun_connect_base_url" name="zabun_connect_base_url" 
               value="<?php echo esc_attr( $url ); ?>" 
               class="regular-text" />
        <p class="description"><?php esc_html_e( 'Default: https://gateway-cmsapi.v2.zabun.be', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_sync_interval_field(): void {
        $interval = (string) get_option( 'zabun_connect_sync_interval', 'hourly' );
        ?>
        <select id="zabun_connect_sync_interval" name="zabun_connect_sync_interval">
            <option value="hourly" <?php selected( $interval, 'hourly' ); ?>><?php esc_html_e( 'Hourly (Recommended)', 'zabun-connect' ); ?></option>
            <option value="twicedaily" <?php selected( $interval, 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily (Every 12 hours)', 'zabun-connect' ); ?></option>
            <option value="daily" <?php selected( $interval, 'daily' ); ?>><?php esc_html_e( 'Daily (Every 24 hours)', 'zabun-connect' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'How often listings are fetched and updated in local cache.', 'zabun-connect' ); ?></p>
        <?php
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'zabun-connect' ) );
        }

        global $wpdb;
        $table_name    = Schema::get_table_name();
        $cached_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        $last_sync     = get_option( 'zabun_connect_last_sync', __( 'Never', 'zabun-connect' ) );
        $db_version    = get_option( 'zabun_connect_db_version', '1.0.0' );
        ?>
        <div class="wrap zabun-settings-wrap">
            <h1 class="wp-heading-inline" style="position:absolute;margin:-1px;padding:0;height:1px;width:1px;overflow:hidden;clip:rect(0,0,0,0);border:0;"><?php esc_html_e( 'Zabun Connect', 'zabun-connect' ); ?></h1>
            <hr class="wp-header-end">

            <div class="zabun-header">
                <div class="zabun-header-title">
                    <span><?php esc_html_e( 'Zabun Connect', 'zabun-connect' ); ?></span>
                    <span class="zabun-badge">v<?php echo esc_html( ZABUN_CONNECT_VERSION ); ?></span>
                </div>
            </div>

            <div class="zabun-grid-layout">
                <div class="zabun-main-column">
                    <div class="zabun-card">
                        <form method="post" action="options.php">
                            <?php
                            settings_fields( 'zabun_connect_settings' );
                            do_settings_sections( 'zabun-connect' );
                            submit_button( __( 'Save Settings', 'zabun-connect' ) );
                            ?>
                        </form>
                    </div>
                </div>

                <div class="zabun-side-column">
                    <!-- Status & Health Card -->
                    <div class="zabun-card">
                        <h2 class="zabun-card-title"><?php esc_html_e( 'System Status', 'zabun-connect' ); ?></h2>
                        <ul class="zabun-status-list">
                            <li class="zabun-status-item">
                                <span class="zabun-status-label"><?php esc_html_e( 'Cached Listings:', 'zabun-connect' ); ?></span>
                                <span class="zabun-status-val" id="zabun-cached-count"><?php echo esc_html( $cached_count ); ?></span>
                            </li>
                            <li class="zabun-status-item">
                                <span class="zabun-status-label"><?php esc_html_e( 'Last Sync:', 'zabun-connect' ); ?></span>
                                <span class="zabun-status-val" id="zabun-last-sync"><?php echo esc_html( $last_sync ); ?></span>
                            </li>
                            <li class="zabun-status-item">
                                <span class="zabun-status-label"><?php esc_html_e( 'Database Version:', 'zabun-connect' ); ?></span>
                                <span class="zabun-status-val"><?php echo esc_html( $db_version ); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Connection Test Card -->
                    <div class="zabun-card">
                        <h2 class="zabun-card-title"><?php esc_html_e( 'Connection Test', 'zabun-connect' ); ?></h2>
                        <p><?php esc_html_e( 'Verify API credentials against Zabun CRM gateway.', 'zabun-connect' ); ?></p>
                        <button type="button" id="zabun-test-connection" class="button button-secondary zabun-action-btn">
                            <?php esc_html_e( 'Test Connection', 'zabun-connect' ); ?>
                        </button>
                        <div id="zabun-connection-status" class="zabun-feedback"></div>
                    </div>

                    <!-- Manual Sync Card -->
                    <div class="zabun-card">
                        <h2 class="zabun-card-title"><?php esc_html_e( 'Manual Synchronization', 'zabun-connect' ); ?></h2>
                        <p><?php esc_html_e( 'Download and refresh all listings now into the local database.', 'zabun-connect' ); ?></p>
                        <button type="button" id="zabun-sync-now" class="button button-primary zabun-action-btn">
                            <?php esc_html_e( 'Sync Now', 'zabun-connect' ); ?>
                        </button>
                        <div id="zabun-sync-status" class="zabun-feedback"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
