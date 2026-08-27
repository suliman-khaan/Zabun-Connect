<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
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

        /* ==========================================================================
           TAB CONTENT
           ========================================================================== */
        $this->start_controls_section(
            'section_featured_layout',
            [
                'label' => __( 'Featured Layout & Query', 'zabun-connect' ),
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

        $this->add_control(
            'detail_url',
            [
                'label'       => __( 'Custom Single Property Page URL', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/property-detail/',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 1. Card Container
           ========================================================================== */
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => __( 'Property Card', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'card_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->add_responsive_control(
            'card_radius',
            [
                'label'      => __( 'Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Price & Typography
           ========================================================================== */
        $this->start_controls_section(
            'section_style_typography',
            [
                'label' => __( 'Price & Titles', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => __( 'Price Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_typography',
                'label'    => __( 'Price Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-card-price',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Title Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-title, {{WRAPPER}} .zabun-card-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'label'    => __( 'Title Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-card-title',
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
            'detail_url' => $settings['detail_url'] ?? '',
            'pagination' => 'no',
        ];

        echo ShortcodesHandler::instance()->render_featured_shortcode( $atts );
    }
}
