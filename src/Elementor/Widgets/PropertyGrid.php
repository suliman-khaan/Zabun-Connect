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

        /* ==========================================================================
           TAB CONTENT: Grid Layout & Query
           ========================================================================== */
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
                'description' => __( 'Leave blank to link via query parameter (?property_id=XYZ)', 'zabun-connect' ),
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

        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => __( 'Card Body Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        /* ==========================================================================
           TAB STYLE: 2. Image / Media
           ========================================================================== */
        $this->start_controls_section(
            'section_style_media',
            [
                'label' => __( 'Image & Media', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'media_height',
            [
                'label'      => __( 'Image Height', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 120, 'max' => 500 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-media' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: unset;',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label'      => __( 'Image Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Status Badge
           ========================================================================== */
        $this->start_controls_section(
            'section_style_badge',
            [
                'label' => __( 'Status Badge', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'badge_bg_color',
            [
                'label'     => __( 'Badge Background Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-badge' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_text_color',
            [
                'label'     => __( 'Badge Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'badge_typography',
                'label'    => __( 'Badge Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-card-badge',
            ]
        );

        $this->add_responsive_control(
            'badge_radius',
            [
                'label'      => __( 'Badge Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label'      => __( 'Badge Padding', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 4. Price & Frequency
           ========================================================================== */
        $this->start_controls_section(
            'section_style_price',
            [
                'label' => __( 'Price', 'zabun-connect' ),
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
            'price_freq_color',
            [
                'label'     => __( 'Frequency Color (e.g. / month)', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-price-freq' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'price_margin',
            [
                'label'      => __( 'Price Margin Bottom', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-price' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 5. Title & Address
           ========================================================================== */
        $this->start_controls_section(
            'section_style_title',
            [
                'label' => __( 'Title & Address', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
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

        $this->add_control(
            'title_hover_color',
            [
                'label'     => __( 'Title Hover Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-title a:hover' => 'color: {{VALUE}};',
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

        $this->add_control(
            'address_color',
            [
                'label'     => __( 'Address Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-address' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'address_typography',
                'label'    => __( 'Address Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-card-address',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 6. Features / Facts (Beds, Baths, Area)
           ========================================================================== */
        $this->start_controls_section(
            'section_style_facts',
            [
                'label' => __( 'Key Facts & Specs (Beds/Baths)', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'facts_border_color',
            [
                'label'     => __( 'Separator Border Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-facts' => 'border-top-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'facts_text_color',
            [
                'label'     => __( 'Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-facts, {{WRAPPER}} .zabun-fact-item' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'facts_strong_color',
            [
                'label'     => __( 'Numbers Highlight Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-fact-item strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'facts_typography',
                'label'    => __( 'Facts Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-card-facts',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 7. Pagination
           ========================================================================== */
        $this->start_controls_section(
            'section_style_pagination',
            [
                'label'     => __( 'Pagination', 'zabun-connect' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [ 'pagination' => 'yes' ],
            ]
        );

        $this->add_control(
            'pagination_active_bg',
            [
                'label'     => __( 'Active Page Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination .current' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_text',
            [
                'label'     => __( 'Active Page Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination .current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_link_bg',
            [
                'label'     => __( 'Page Button Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_link_text',
            [
                'label'     => __( 'Page Button Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination a' => 'color: {{VALUE}};',
                ],
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
