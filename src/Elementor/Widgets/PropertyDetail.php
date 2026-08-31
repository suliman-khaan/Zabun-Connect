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
        return [ 'zabun', 'property', 'single', 'detail', 'real estate', 'facts', 'gallery', 'specs' ];
    }

    protected function register_controls(): void {
        /* ==========================================================================
           TAB CONTENT: Configuration
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
                'placeholder' => __( 'e.g. ZB-48213 or numeric ID', 'zabun-connect' ),
                'description' => __( 'Leave empty to automatically pull from URL query parameter (?property_id=XYZ)', 'zabun-connect' ),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB CONTENT: Custom Icons / SVGs
           ========================================================================== */
        $this->start_controls_section(
            'section_custom_icons',
            [
                'label' => __( 'Custom Icons / SVGs', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'custom_icon_pin',
            [
                'label'       => __( 'Location Pin Icon', 'zabun-connect' ),
                'type'        => Controls_Manager::ICONS,
                'description' => __( 'Upload your own SVG or choose from library.', 'zabun-connect' ),
            ]
        );

        $this->add_control(
            'custom_icon_beds',
            [
                'label'       => __( 'Bedrooms Icon', 'zabun-connect' ),
                'type'        => Controls_Manager::ICONS,
                'description' => __( 'Upload your own SVG or choose from library.', 'zabun-connect' ),
            ]
        );

        $this->add_control(
            'custom_icon_baths',
            [
                'label'       => __( 'Bathrooms Icon', 'zabun-connect' ),
                'type'        => Controls_Manager::ICONS,
                'description' => __( 'Upload your own SVG or choose from library.', 'zabun-connect' ),
            ]
        );

        $this->add_control(
            'custom_icon_area',
            [
                'label'       => __( 'Surface / Area Icon', 'zabun-connect' ),
                'type'        => Controls_Manager::ICONS,
                'description' => __( 'Upload your own SVG or choose from library.', 'zabun-connect' ),
            ]
        );

        $this->add_control(
            'custom_icon_check',
            [
                'label'       => __( 'Feature Checkmark Icon', 'zabun-connect' ),
                'type'        => Controls_Manager::ICONS,
                'description' => __( 'Checkmark icon displayed in features list.', 'zabun-connect' ),
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB CONTENT: Agent & Sidebar Actions
           ========================================================================== */
        $this->start_controls_section(
            'section_agent_settings',
            [
                'label' => __( 'Sidebar Agent & Actions', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'agent_name',
            [
                'label'       => __( 'Agent Name', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'Leave empty to pull from CRM or site name', 'zabun-connect' ),
            ]
        );

        $this->add_control(
            'agent_role',
            [
                'label'       => __( 'Agent Role / Subtitle', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'Leave empty for localized "Listing agent"', 'zabun-connect' ),
                'default'     => '',
            ]
        );

        $this->add_control(
            'agent_phone',
            [
                'label'       => __( 'Agent Phone', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => '+32 2 555 12 34',
            ]
        );

        $this->add_control(
            'agent_email',
            [
                'label'       => __( 'Agent Email', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'agent@example.com',
            ]
        );

        $this->add_control(
            'inquiry_btn_text',
            [
                'label'       => __( 'Primary Button Text', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'Leave empty for localized "Request a viewing"', 'zabun-connect' ),
                'default'     => '',
            ]
        );

        $this->add_control(
            'inquiry_url',
            [
                'label'       => __( 'Primary Button URL / Action', 'zabun-connect' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'mailto:agent@example.com or #inquiry-form',
            ]
        );

        $this->add_control(
            'show_brochure_btn',
            [
                'label'        => __( 'Show "Download brochure" Button', 'zabun-connect' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'zabun-connect' ),
                'label_off'    => __( 'No', 'zabun-connect' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 1. Asymmetrical Photo Gallery
           ========================================================================== */
        $this->start_controls_section(
            'section_style_gallery',
            [
                'label' => __( 'Photo Gallery', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'gallery_height',
            [
                'label'      => __( 'Gallery Height', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 280, 'max' => 750 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-detail-gallery' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'gallery_border',
                'selector' => '{{WRAPPER}} .zabun-detail-gallery',
            ]
        );

        $this->add_responsive_control(
            'gallery_radius',
            [
                'label'      => __( 'Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-detail-gallery' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .zabun-detail-gallery img' => 'object-fit: {{VALUE}};',
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
                    '{{WRAPPER}} .zabun-detail-gallery img' => 'object-position: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 2. Price, Title & Address
           ========================================================================== */
        $this->start_controls_section(
            'section_style_header',
            [
                'label' => __( 'Price, Title & Address', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => __( 'Price Color', 'zabun-connect' ),
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

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Title Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'label'    => __( 'Title Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-title',
            ]
        );

        $this->add_control(
            'address_color',
            [
                'label'     => __( 'Address Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-address, {{WRAPPER}} .zabun-detail-address span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'address_typography',
                'label'    => __( 'Address Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-address',
            ]
        );

        $this->add_control(
            'address_icon_color',
            [
                'label'     => __( 'Address Pin Icon Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-address .zabun-icon-wrap, {{WRAPPER}} .zabun-detail-address .zabun-icon-wrap svg' => 'color: {{VALUE}}; fill: {{VALUE}}; stroke: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Key Facts Strip
           ========================================================================== */
        $this->start_controls_section(
            'section_style_facts_strip',
            [
                'label' => __( 'Key Facts Strip', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'facts_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zabun-detail-facts-strip',
            ]
        );

        $this->add_control(
            'facts_border_color',
            [
                'label'     => __( 'Border & Divider Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-facts-strip' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-detail-fact'       => 'border-left-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'facts_icon_color',
            [
                'label'     => __( 'Icons Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-fact .zabun-icon-wrap, {{WRAPPER}} .zabun-detail-fact .zabun-icon-wrap svg' => 'color: {{VALUE}}; fill: {{VALUE}}; stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'facts_icon_size',
            [
                'label'      => __( 'Icons Size', 'zabun-connect' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 12, 'max' => 48 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-detail-fact .zabun-icon-wrap svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'facts_num_color',
            [
                'label'     => __( 'Numbers Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-fact .num' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'facts_num_typography',
                'label'    => __( 'Numbers Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-fact .num',
            ]
        );

        $this->add_control(
            'facts_label_color',
            [
                'label'     => __( 'Labels Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-fact .label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'facts_label_typography',
                'label'    => __( 'Labels Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-fact .label',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 4. Section Headings & Description
           ========================================================================== */
        $this->start_controls_section(
            'section_style_sections',
            [
                'label' => __( 'Section Headings & Description', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_heading_color',
            [
                'label'     => __( 'Section Heading Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} h2.zabun-section-heading' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'section_heading_typography',
                'label'    => __( 'Section Heading Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} h2.zabun-section-heading',
            ]
        );

        $this->add_control(
            'desc_text_color',
            [
                'label'     => __( 'Description Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-detail-description, {{WRAPPER}} .zabun-detail-description p' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'desc_typography',
                'label'    => __( 'Description Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-detail-description',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 5. Key Details Specs Table
           ========================================================================== */
        $this->start_controls_section(
            'section_style_specs',
            [
                'label' => __( 'Key Details Specs Table', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'spec_label_color',
            [
                'label'     => __( 'Label Column Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-spec-table td:first-child' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'spec_val_color',
            [
                'label'     => __( 'Value Column Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-spec-table td:last-child' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'spec_typography',
                'label'    => __( 'Table Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-spec-table',
            ]
        );

        $this->add_control(
            'spec_border_color',
            [
                'label'     => __( 'Row Border Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-spec-table tr' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 6. Features List
           ========================================================================== */
        $this->start_controls_section(
            'section_style_features',
            [
                'label' => __( 'Features List', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'feature_icon_color',
            [
                'label'     => __( 'Checkmark Icon Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-features-list .feature-check-icon, {{WRAPPER}} .zabun-features-list .feature-check-icon svg' => 'color: {{VALUE}}; stroke: {{VALUE}}; fill: none;',
                ],
            ]
        );

        $this->add_control(
            'feature_text_color',
            [
                'label'     => __( 'Feature Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-features-list li, {{WRAPPER}} .zabun-features-list span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'feature_typography',
                'label'    => __( 'Feature Typography', 'zabun-connect' ),
                'selector' => '{{WRAPPER}} .zabun-features-list',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 7. Sidebar & Agent Card
           ========================================================================== */
        $this->start_controls_section(
            'section_style_sidebar',
            [
                'label' => __( 'Sidebar & Agent Card', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'agent_card_bg',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zabun-agent-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'agent_card_border',
                'selector' => '{{WRAPPER}} .zabun-agent-card',
            ]
        );

        $this->add_responsive_control(
            'agent_card_radius',
            [
                'label'      => __( 'Card Border Radius', 'zabun-connect' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .zabun-agent-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'agent_card_shadow',
                'selector' => '{{WRAPPER}} .zabun-agent-card',
            ]
        );

        $this->add_control(
            'agent_btn_primary_bg',
            [
                'label'     => __( 'Primary Button Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-primary' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'agent_btn_primary_color',
            [
                'label'     => __( 'Primary Button Text Color', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-primary' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'agent_btn_primary_hover_bg',
            [
                'label'     => __( 'Primary Button Hover Background', 'zabun-connect' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-btn-primary:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $raw_role = ! empty( $settings['agent_role'] ) ? trim( $settings['agent_role'] ) : '';
        $raw_btn  = ! empty( $settings['inquiry_btn_text'] ) ? trim( $settings['inquiry_btn_text'] ) : '';

        $custom_options = [
            'agent_name'        => ! empty( $settings['agent_name'] ) ? $settings['agent_name'] : null,
            'agent_role'        => ( ! empty( $raw_role ) && strcasecmp( $raw_role, 'Listing agent' ) !== 0 && strcasecmp( $raw_role, 'Listing Agent' ) !== 0 ) ? $raw_role : null,
            'agent_phone'       => ! empty( $settings['agent_phone'] ) ? $settings['agent_phone'] : null,
            'agent_email'       => ! empty( $settings['agent_email'] ) ? $settings['agent_email'] : null,
            'inquiry_btn_text'  => ( ! empty( $raw_btn ) && strcasecmp( $raw_btn, 'Request a viewing' ) !== 0 ) ? $raw_btn : null,
            'inquiry_url'       => ! empty( $settings['inquiry_url'] ) ? $settings['inquiry_url'] : null,
            'show_brochure_btn' => ( $settings['show_brochure_btn'] ?? 'yes' ) === 'yes',
        ];

        // Custom SVGs / Icons rendering
        if ( ! empty( $settings['custom_icon_pin']['value'] ) ) {
            ob_start();
            Icons_Manager::render_icon( $settings['custom_icon_pin'], [ 'aria-hidden' => 'true' ] );
            $custom_options['icon_pin'] = ob_get_clean();
        }

        if ( ! empty( $settings['custom_icon_beds']['value'] ) ) {
            ob_start();
            Icons_Manager::render_icon( $settings['custom_icon_beds'], [ 'aria-hidden' => 'true' ] );
            $custom_options['icon_beds'] = ob_get_clean();
        }

        if ( ! empty( $settings['custom_icon_baths']['value'] ) ) {
            ob_start();
            Icons_Manager::render_icon( $settings['custom_icon_baths'], [ 'aria-hidden' => 'true' ] );
            $custom_options['icon_baths'] = ob_get_clean();
        }

        if ( ! empty( $settings['custom_icon_area']['value'] ) ) {
            ob_start();
            Icons_Manager::render_icon( $settings['custom_icon_area'], [ 'aria-hidden' => 'true' ] );
            $custom_options['icon_area'] = ob_get_clean();
        }

        if ( ! empty( $settings['custom_icon_check']['value'] ) ) {
            ob_start();
            Icons_Manager::render_icon( $settings['custom_icon_check'], [ 'aria-hidden' => 'true' ] );
            $custom_options['icon_check'] = ob_get_clean();
        }

        $atts = [
            'id'          => $settings['property_id'] ?? '',
            'external_id' => $settings['property_id'] ?? '',
        ];

        echo ShortcodesHandler::instance()->render_detail_shortcode( $atts, $custom_options );
    }
}
