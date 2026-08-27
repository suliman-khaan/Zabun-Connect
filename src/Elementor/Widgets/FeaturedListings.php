<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use ZabunConnect\Shortcodes\ShortcodesHandler;
use ZabunConnect\Cache\ListingsRepository;

defined( 'ABSPATH' ) || exit;

class FeaturedListings extends Widget_Base {

    public function get_name(): string {
        return 'zabun_featured_listings';
    }

    public function get_title(): string {
        return __( 'Featured Listings', 'zabun-connect' );
    }

    public function get_icon(): string {
        return 'eicon-star';
    }

    public function get_categories(): array {
        return [ 'zabun-connect' ];
    }

    public function get_keywords(): array {
        return [ 'zabun', 'featured', 'property', 'real estate', 'highlight' ];
    }

    protected function register_controls(): void {
        $repo = ListingsRepository::instance();

        $this->start_controls_section(
            'section_featured_layout',
            [
                'label' => __( 'Featured Layout', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'   => __( 'Columns', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
            ]
        );

        $this->add_control(
            'limit',
            [
                'label'   => __( 'Number of Properties', 'zabun-connect' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 3,
                'min'     => 1,
                'max'     => 12,
            ]
        );

        $this->add_control(
            'status',
            [
                'label'   => __( 'Filter by Status', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    ''         => __( 'All Statuses', 'zabun-connect' ),
                    'for_sale' => __( 'For Sale', 'zabun-connect' ),
                    'for_rent' => __( 'For Rent', 'zabun-connect' ),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $atts = [
            'columns'    => $settings['columns'] ?? 3,
            'limit'      => $settings['limit'] ?? 3,
            'status'     => $settings['status'] ?? '',
            'pagination' => 'no',
        ];

        echo ShortcodesHandler::instance()->render_featured_shortcode( $atts );
    }
}
