<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use ZabunConnect\Shortcodes\ShortcodesHandler;

defined( 'ABSPATH' ) || exit;

class PropertyDetail extends Widget_Base {

    public function get_name(): string {
        return 'zabun_property_detail';
    }

    public function get_title(): string {
        return __( 'Property Detail', 'zabun-connect' );
    }

    public function get_icon(): string {
        return 'eicon-single-post';
    }

    public function get_categories(): array {
        return [ 'zabun-connect' ];
    }

    public function get_keywords(): array {
        return [ 'zabun', 'property', 'single', 'detail', 'real estate', 'facts', 'gallery' ];
    }

    protected function register_controls(): void {
        /* ==========================================================================
           TAB CONTENT
           ========================================================================== */
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Configuration', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'property_id',
            [
                'label'       => __( 'Specific Property Reference / ID', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'e.g. ZAB-1001 or numeric ID', 'zabun-connect' ),
                'description' => __( 'Leave empty to automatically pull from URL query parameter (?property_id=XYZ)', 'zabun-connect' ),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 1. Header & Price Block
           ========================================================================== */
        $this->start_controls_section(
            'section_style_header',
            [
                'label' => __( 'Header & Price', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Property Title Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-title-block h1' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'label'    => __( 'Title Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-title-block h1',
            ]
        );

        $this->add_control(
            'location_color',
            [
                'label'     => __( 'Location / Address Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-title-block .zabun-card-address' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'location_typography',
                'label'    => __( 'Location Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-title-block .zabun-card-address',
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => __( 'Price Highlight Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_typography',
                'label'    => __( 'Price Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-price',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Image Gallery
           ========================================================================== */
        $this->start_controls_section(
            'section_style_gallery',
            [
                'label' => __( 'Photo Gallery', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'gallery_max_height',
            [
                'label'      => __( 'Main Image Max Height', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 250, 'max' => 800 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-gallery-main, {{WRAPPER}} .zabun-gallery-main img' => 'max-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gallery_radius',
            [
                'label'      => __( 'Main Image Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-gallery-main' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'gallery_shadow',
                'selector' => '{{WRAPPER}} .zabun-gallery-main',
            ]
        );

        $this->add_control(
            'thumb_active_border',
            [
                'label'     => __( 'Active Thumbnail Border Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-thumb-item.active, {{WRAPPER}} .zabun-thumb-item:hover' => 'border-color: {{VALUE}}; opacity: 1;',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Overview & Facts Table
           ========================================================================== */
        $this->start_controls_section(
            'section_style_facts_table',
            [
                'label' => __( 'Facts & Specifications Table', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_header_bg',
            [
                'label'     => __( 'Label Column Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-facts-table th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_color',
            [
                'label'     => __( 'Label Column Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-facts-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_val_color',
            [
                'label'     => __( 'Value Column Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-facts-table td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'table_border',
                'selector' => '{{WRAPPER}} .zabun-facts-table, {{WRAPPER}} .zabun-facts-table th, {{WRAPPER}} .zabun-facts-table td',
            ]
        );

        $this->add_responsive_control(
            'table_cell_padding',
            [
                'label'      => __( 'Cell Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-facts-table th, {{WRAPPER}} .zabun-facts-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 4. Sidebar Card
           ========================================================================== */
        $this->start_controls_section(
            'section_style_sidebar',
            [
                'label' => __( 'Inquiry Sidebar Card', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'sidebar_bg',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zabun-sidebar-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'sidebar_border',
                'selector' => '{{WRAPPER}} .zabun-sidebar-card',
            ]
        );

        $this->add_responsive_control(
            'sidebar_radius',
            [
                'label'      => __( 'Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-sidebar-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sidebar_padding',
            [
                'label'      => __( 'Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-sidebar-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'sidebar_shadow',
                'selector' => '{{WRAPPER}} .zabun-sidebar-card',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $atts = [
            'id'          => $settings['property_id'] ?? '',
            'external_id' => $settings['property_id'] ?? '',
        ];

        echo ShortcodesHandler::instance()->render_detail_shortcode( $atts );
    }
}
