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

class PropertySearch extends Widget_Base {

    public function get_name(): string {
        return 'zabun_property_search';
    }

    public function get_title(): string {
        return __( 'Property Search Bar', 'zabun-connect' );
    }

    public function get_icon(): string {
        return 'eicon-search';
    }

    public function get_categories(): array {
        return [ 'zabun-connect' ];
    }

    public function get_keywords(): array {
        return [ 'zabun', 'property', 'search', 'filter', 'real estate', 'bar', 'hero' ];
    }

    protected function register_controls(): void {
        /* ==========================================================================
           TAB CONTENT
           ========================================================================== */
        $this->start_controls_section(
            'section_search_config',
            [
                'label' => __( 'Search Configuration', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'action_url',
            [
                'label'       => __( 'Target Listings Page URL', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/properties/',
                'description' => __( 'Leave empty to submit to the current page', 'zabun-connect' ),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 1. Hero Search Container
           ========================================================================== */
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __( 'Search Bar Container', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'bar_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zabun-search-hero',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'bar_border',
                'selector' => '{{WRAPPER}} .zabun-search-hero',
            ]
        );

        $this->add_responsive_control(
            'bar_radius',
            [
                'label'      => __( 'Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-search-hero' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'bar_shadow',
                'selector' => '{{WRAPPER}} .zabun-search-hero',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Status Tabs
           ========================================================================== */
        $this->start_controls_section(
            'section_style_tabs',
            [
                'label' => __( 'Status Tabs', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tab_bg',
            [
                'label'     => __( 'Tab Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-status-tabs button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_color',
            [
                'label'     => __( 'Tab Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-status-tabs button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_active_bg',
            [
                'label'     => __( 'Active Tab Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-status-tabs button.active' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'tab_active_color',
            [
                'label'     => __( 'Active Tab Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-status-tabs button.active' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'tab_typography',
                'label'    => __( 'Tab Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-status-tabs button',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Form Labels & Input Fields
           ========================================================================== */
        $this->start_controls_section(
            'section_style_fields',
            [
                'label' => __( 'Inputs & Dropdowns', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => __( 'Label Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-field label, {{WRAPPER}} .zabun-sort-line label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'label'    => __( 'Label Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-field label, {{WRAPPER}} .zabun-sort-line label',
            ]
        );

        $this->add_control(
            'input_color',
            [
                'label'     => __( 'Input Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-control' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'input_typography',
                'label'    => __( 'Input Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-control',
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label'     => __( 'Field Dividers Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-hero-row .zabun-field' => 'border-right-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-status-tabs'          => 'border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-status-tabs button'   => 'border-right-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-btn-more'             => 'border-left-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 4. Search Button
           ========================================================================== */
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __( 'Search & Action Buttons', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'btn_bg_color',
            [
                'label'     => __( 'Search Button Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-search' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg_hover_color',
            [
                'label'     => __( 'Search Button Hover Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-search:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_text_color',
            [
                'label'     => __( 'Search Button Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-search' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'btn_typography',
                'label'    => __( 'Button Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-btn-search',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 5. More Filters Drawer
           ========================================================================== */
        $this->start_controls_section(
            'section_style_drawer',
            [
                'label' => __( 'Expanded Drawer', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'drawer_bg',
            [
                'label'     => __( 'Drawer Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-expanded-drawer' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btngroup_active_bg',
            [
                'label'     => __( 'Filter Buttons Active Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-btngroup button.active' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'btngroup_active_color',
            [
                'label'     => __( 'Filter Buttons Active Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btngroup button.active' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $atts = [
            'action_url' => $settings['action_url'] ?? '',
        ];

        echo ShortcodesHandler::instance()->render_search_shortcode( $atts );
    }
}
