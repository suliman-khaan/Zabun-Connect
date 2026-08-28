<?php

namespace ZabunConnect\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use ZabunConnect\Shortcodes\ShortcodesHandler;
use ZabunConnect\Cache\ListingsRepository;

defined('ABSPATH') || exit;

class PropertyGrid extends Widget_Base
{

    public function get_name(): string
    {
        return 'zabun_property_grid';
    }

    public function get_title(): string
    {
        return __('Property Grid', 'zabun-connect');
    }

    public function get_icon(): string
    {
        return 'eicon-gallery-grid';
    }

    public function get_categories(): array
    {
        return ['zabun-connect'];
    }

    public function get_keywords(): array
    {
        return ['zabun', 'property', 'real estate', 'grid', 'listings', 'houses', 'apartments'];
    }

    protected function register_controls(): void
    {
        $repo = ListingsRepository::instance();

        /* ==========================================================================
           TAB CONTENT: Grid Layout & Query
           ========================================================================== */
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Grid Layout & Query', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
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
                'label' => __('Properties per Page', 'zabun-connect'),
                'type' => Controls_Manager::NUMBER,
                'default' => 9,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'status',
            [
                'label' => __('Filter by Status', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('All Statuses', 'zabun-connect'),
                    'for_sale' => __('For Sale', 'zabun-connect'),
                    'for_rent' => __('For Rent', 'zabun-connect'),
                    'sold' => __('Sold', 'zabun-connect'),
                    'rented' => __('Rented', 'zabun-connect'),
                ],
            ]
        );

        $city_options = ['' => __('All Cities', 'zabun-connect')];
        foreach ($repo->get_distinct_cities() as $city) {
            $city_options[$city] = $city;
        }

        $this->add_control(
            'city',
            [
                'label' => __('Filter by City', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $city_options,
            ]
        );

        $type_options = ['' => __('All Types', 'zabun-connect')];
        foreach ($repo->get_distinct_types() as $type) {
            $type_options[$type] = $type;
        }

        $this->add_control(
            'type',
            [
                'label' => __('Filter by Type', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $type_options,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date Added', 'zabun-connect'),
                    'price' => __('Price', 'zabun-connect'),
                    'title' => __('Title', 'zabun-connect'),
                    'living_area' => __('Living Area', 'zabun-connect'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order Direction', 'zabun-connect'),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __('Descending (High to Low / Newest)', 'zabun-connect'),
                    'ASC' => __('Ascending (Low to High / Oldest)', 'zabun-connect'),
                ],
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label' => __('Enable Pagination', 'zabun-connect'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'zabun-connect'),
                'label_off' => __('No', 'zabun-connect'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'detail_url',
            [
                'label' => __('Custom Single Property Page URL', 'zabun-connect'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/property-detail/',
                'description' => __('Leave blank to link via query parameter (?property_id=XYZ)', 'zabun-connect'),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB CONTENT: Custom Icons & SVGs
           ========================================================================== */
        $this->start_controls_section(
            'section_custom_icons',
            [
                'label' => __('Custom Icons / SVGs', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'custom_icon_pin',
            [
                'label' => __('Location Pin Icon', 'zabun-connect'),
                'type' => Controls_Manager::ICONS,
                'description' => __('Upload your own SVG or choose from library. Defaults to luxury pin.', 'zabun-connect'),
            ]
        );

        $this->add_control(
            'custom_icon_beds',
            [
                'label' => __('Bedrooms Icon', 'zabun-connect'),
                'type' => Controls_Manager::ICONS,
                'description' => __('Upload your own SVG or choose from library. Defaults to bed icon.', 'zabun-connect'),
            ]
        );

        $this->add_control(
            'custom_icon_baths',
            [
                'label' => __('Bathrooms Icon', 'zabun-connect'),
                'type' => Controls_Manager::ICONS,
                'description' => __('Upload your own SVG or choose from library. Defaults to bath icon.', 'zabun-connect'),
            ]
        );

        $this->add_control(
            'custom_icon_area',
            [
                'label' => __('Surface / Area Icon', 'zabun-connect'),
                'type' => Controls_Manager::ICONS,
                'description' => __('Upload your own SVG or choose from library. Defaults to m² icon.', 'zabun-connect'),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 1. Property Card
           ========================================================================== */
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => __('Property Card', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Card Body Padding', 'zabun-connect'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .zabun-card-facts' => 'margin-left: -{{LEFT}}{{UNIT}}; margin-right: -{{RIGHT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->add_responsive_control(
            'card_radius',
            [
                'label' => __('Border Radius', 'zabun-connect'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .zabun-card',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Image & Media
           ========================================================================== */
        $this->start_controls_section(
            'section_style_media',
            [
                'label' => __('Image & Media', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'      => __( 'Image Height', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 120, 'max' => 600 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-card-img'   => 'height: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .zabun-card-img'   => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .zabun-card-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_object_fit',
            [
                'label'     => __( 'Image Object Fit', 'zabun-connect' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'cover',
                'options'   => [
                    'cover'      => __( 'Cover', 'zabun-connect' ),
                    'contain'    => __( 'Contain', 'zabun-connect' ),
                    'fill'       => __( 'Fill', 'zabun-connect' ),
                    'scale-down' => __( 'Scale Down', 'zabun-connect' ),
                    'none'       => __( 'None', 'zabun-connect' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_object_position',
            [
                'label'     => __( 'Image Object Position', 'zabun-connect' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'center center',
                'options'   => [
                    'center center' => __( 'Center Center', 'zabun-connect' ),
                    'center top'    => __( 'Center Top', 'zabun-connect' ),
                    'center bottom' => __( 'Center Bottom', 'zabun-connect' ),
                    'left top'      => __( 'Left Top', 'zabun-connect' ),
                    'left center'   => __( 'Left Center', 'zabun-connect' ),
                    'left bottom'   => __( 'Left Bottom', 'zabun-connect' ),
                    'right top'     => __( 'Right Top', 'zabun-connect' ),
                    'right center'  => __( 'Right Center', 'zabun-connect' ),
                    'right bottom'  => __( 'Right Bottom', 'zabun-connect' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-img' => 'object-position: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Status Badges (Separate Color Per Status)
           ========================================================================== */
        $this->start_controls_section(
            'section_style_badges',
            [
                'label' => __('Status Badges', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // For Sale Badge
        $this->add_control(
            'heading_badge_sale',
            [
                'label' => __('"For Sale" Badge', 'zabun-connect'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'badge_sale_bg',
            [
                'label' => __('Background Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_sale, {{WRAPPER}} .zabun-card-tag.status-sale' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_sale_color',
            [
                'label' => __('Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_sale, {{WRAPPER}} .zabun-card-tag.status-sale' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        // For Rent Badge
        $this->add_control(
            'heading_badge_rent',
            [
                'label' => __('"For Rent" Badge', 'zabun-connect'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'badge_rent_bg',
            [
                'label' => __('Background Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_rent, {{WRAPPER}} .zabun-card-tag.status-rent' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_rent_color',
            [
                'label' => __('Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_rent, {{WRAPPER}} .zabun-card-tag.status-rent' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        // Price Reduced Badge
        $this->add_control(
            'heading_badge_reduced',
            [
                'label' => __('"Price Reduced" Badge', 'zabun-connect'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'badge_reduced_bg',
            [
                'label' => __('Background Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-reduced, {{WRAPPER}} .zabun-card-tag.status-price_reduced' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_reduced_color',
            [
                'label' => __('Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-reduced, {{WRAPPER}} .zabun-card-tag.status-price_reduced' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        // Sold / Rented Badge
        $this->add_control(
            'heading_badge_sold',
            [
                'label' => __('"Sold / Rented" Badge', 'zabun-connect'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'badge_sold_bg',
            [
                'label' => __('Background Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-sold, {{WRAPPER}} .zabun-card-tag.status-rented' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_sold_color',
            [
                'label' => __('Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-sold, {{WRAPPER}} .zabun-card-tag.status-rented' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        // Badge Typography & Style
        $this->add_control(
            'heading_badge_general',
            [
                'label' => __('Badge Typography & Sizing', 'zabun-connect'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'label' => __('Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-tag',
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Padding', 'zabun-connect'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'label' => __('Price', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'label' => __('Price Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-price',
            ]
        );

        $this->add_control(
            'price_freq_color',
            [
                'label' => __('Frequency Color (e.g. / month)', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-price-freq' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'price_margin',
            [
                'label' => __('Price Margin Bottom', 'zabun-connect'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 40]],
                'selectors' => [
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
                'label' => __('Title & Address', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-title, {{WRAPPER}} .zabun-card-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Title Hover Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-title',
            ]
        );

        $this->add_control(
            'address_color',
            [
                'label' => __('Address Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-address, {{WRAPPER}} .zabun-card-address span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'address_typography',
                'label' => __('Address Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-address',
            ]
        );

        $this->add_control(
            'address_icon_color',
            [
                'label' => __('Address Pin Icon Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-address .zabun-icon-wrap, {{WRAPPER}} .zabun-card-address .zabun-icon-wrap svg, {{WRAPPER}} .zabun-card-address .zabun-icon-wrap i' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'address_icon_size',
            [
                'label' => __('Address Pin Icon Size', 'zabun-connect'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 8, 'max' => 32]],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-address .zabun-icon-wrap svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .zabun-card-address .zabun-icon-wrap i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 6. Key Facts & Icons (Beds/Baths/Area)
           ========================================================================== */
        $this->start_controls_section(
            'section_style_facts',
            [
                'label' => __('Key Facts & Icons', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'facts_border_color',
            [
                'label' => __('Divider & Top Border Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-facts' => 'border-top-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-card-fact-item' => 'border-left-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'facts_icon_color',
            [
                'label' => __('Facts Icon Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap, {{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap svg, {{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap i' => 'color: {{VALUE}}; fill: {{VALUE}}; stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'facts_icon_size',
            [
                'label' => __('Facts Icon Size', 'zabun-connect'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 10, 'max' => 48]],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'facts_num_color',
            [
                'label' => __('Numbers Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item .num' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'facts_num_typography',
                'label' => __('Numbers Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-fact-item .num',
            ]
        );

        $this->add_control(
            'facts_label_color',
            [
                'label' => __('Labels Color (e.g. BEDS, BATHS)', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item .label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'facts_label_typography',
                'label' => __('Labels Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-fact-item .label',
            ]
        );

        $this->add_responsive_control(
            'facts_cell_padding',
            [
                'label' => __('Cell Padding', 'zabun-connect'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 7. Pagination
           ========================================================================== */
        $this->start_controls_section(
            'section_style_pagination',
            [
                'label' => __('Pagination', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['pagination' => 'yes'],
            ]
        );

        $this->add_control(
            'pagination_active_bg',
            [
                'label' => __('Active Page Background', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination .current' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_text',
            [
                'label' => __('Active Page Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination .current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_link_bg',
            [
                'label' => __('Page Button Background', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_pagination_info',
            [
                'label'     => __( 'Results Count Info', 'zabun-connect' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pagination_info_color',
            [
                'label'     => __( 'Count Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-pagination-info' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'pagination_info_typography',
                'label'    => __( 'Count Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-pagination-info',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();

        $custom_icons = [];

        // Render Pin Icon if set
        if (!empty($settings['custom_icon_pin']['value'])) {
            ob_start();
            Icons_Manager::render_icon($settings['custom_icon_pin'], ['aria-hidden' => 'true']);
            $custom_icons['pin'] = ob_get_clean();
        }

        // Render Beds Icon if set
        if (!empty($settings['custom_icon_beds']['value'])) {
            ob_start();
            Icons_Manager::render_icon($settings['custom_icon_beds'], ['aria-hidden' => 'true']);
            $custom_icons['beds'] = ob_get_clean();
        }

        // Render Baths Icon if set
        if (!empty($settings['custom_icon_baths']['value'])) {
            ob_start();
            Icons_Manager::render_icon($settings['custom_icon_baths'], ['aria-hidden' => 'true']);
            $custom_icons['baths'] = ob_get_clean();
        }

        // Render Area Icon if set
        if (!empty($settings['custom_icon_area']['value'])) {
            ob_start();
            Icons_Manager::render_icon($settings['custom_icon_area'], ['aria-hidden' => 'true']);
            $custom_icons['area'] = ob_get_clean();
        }

        $repo = ListingsRepository::instance();

        $current_page = max(1, (int) ($_GET['zabun_page'] ?? (get_query_var('paged') ? get_query_var('paged') : 1)));
        $status_filter = !empty($_GET['zabun_status']) ? sanitize_text_field($_GET['zabun_status']) : ($settings['status'] ?? '');
        $city_filter = !empty($_GET['zabun_city']) ? sanitize_text_field($_GET['zabun_city']) : ($settings['city'] ?? '');
        $type_filter = !empty($_GET['zabun_type']) ? sanitize_text_field($_GET['zabun_type']) : ($settings['type'] ?? '');
        $min_price = !empty($_GET['zabun_min_price']) ? (float) $_GET['zabun_min_price'] : null;
        $max_price = !empty($_GET['zabun_max_price']) ? (float) $_GET['zabun_max_price'] : null;
        $bedrooms = !empty($_GET['zabun_bedrooms']) ? (int) $_GET['zabun_bedrooms'] : null;
        $search = !empty($_GET['zabun_search']) ? sanitize_text_field($_GET['zabun_search']) : '';

        $limit = max(1, (int) ($settings['limit'] ?? 9));

        $query_args = [
            'limit' => $limit,
            'page' => $current_page,
            'status' => $status_filter,
            'city' => $city_filter,
            'property_type' => $type_filter,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'bedrooms' => $bedrooms,
            'search' => $search,
            'orderby' => sanitize_text_field($settings['orderby'] ?? 'date'),
            'order' => sanitize_text_field($settings['order'] ?? 'DESC'),
        ];

        $listings = $repo->get_listings($query_args);
        $total_items = $repo->count_listings($query_args);
        $total_pages = ceil($total_items / $limit);
        $columns_cls = 'zabun-grid-' . min(4, max(1, (int) ($settings['columns'] ?? 3)));
        $detail_url = $settings['detail_url'] ?? '';

        ShortcodesHandler::instance()->enqueue_assets();
        ?>
        <div class="zabun-grid-container">
            <?php if (empty($listings)): ?>
                <div class="zabun-empty-state">
                    <p><?php esc_html_e('No property listings found matching your criteria.', 'zabun-connect'); ?></p>
                </div>
            <?php else: ?>
                <div class="zabun-grid <?php echo esc_attr($columns_cls); ?>">
                    <?php foreach ($listings as $item): ?>
                        <?php echo ShortcodesHandler::instance()->render_card_html($item, $detail_url, $custom_icons); ?>
                    <?php endforeach; ?>
                </div>

                <?php if (($settings['pagination'] ?? 'yes') === 'yes' && $total_items > 0): ?>
                    <?php
                    $from_num = ( ( $current_page - 1 ) * $limit ) + 1;
                    $to_num   = min( $total_items, $current_page * $limit );
                    ?>
                    <div class="zabun-pagination-wrap">
                        <div class="zabun-pagination-info">
                            <?php echo sprintf( esc_html__( 'Showing %1$s–%2$s of %3$s listings', 'zabun-connect' ), number_format_i18n( $from_num ), number_format_i18n( $to_num ), number_format_i18n( $total_items ) ); ?>
                        </div>
                        <?php if ($total_pages > 1): ?>
                            <div class="zabun-pagination">
                                <?php
                                echo paginate_links( [
                                    'base'      => add_query_arg( 'zabun_page', '%#%' ),
                                    'format'    => '',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'total'     => $total_pages,
                                    'current'   => max( 1, $current_page ),
                                    'mid_size'  => 1,
                                    'end_size'  => 1,
                                ] );
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
