<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use ZabunConnect\Shortcodes\ShortcodesHandler;
use ZabunConnect\Cache\ListingsRepository;

defined( 'ABSPATH' ) || exit;

class PropertyGrid extends Widget_Base {

    public function get_name(): string {
        return 'zabun_property_grid';
    }

    public function get_title(): string {
        return __( 'Property Grid', 'zabun-connect' );
    }

    public function get_icon(): string {
        return 'eicon-gallery-grid';
    }

    public function get_categories(): array {
        return [ 'zabun-connect' ];
    }

    public function get_keywords(): array {
        return [ 'zabun', 'property', 'real estate', 'grid', 'listings', 'houses', 'apartments' ];
    }

    protected function register_controls(): void {
        $repo = ListingsRepository::instance();

        // CONTENT: Layout & Query
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __( 'Grid Layout & Query', 'zabun-connect' ),
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
                'label'   => __( 'Properties per Page', 'zabun-connect' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 9,
                'min'     => 1,
                'max'     => 100,
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
                    'sold'     => __( 'Sold', 'zabun-connect' ),
                    'rented'   => __( 'Rented', 'zabun-connect' ),
                ],
            ]
        );

        $city_options = [ '' => __( 'All Cities', 'zabun-connect' ) ];
        foreach ( $repo->get_distinct_cities() as $city ) {
            $city_options[ $city ] = $city;
        }

        $this->add_control(
            'city',
            [
                'label'   => __( 'Filter by City', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => $city_options,
            ]
        );

        $type_options = [ '' => __( 'All Types', 'zabun-connect' ) ];
        foreach ( $repo->get_distinct_types() as $type ) {
            $type_options[ $type ] = $type;
        }

        $this->add_control(
            'type',
            [
                'label'   => __( 'Filter by Type', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => $type_options,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __( 'Order By', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'        => __( 'Date Added', 'zabun-connect' ),
                    'price'       => __( 'Price', 'zabun-connect' ),
                    'title'       => __( 'Title', 'zabun-connect' ),
                    'living_area' => __( 'Living Area', 'zabun-connect' ),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => __( 'Order Direction', 'zabun-connect' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __( 'Descending (High to Low / Newest)', 'zabun-connect' ),
                    'ASC'  => __( 'Ascending (Low to High / Oldest)', 'zabun-connect' ),
                ],
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label'        => __( 'Enable Pagination', 'zabun-connect' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'zabun-connect' ),
                'label_off'    => __( 'No', 'zabun-connect' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'detail_url',
            [
                'label'       => __( 'Custom Single Property Page URL', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/property-detail/',
                'description' => __( 'Leave blank to link via current page query (?property_id=XYZ)', 'zabun-connect' ),
            ]
        );

        $this->end_controls_section();

        // STYLE: Card
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => __( 'Card Styling', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_bg',
            [
                'label'     => __( 'Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card' => 'background-color: {{VALUE}};',
                ],
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

        // STYLE: Typography & Colors
        $this->start_controls_section(
            'section_style_typography',
            [
                'label' => __( 'Typography & Colors', 'zabun-connect' ),
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
            'limit'      => $settings['limit'] ?? 9,
            'status'     => $settings['status'] ?? '',
            'city'       => $settings['city'] ?? '',
            'type'       => $settings['type'] ?? '',
            'orderby'    => $settings['orderby'] ?? 'date',
            'order'      => $settings['order'] ?? 'DESC',
            'pagination' => $settings['pagination'] ?? 'yes',
            'detail_url' => $settings['detail_url'] ?? '',
        ];

        echo ShortcodesHandler::instance()->render_grid_shortcode( $atts );
    }
}
