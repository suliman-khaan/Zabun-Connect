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
        return [ 'zabun', 'property', 'search', 'filter', 'real estate', 'bar' ];
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
           TAB STYLE: 1. Search Bar Container
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
                'selector' => '{{WRAPPER}} .zabun-search-bar',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'bar_border',
                'selector' => '{{WRAPPER}} .zabun-search-bar',
            ]
        );

        $this->add_responsive_control(
            'bar_radius',
            [
                'label'      => __( 'Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-search-bar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'bar_padding',
            [
                'label'      => __( 'Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-search-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'bar_shadow',
                'selector' => '{{WRAPPER}} .zabun-search-bar',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Form Labels & Input Fields
           ========================================================================== */
        $this->start_controls_section(
            'section_style_fields',
            [
                'label' => __( 'Form Inputs & Selects', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => __( 'Label Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-form-field label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'label'    => __( 'Label Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-form-field label',
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label'     => __( 'Input Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-form-field input, {{WRAPPER}} .zabun-form-field select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => __( 'Input Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-form-field input, {{WRAPPER}} .zabun-form-field select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'input_border',
                'selector' => '{{WRAPPER}} .zabun-form-field input, {{WRAPPER}} .zabun-form-field select',
            ]
        );

        $this->add_responsive_control(
            'input_radius',
            [
                'label'      => __( 'Input Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-form-field input, {{WRAPPER}} .zabun-form-field select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Submit Button
           ========================================================================== */
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __( 'Search Button', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'btn_bg_color',
            [
                'label'     => __( 'Button Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-search-submit' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg_hover_color',
            [
                'label'     => __( 'Button Hover Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-search-submit:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_text_color',
            [
                'label'     => __( 'Button Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-search-submit' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'btn_typography',
                'label'    => __( 'Button Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-search-submit',
            ]
        );

        $this->add_responsive_control(
            'btn_radius',
            [
                'label'      => __( 'Button Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-search-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'btn_padding',
            [
                'label'      => __( 'Button Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-search-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
