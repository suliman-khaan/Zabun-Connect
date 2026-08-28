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

class FeaturedListings extends Widget_Base
{

    public function get_name(): string
    {
        return 'zabun_featured_listings';
    }

    public function get_title(): string
    {
        return __('Featured Listings', 'zabun-connect');
    }

    public function get_icon(): string
    {
        return 'eicon-star';
    }

    public function get_categories(): array
    {
        return ['zabun-connect'];
    }

    public function get_keywords(): array
    {
        return ['zabun', 'featured', 'property', 'real estate', 'highlight'];
    }

    protected function register_controls(): void
    {
        /* ==========================================================================
           TAB CONTENT
           ========================================================================== */
        $this->start_controls_section(
            'section_featured_layout',
            [
                'label' => __('Featured Layout & Query', 'zabun-connect'),
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
                'label' => __('Number of Properties', 'zabun-connect'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 12,
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
                ],
            ]
        );

        $this->add_control(
            'detail_url',
            [
                'label' => __('Custom Single Property Page URL', 'zabun-connect'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/property-detail/',
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
                'label' => __( 'Image & Media', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
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
                'label' => __( 'Status Badges', 'zabun-connect' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'badge_sale_bg',
            [
                'label' => __('"For Sale" Background', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_sale, {{WRAPPER}} .zabun-card-tag.status-sale' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_sale_color',
            [
                'label' => __('"For Sale" Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_sale, {{WRAPPER}} .zabun-card-tag.status-sale' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_rent_bg',
            [
                'label' => __('"For Rent" Background', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_rent, {{WRAPPER}} .zabun-card-tag.status-rent' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_rent_color',
            [
                'label' => __('"For Rent" Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-for_rent, {{WRAPPER}} .zabun-card-tag.status-rent' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_reduced_bg',
            [
                'label' => __('"Price Reduced" Background', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-reduced, {{WRAPPER}} .zabun-card-tag.status-price_reduced' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'badge_reduced_color',
            [
                'label' => __('"Price Reduced" Text Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-tag.status-reduced, {{WRAPPER}} .zabun-card-tag.status-price_reduced' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'label' => __('Badge Typography', 'zabun-connect'),
                'selector' => '{{WRAPPER}} .zabun-card-tag',
            ]
        );

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 3. Price & Typography
           ========================================================================== */
        $this->start_controls_section(
            'section_style_typography',
            [
                'label' => __('Price & Titles', 'zabun-connect'),
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
            'title_color',
            [
                'label' => __('Title Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-title, {{WRAPPER}} .zabun-card-title a' => 'color: {{VALUE}};',
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

        $this->end_controls_section();

        /* ==========================================================================
           TAB STYLE: 4. Facts & Icons
           ========================================================================== */
        $this->start_controls_section(
            'section_style_facts',
            [
                'label' => __('Key Facts & Icons', 'zabun-connect'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'facts_icon_color',
            [
                'label' => __('Icon Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap, {{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap svg, {{WRAPPER}} .zabun-card-fact-item .zabun-icon-wrap i' => 'color: {{VALUE}}; fill: {{VALUE}}; stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'facts_icon_size',
            [
                'label' => __('Icon Size', 'zabun-connect'),
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
            'facts_border_color',
            [
                'label' => __('Border Color', 'zabun-connect'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zabun-card-facts' => 'border-top-color: {{VALUE}};',
                    '{{WRAPPER}} .zabun-card-fact-item' => 'border-left-color: {{VALUE}};',
                ],
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

        $limit = max(1, (int) ($settings['limit'] ?? 3));

        $query_args = [
            'limit' => $limit,
            'status' => $settings['status'] ?? '',
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $listings = $repo->get_listings($query_args);
        $columns_cls = 'zabun-grid-' . min(4, max(1, (int) ($settings['columns'] ?? 3)));
        $detail_url = $settings['detail_url'] ?? '';

        ShortcodesHandler::instance()->enqueue_assets();
        ?>
        <div class="zabun-grid-container">
            <?php if (empty($listings)): ?>
                <div class="zabun-empty-state">
                    <p><?php esc_html_e('No featured listings found.', 'zabun-connect'); ?></p>
                </div>
            <?php else: ?>
                <div class="zabun-grid <?php echo esc_attr($columns_cls); ?>">
                    <?php foreach ($listings as $item): ?>
                        <?php echo ShortcodesHandler::instance()->render_card_html($item, $detail_url, $custom_icons); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
