<?php

namespace ZabunConnect;

defined( 'ABSPATH' ) || exit;

class Plugin {

    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Plugin
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Prevent direct instantiation.
    }

    /**
     * Initialize plugin components.
     */
    public function init(): void {
        // Check DB updates if necessary
        $this->maybe_update_database();

        // Admin-only modules
        if ( is_admin() ) {
            $this->init_admin();
        }

        // Cron & Sync components
        $this->init_sync();

        // Shortcodes
        $this->init_shortcodes();

        // Elementor integration
        $this->init_elementor();
    }

    /**
     * Initialize Shortcodes.
     */
    private function init_shortcodes(): void {
        if ( class_exists( '\ZabunConnect\Shortcodes\ShortcodesHandler' ) ) {
            \ZabunConnect\Shortcodes\ShortcodesHandler::instance()->init();
        }
    }

    /**
     * Run database schema migration if version mismatch.
     */
    private function maybe_update_database(): void {
        $installed_ver = get_option( 'zabun_connect_db_version', '0.0.0' );
        if ( version_compare( $installed_ver, ZABUN_CONNECT_DB_VERSION, '<' ) ) {
            \ZabunConnect\Database\Schema::create_tables();
        }
    }

    /**
     * Initialize Admin hooks & screens.
     */
    private function init_admin(): void {
        if ( class_exists( '\ZabunConnect\Admin\SettingsPage' ) ) {
            \ZabunConnect\Admin\SettingsPage::instance()->init();
        }
    }

    /**
     * Initialize Sync & Cron handlers.
     */
    private function init_sync(): void {
        if ( class_exists( '\ZabunConnect\Sync\Scheduler' ) ) {
            \ZabunConnect\Sync\Scheduler::instance()->init();
        }
    }

    /**
     * Initialize Elementor widgets if Elementor is active.
     */
    private function init_elementor(): void {
        if ( did_action( 'elementor/loaded' ) && class_exists( '\ZabunConnect\Elementor\WidgetsLoader' ) ) {
            \ZabunConnect\Elementor\WidgetsLoader::instance()->init();
        }
    }
}
