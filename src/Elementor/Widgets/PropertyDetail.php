<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
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
        // CONTENT
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

        // STYLE: Colors & Typography
        $this->start_controls_section(
            'section_style_detail',
            [
                'label' => __( 'Price & Typography', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => __( 'Price Highlight Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
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

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'label'    => __( 'Title Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-title-block h1',
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
