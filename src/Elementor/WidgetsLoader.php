<?php

namespace ZabunConnect\Elementor;

defined( 'ABSPATH' ) || exit;

class WidgetsLoader {

    /**
     * Singleton instance.
     *
     * @var WidgetsLoader|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return WidgetsLoader
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize Elementor hooks.
     */
    public function init(): void {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Register Zabun Connect category in Elementor editor.
     *
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public function register_category( $elements_manager ): void {
        $elements_manager->add_category(
            'zabun-connect',
            [
                'title' => __( 'Zabun Connect', 'zabun-connect' ),
                'icon'  => 'fa fa-home',
            ]
        );
    }

    /**
     * Register Elementor custom widgets.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function register_widgets( $widgets_manager ): void {
        require_once __DIR__ . '/Widgets/PropertyGrid.php';
        require_once __DIR__ . '/Widgets/PropertyDetail.php';
        require_once __DIR__ . '/Widgets/PropertySearch.php';
        require_once __DIR__ . '/Widgets/FeaturedListings.php';

        $widgets_manager->register( new Widgets\PropertyGrid() );
        $widgets_manager->register( new Widgets\PropertyDetail() );
        $widgets_manager->register( new Widgets\PropertySearch() );
        $widgets_manager->register( new Widgets\FeaturedListings() );
    }

    /**
     * Enqueue styles for Elementor.
     */
    public function enqueue_styles(): void {
        if ( ! wp_style_is( 'select2', 'registered' ) ) {
            wp_register_style(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                [],
                '4.1.0-rc.0'
            );
        }
        wp_enqueue_style( 'select2' );
        wp_enqueue_style(
            'zabun-connect-frontend',
            ZABUN_CONNECT_URL . 'assets/css/frontend.css',
            [ 'select2' ],
            ZABUN_CONNECT_VERSION
        );
    }

    /**
     * Enqueue scripts for Elementor.
     */
    public function enqueue_scripts(): void {
        if ( ! wp_script_is( 'select2', 'registered' ) ) {
            wp_register_script(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                [ 'jquery' ],
                '4.1.0-rc.0',
                true
            );
        }
        wp_enqueue_script( 'select2' );
        wp_enqueue_script(
            'zabun-connect-frontend',
            ZABUN_CONNECT_URL . 'assets/js/frontend.js',
            [ 'jquery', 'select2' ],
            ZABUN_CONNECT_VERSION,
            true
        );
    }
}
