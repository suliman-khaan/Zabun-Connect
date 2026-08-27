<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
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
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $atts = [
            'action_url' => $settings['action_url'] ?? '',
        ];

        echo ShortcodesHandler::instance()->render_search_shortcode( $atts );
    }
}
