<?php

namespace ZabunConnect\Shortcodes;

use ZabunConnect\Cache\ListingsRepository;

defined( 'ABSPATH' ) || exit;

class ShortcodesHandler {

    /**
     * Singleton instance.
     *
     * @var ShortcodesHandler|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return ShortcodesHandler
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize shortcode hooks.
     */
    public function init(): void {
        add_shortcode( 'zabun_grid', [ $this, 'render_grid_shortcode' ] );
        add_shortcode( 'zabun_listings', [ $this, 'render_grid_shortcode' ] );
        add_shortcode( 'zabun_detail', [ $this, 'render_detail_shortcode' ] );
        add_shortcode( 'zabun_property', [ $this, 'render_detail_shortcode' ] );
        add_shortcode( 'zabun_search', [ $this, 'render_search_shortcode' ] );
        add_shortcode( 'zabun_featured', [ $this, 'render_featured_shortcode' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    /**
     * Register frontend assets.
     */
    public function register_assets(): void {
        wp_register_style(
            'zabun-connect-frontend',
            ZABUN_CONNECT_URL . 'assets/css/frontend.css',
            [],
            ZABUN_CONNECT_VERSION
        );

        wp_register_script(
            'zabun-connect-frontend',
            ZABUN_CONNECT_URL . 'assets/js/frontend.js',
            [ 'jquery' ],
            ZABUN_CONNECT_VERSION,
            true
        );
    }

    /**
     * Enqueue assets when shortcode renders.
     */
    public function enqueue_assets(): void {
        wp_enqueue_style( 'zabun-connect-frontend' );
        wp_enqueue_script( 'zabun-connect-frontend' );
    }

    /**
     * Render Property Grid Shortcode [zabun_grid].
     *
     * @param array $atts
     * @return string
     */
    public function render_grid_shortcode( $atts = [] ): string {
        $this->enqueue_assets();

        $atts = shortcode_atts(
            [
                'columns'     => 3,
                'limit'       => 12,
                'status'      => '',
                'city'        => '',
                'type'        => '',
                'orderby'     => 'date',
                'order'       => 'DESC',
                'pagination'  => 'yes',
                'detail_url'  => '',
            ],
            $atts,
            'zabun_grid'
        );

        $repo = ListingsRepository::instance();

        // Check URL query parameters for dynamic search filter integration
        $current_page = max( 1, (int) ( $_GET['zabun_page'] ?? ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ) );
        $status_filter = ! empty( $_GET['zabun_status'] ) ? sanitize_text_field( $_GET['zabun_status'] ) : $atts['status'];
        $city_filter   = ! empty( $_GET['zabun_city'] ) ? sanitize_text_field( $_GET['zabun_city'] ) : $atts['city'];
        $type_filter   = ! empty( $_GET['zabun_type'] ) ? sanitize_text_field( $_GET['zabun_type'] ) : $atts['type'];
        $min_price     = ! empty( $_GET['zabun_min_price'] ) ? (float) $_GET['zabun_min_price'] : null;
        $max_price     = ! empty( $_GET['zabun_max_price'] ) ? (float) $_GET['zabun_max_price'] : null;
        $bedrooms      = ! empty( $_GET['zabun_bedrooms'] ) ? (int) $_GET['zabun_bedrooms'] : null;
        $search        = ! empty( $_GET['zabun_search'] ) ? sanitize_text_field( $_GET['zabun_search'] ) : '';

        $query_args = [
            'limit'         => (int) $atts['limit'],
            'page'          => $current_page,
            'status'        => $status_filter,
            'city'          => $city_filter,
            'property_type' => $type_filter,
            'min_price'     => $min_price,
            'max_price'     => $max_price,
            'bedrooms'      => $bedrooms,
            'search'        => $search,
            'orderby'       => sanitize_text_field( $atts['orderby'] ),
            'order'         => sanitize_text_field( $atts['order'] ),
        ];

        $listings     = $repo->get_listings( $query_args );
        $total_items  = $repo->count_listings( $query_args );
        $total_pages  = ceil( $total_items / max( 1, (int) $atts['limit'] ) );
        $columns_cls  = 'zabun-grid-' . min( 4, max( 1, (int) $atts['columns'] ) );

        ob_start();
        ?>
        <div class="zabun-grid-container">
            <?php if ( empty( $listings ) ) : ?>
                <div class="zabun-empty-state">
                    <p><?php esc_html_e( 'No property listings found matching your criteria.', 'zabun-connect' ); ?></p>
                </div>
            <?php else : ?>
                <div class="zabun-grid <?php echo esc_attr( $columns_cls ); ?>">
                    <?php foreach ( $listings as $item ) : ?>
                        <?php echo $this->render_card_html( $item, $atts['detail_url'] ); ?>
                    <?php endforeach; ?>
                </div>

                <?php if ( $atts['pagination'] === 'yes' && $total_pages > 1 ) : ?>
                    <div class="zabun-pagination">
                        <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                            <?php if ( $i === $current_page ) : ?>
                                <span class="current"><?php echo esc_html( $i ); ?></span>
                            <?php else : ?>
                                <a href="<?php echo esc_url( add_query_arg( 'zabun_page', $i ) ); ?>"><?php echo esc_html( $i ); ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render single property card HTML.
     *
     * @param array $item
     * @param string $detail_url_base
     * @return string
     */
    public function render_card_html( array $item, string $detail_url_base = '', array $custom_icons = [] ): string {
        $detail_link = ! empty( $detail_url_base ) 
            ? add_query_arg( 'property_id', $item['external_id'], $detail_url_base ) 
            : add_query_arg( 'property_id', $item['external_id'] );

        $raw_status = strtolower( trim( $item['status'] ?? 'for_sale' ) );
        $status_label = ucwords( str_replace( '_', ' ', $raw_status ) );
        $status_class = 'status-' . sanitize_html_class( $raw_status );
        
        $price_formatted = ! empty( $item['price'] ) 
            ? '€ ' . number_format( (float) $item['price'], 0, ',', '.' ) 
            : __( 'Price on request', 'zabun-connect' );

        $freq = ! empty( $item['price_frequency'] ) ? ' / ' . esc_html( $item['price_frequency'] ) : '';

        // Default SVG Icons
        $icon_pin   = $custom_icons['pin'] ?? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 22s7-7.58 7-13a7 7 0 0 0-14 0c0 5.42 7 13 7 13Z"/><circle cx="12" cy="9" r="2.4"/></svg>';
        $icon_beds  = $custom_icons['beds'] ?? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6"/><path d="M3 18v2M21 18v2"/><path d="M7 10V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v3"/><path d="M3 14h18"/></svg>';
        $icon_baths = $custom_icons['baths'] ?? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3Z"/><path d="M6 12V6a2 2 0 0 1 3.2-1.6"/><path d="M4 19v1M18 19v1"/></svg>';
        $icon_area  = $custom_icons['area'] ?? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8V3h5M21 8V3h-5M3 16v5h5M21 16v5h-5"/></svg>';

        $address_str = trim( ( $item['address'] ? $item['address'] . ', ' : '' ) . $item['city'] );

        ob_start();
        ?>
        <article class="zabun-card">
            <div class="zabun-card-media">
                <span class="zabun-card-tag <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                <?php if ( ! empty( $item['featured_image'] ) ) : ?>
                    <a href="<?php echo esc_url( $detail_link ); ?>">
                        <img class="zabun-card-img" src="<?php echo esc_url( $item['featured_image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" />
                    </a>
                <?php else : ?>
                    <div class="zabun-placeholder-img">
                        <span><?php esc_html_e( 'No Image Available', 'zabun-connect' ); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="zabun-card-body">
                <p class="zabun-card-price">
                    <?php echo esc_html( $price_formatted ); ?><?php if ( $freq ) : ?><span class="zabun-card-price-freq"><?php echo esc_html( $freq ); ?></span><?php endif; ?>
                </p>
                <h3 class="zabun-card-title">
                    <a href="<?php echo esc_url( $detail_link ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                </h3>
                <?php if ( ! empty( $address_str ) ) : ?>
                    <p class="zabun-card-address">
                        <span class="zabun-icon-wrap"><?php echo $icon_pin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span><?php echo esc_html( $address_str ); ?></span>
                    </p>
                <?php endif; ?>

                <div class="zabun-card-facts">
                    <div class="zabun-card-fact-item">
                        <span class="zabun-icon-wrap"><?php echo $icon_beds; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="num"><?php echo esc_html( $item['bedrooms'] ?: 0 ); ?></span>
                        <span class="label"><?php esc_html_e( 'Beds', 'zabun-connect' ); ?></span>
                    </div>
                    <div class="zabun-card-fact-item">
                        <span class="zabun-icon-wrap"><?php echo $icon_baths; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="num"><?php echo esc_html( $item['bathrooms'] ?: 0 ); ?></span>
                        <span class="label"><?php esc_html_e( 'Baths', 'zabun-connect' ); ?></span>
                    </div>
                    <div class="zabun-card-fact-item">
                        <span class="zabun-icon-wrap"><?php echo $icon_area; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="num"><?php echo esc_html( $item['living_area'] ? round( (float) $item['living_area'], 1 ) : '-' ); ?></span>
                        <span class="label"><?php esc_html_e( 'm²', 'zabun-connect' ); ?></span>
                    </div>
                </div>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render Single Property Detail Shortcode [zabun_detail].
     *
     * @param array|string $atts
     * @param string|array $content
     * @param string $tag
     * @param array $custom_options
     * @return string
     */
    public function render_detail_shortcode( $atts = [], $content = '', $tag = '', $custom_options = [] ): string {
        $this->enqueue_assets();

        if ( is_array( $content ) && empty( $custom_options ) ) {
            $custom_options = $content;
        }

        $atts = is_array( $atts ) ? $atts : [];

        $atts = shortcode_atts(
            [
                'id'          => '',
                'external_id' => '',
            ],
            $atts,
            'zabun_detail'
        );

        $repo = ListingsRepository::instance();
        $property = null;

        $target_id = ! empty( $atts['id'] ) 
            ? $atts['id'] 
            : ( ! empty( $atts['external_id'] ) 
                ? $atts['external_id'] 
                : ( $_GET['property_id'] ?? ( $_GET['id'] ?? '' ) ) );

        if ( ! empty( $target_id ) ) {
            if ( is_numeric( $target_id ) ) {
                $property = $repo->get_listing_by_id( (int) $target_id );
            }
            if ( ! $property ) {
                $property = $repo->get_listing_by_external_id( sanitize_text_field( $target_id ) );
            }
        }

        if ( ! $property ) {
            return '<div class="zabun-empty-state"><p>' . esc_html__( 'Property not found or invalid ID specified.', 'zabun-connect' ) . '</p></div>';
        }

        $gallery = $property['gallery_images'] ?? [];
        if ( empty( $gallery ) && ! empty( $property['featured_image'] ) ) {
            $gallery = [ $property['featured_image'] ];
        }

        $photo_count = count( $gallery );
        $main_img   = ! empty( $gallery[0] ) ? $gallery[0] : ( $property['featured_image'] ?? '' );
        $side_img_1 = $gallery[1] ?? ( $gallery[0] ?? '' );
        $side_img_2 = $gallery[2] ?? ( $gallery[1] ?? ( $gallery[0] ?? '' ) );

        $raw_status   = strtolower( trim( $property['status'] ?? 'for_sale' ) );
        $status_label = ucwords( str_replace( '_', ' ', $raw_status ) );
        $status_class = 'status-' . sanitize_html_class( $raw_status );

        $price_formatted = ! empty( $property['price'] ) 
            ? '€ ' . number_format( (float) $property['price'], 0, ',', '.' ) 
            : __( 'Price on request', 'zabun-connect' );
        
        $freq = ! empty( $property['price_frequency'] ) ? ' / ' . esc_html( $property['price_frequency'] ) : '';

        $raw         = $property['raw_data'] ?? [];
        $description = $raw['description'] ?? ( $raw['remarks'] ?? '' );

        // Extract features/amenities
        $features = [];
        if ( ! empty( $raw['features'] ) && is_array( $raw['features'] ) ) {
            $features = $raw['features'];
        } elseif ( ! empty( $raw['amenities'] ) && is_array( $raw['amenities'] ) ) {
            $features = $raw['amenities'];
        }

        // Agent information with intelligent fallback
        $agent_name   = $custom_options['agent_name'] ?? ( $raw['agent_name'] ?? get_bloginfo( 'name' ) );
        $agent_role   = $custom_options['agent_role'] ?? ( $raw['agent_role'] ?? __( 'Listing Agent', 'zabun-connect' ) );
        $agent_phone  = $custom_options['agent_phone'] ?? ( $raw['agent_phone'] ?? ( $raw['contact_phone'] ?? '' ) );
        $agent_email  = $custom_options['agent_email'] ?? ( $raw['agent_email'] ?? ( $raw['contact_email'] ?? get_option( 'admin_email' ) ) );
        $agent_avatar = $custom_options['agent_avatar'] ?? ( $raw['agent_avatar'] ?? '' );
        $agent_init   = '';
        if ( ! empty( $agent_name ) ) {
            $parts = explode( ' ', trim( $agent_name ) );
            $agent_init = strtoupper( substr( $parts[0], 0, 1 ) . ( isset( $parts[1] ) ? substr( $parts[1], 0, 1 ) : '' ) );
        }

        $address_full = trim( ( $property['address'] ? $property['address'] . ', ' : '' ) . ( $property['postal_code'] ? $property['postal_code'] . ' ' : '' ) . $property['city'] );

        // Custom icons
        $icon_pin   = $custom_options['icon_pin'] ?? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 22s7-7.58 7-13a7 7 0 0 0-14 0c0 5.42 7 13 7 13Z"/><circle cx="12" cy="9" r="2.4"/></svg>';
        $icon_beds  = $custom_options['icon_beds'] ?? '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6"/><path d="M3 18v2M21 18v2"/><path d="M7 10V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v3"/><path d="M3 14h18"/></svg>';
        $icon_baths = $custom_options['icon_baths'] ?? '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3Z"/><path d="M6 12V6a2 2 0 0 1 3.2-1.6"/><path d="M4 19v1M18 19v1"/></svg>';
        $icon_area  = $custom_options['icon_area'] ?? '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8V3h5M21 8V3h-5M3 16v5h5M21 16v5h-5"/></svg>';
        $icon_check = $custom_options['icon_check'] ?? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>';

        ob_start();
        ?>
        <div class="zabun-detail-wrap zabun-detail-wrapper">
            <!-- Breadcrumbs -->
            <nav class="zabun-crumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'zabun-connect' ); ?></a>
                <span class="crumb-sep">/</span>
                <span><?php echo esc_html( $status_label ); ?></span>
                <?php if ( ! empty( $property['city'] ) ) : ?>
                    <span class="crumb-sep">/</span>
                    <span><?php echo esc_html( $property['city'] ); ?></span>
                <?php endif; ?>
                <span class="crumb-sep">/</span>
                <span class="current-crumb"><?php echo esc_html( $property['title'] ); ?></span>
            </nav>

            <!-- Asymmetrical 3-Photo Gallery -->
            <?php if ( ! empty( $main_img ) ) : ?>
                <div class="zabun-detail-gallery">
                    <span class="zabun-card-tag <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                    
                    <img class="<?php echo $photo_count > 1 ? 'main-img' : 'gallery-single'; ?>" src="<?php echo esc_url( $main_img ); ?>" alt="<?php echo esc_attr( $property['title'] ); ?>" loading="lazy" />
                    
                    <?php if ( $photo_count > 1 ) : ?>
                        <img class="side-img-1" src="<?php echo esc_url( $side_img_1 ); ?>" alt="<?php echo esc_attr( $property['title'] ); ?> 2" loading="lazy" />
                        <?php if ( $photo_count > 2 ) : ?>
                            <img class="side-img-2" src="<?php echo esc_url( $side_img_2 ); ?>" alt="<?php echo esc_attr( $property['title'] ); ?> 3" loading="lazy" />
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ( $photo_count > 0 ) : ?>
                        <span class="photo-count-badge">1 / <?php echo esc_html( $photo_count ); ?> <?php esc_html_e( 'photos', 'zabun-connect' ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- 2-Column Main Layout -->
            <div class="zabun-detail-layout">
                <div class="zabun-detail-main-col">
                    <p class="zabun-detail-price">
                        <?php echo esc_html( $price_formatted ); ?><?php if ( $freq ) : ?><span class="zabun-card-price-freq"><?php echo esc_html( $freq ); ?></span><?php endif; ?>
                    </p>
                    <h1 class="zabun-detail-title"><?php echo esc_html( $property['title'] ); ?></h1>
                    
                    <?php if ( ! empty( $address_full ) ) : ?>
                        <p class="zabun-detail-address">
                            <span class="zabun-icon-wrap"><?php echo $icon_pin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <span><?php echo esc_html( $address_full ); ?></span>
                        </p>
                    <?php endif; ?>

                    <!-- Facts Strip -->
                    <div class="zabun-detail-facts-strip">
                        <div class="zabun-detail-fact">
                            <span class="zabun-icon-wrap"><?php echo $icon_beds; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <span class="num"><?php echo esc_html( $property['bedrooms'] ?: 0 ); ?></span>
                            <span class="label"><?php esc_html_e( 'Beds', 'zabun-connect' ); ?></span>
                        </div>
                        <div class="zabun-detail-fact">
                            <span class="zabun-icon-wrap"><?php echo $icon_baths; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <span class="num"><?php echo esc_html( $property['bathrooms'] ?: 0 ); ?></span>
                            <span class="label"><?php esc_html_e( 'Baths', 'zabun-connect' ); ?></span>
                        </div>
                        <div class="zabun-detail-fact">
                            <span class="zabun-icon-wrap"><?php echo $icon_area; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <span class="num"><?php echo esc_html( $property['living_area'] ? round( (float) $property['living_area'], 1 ) : '-' ); ?></span>
                            <span class="label"><?php esc_html_e( 'm²', 'zabun-connect' ); ?></span>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <?php if ( ! empty( $description ) ) : ?>
                        <h2 class="zabun-section-heading"><?php esc_html_e( 'Description', 'zabun-connect' ); ?></h2>
                        <div class="zabun-detail-description">
                            <?php echo wp_kses_post( wpautop( $description ) ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Key Details Spec Table -->
                    <h2 class="zabun-section-heading"><?php esc_html_e( 'Key details', 'zabun-connect' ); ?></h2>
                    <table class="zabun-spec-table zabun-facts-table">
                        <tbody>
                            <tr>
                                <td><?php esc_html_e( 'Reference', 'zabun-connect' ); ?></td>
                                <td><?php echo esc_html( $property['external_id'] ); ?></td>
                            </tr>
                            <?php if ( ! empty( $property['property_type'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'Property type', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $property['property_type'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td><?php esc_html_e( 'Status', 'zabun-connect' ); ?></td>
                                <td><?php echo esc_html( $status_label ); ?></td>
                            </tr>
                            <?php if ( ! empty( $raw['year_built'] ) || ! empty( $raw['construction_year'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'Year built', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $raw['year_built'] ?? $raw['construction_year'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['living_area'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'Habitable area', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $property['living_area'] ); ?> m²</td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['land_area'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'Plot size', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $property['land_area'] ); ?> m²</td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['epc_value'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'EPC label', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $property['epc_value'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $raw['availability'] ) ) : ?>
                                <tr>
                                    <td><?php esc_html_e( 'Availability', 'zabun-connect' ); ?></td>
                                    <td><?php echo esc_html( $raw['availability'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Features List Section -->
                    <?php if ( ! empty( $features ) ) : ?>
                        <h2 class="zabun-section-heading"><?php esc_html_e( 'Features', 'zabun-connect' ); ?></h2>
                        <ul class="zabun-features-list">
                            <?php foreach ( $features as $feat ) : ?>
                                <li>
                                    <span class="feature-check-icon"><?php echo $icon_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                    <span><?php echo esc_html( is_array( $feat ) ? ( $feat['name'] ?? '' ) : $feat ); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Sidebar Column -->
                <div class="zabun-sidebar">
                    <div class="zabun-agent-card">
                        <div class="zabun-agent-top">
                            <div class="zabun-agent-avatar">
                                <?php if ( ! empty( $agent_avatar ) ) : ?>
                                    <img src="<?php echo esc_url( $agent_avatar ); ?>" alt="<?php echo esc_attr( $agent_name ); ?>" />
                                <?php else : ?>
                                    <?php echo esc_html( $agent_init ?: 'ZB' ); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="zabun-agent-name"><?php echo esc_html( $agent_name ); ?></p>
                                <p class="zabun-agent-role"><?php echo esc_html( $agent_role ); ?></p>
                            </div>
                        </div>

                        <?php if ( ! empty( $agent_phone ) ) : ?>
                            <a class="zabun-agent-line" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $agent_phone ) ); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                                <span><?php echo esc_html( $agent_phone ); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if ( ! empty( $agent_email ) ) : ?>
                            <a class="zabun-agent-line" href="mailto:<?php echo esc_attr( $agent_email ); ?>?subject=<?php echo esc_attr( rawurlencode( 'Inquiry: ' . $property['title'] . ' (' . $property['external_id'] . ')' ) ); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="20" height="16" rx="1"/><path d="m22 6-10 7L2 6"/></svg>
                                <span><?php echo esc_html( $agent_email ); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php
                        $inquiry_url = $custom_options['inquiry_url'] ?? ( ! empty( $agent_email ) ? 'mailto:' . esc_attr( $agent_email ) . '?subject=' . rawurlencode( 'Request a viewing: ' . $property['title'] ) : '#inquiry' );
                        ?>
                        <a href="<?php echo esc_url( $inquiry_url ); ?>" class="zabun-btn zabun-btn-primary">
                            <?php echo esc_html( $custom_options['inquiry_btn_text'] ?? __( 'Request a viewing', 'zabun-connect' ) ); ?>
                        </a>

                        <?php if ( ! empty( $raw['brochure_url'] ) || ! empty( $custom_options['show_brochure_btn'] ) ) : ?>
                            <a href="<?php echo esc_url( $raw['brochure_url'] ?? '#' ); ?>" class="zabun-btn zabun-btn-ghost" target="_blank" rel="noopener">
                                <?php esc_html_e( 'Download brochure', 'zabun-connect' ); ?>
                            </a>
                        <?php endif; ?>

                        <?php
                        // Action hook for modal forms / contact forms
                        do_action( 'zabun_property_inquiry_form', $property );
                        ?>
                    </div>

                    <p class="zabun-ref-notice">
                        <?php echo sprintf( esc_html__( 'Ref. %s · synced from Zabun', 'zabun-connect' ), esc_html( $property['external_id'] ) ); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render Property Search Shortcode [zabun_search].
     *
     * @param array|string $atts
     * @param string|array $content
     * @param string $tag
     * @param array $custom_options
     * @return string
     */
    public function render_search_shortcode( $atts = [], $content = '', $tag = '', $custom_options = [] ): string {
        $this->enqueue_assets();

        if ( is_array( $content ) && empty( $custom_options ) ) {
            $custom_options = $content;
        }

        $atts = is_array( $atts ) ? $atts : [];

        $atts = shortcode_atts(
            [
                'action_url' => '',
            ],
            $atts,
            'zabun_search'
        );

        $repo     = ListingsRepository::instance();
        $cities   = $repo->get_distinct_cities();
        $types    = $repo->get_distinct_types();
        $statuses = [
            'for_sale' => __( 'For sale', 'zabun-connect' ),
            'for_rent' => __( 'For rent', 'zabun-connect' ),
            'sold'     => __( 'Sold', 'zabun-connect' ),
            'rented'   => __( 'Rented', 'zabun-connect' ),
        ];

        $curr_search    = sanitize_text_field( $_GET['zabun_search'] ?? '' );
        $curr_status    = sanitize_text_field( $_GET['zabun_status'] ?? 'for_sale' );
        $curr_city      = sanitize_text_field( $_GET['zabun_city'] ?? '' );
        $curr_type      = sanitize_text_field( $_GET['zabun_type'] ?? '' );
        $curr_min_price = sanitize_text_field( $_GET['zabun_min_price'] ?? '' );
        $curr_max_price = sanitize_text_field( $_GET['zabun_max_price'] ?? '' );
        $curr_beds      = sanitize_text_field( $_GET['zabun_bedrooms'] ?? '' );
        $curr_baths     = sanitize_text_field( $_GET['zabun_bathrooms'] ?? '' );
        $curr_min_area  = sanitize_text_field( $_GET['zabun_min_area'] ?? '' );
        $curr_max_area  = sanitize_text_field( $_GET['zabun_max_area'] ?? '' );
        $curr_order     = sanitize_text_field( $_GET['zabun_orderby'] ?? 'date' );

        $price_options = [
            ''        => __( 'Any', 'zabun-connect' ),
            '100000'  => '€ 100.000',
            '250000'  => '€ 250.000',
            '500000'  => '€ 500.000',
            '750000'  => '€ 750.000',
            '1000000' => '€ 1.000.000',
            '1500000' => '€ 1.500.000+',
        ];

        ob_start();
        ?>
        <div class="zabun-search-hero zabun-search-bar">
            <form method="get" action="<?php echo esc_url( $atts['action_url'] ); ?>" class="zabun-search-form">
                <input type="hidden" name="zabun_status" value="<?php echo esc_attr( $curr_status ); ?>" />
                <input type="hidden" name="zabun_bedrooms" value="<?php echo esc_attr( $curr_beds ); ?>" />
                <input type="hidden" name="zabun_bathrooms" value="<?php echo esc_attr( $curr_baths ); ?>" />

                <!-- Status Tabs -->
                <div class="zabun-status-tabs">
                    <?php foreach ( $statuses as $st_key => $st_name ) : ?>
                        <button type="button" class="<?php echo ( $curr_status === $st_key || ( empty( $curr_status ) && $st_key === 'for_sale' ) ) ? 'active' : ''; ?>" data-status="<?php echo esc_attr( $st_key ); ?>">
                            <?php echo esc_html( $st_name ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Hero Input Row -->
                <div class="zabun-hero-row">
                    <div class="zabun-field field-text">
                        <label for="hs-search"><?php esc_html_e( 'Keyword or location', 'zabun-connect' ); ?></label>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input class="zabun-control" id="hs-search" type="text" name="zabun_search" placeholder="<?php esc_attr_e( 'Address, city, reference...', 'zabun-connect' ); ?>" value="<?php echo esc_attr( $curr_search ); ?>" />
                    </div>

                    <div class="zabun-field">
                        <label for="hs-city"><?php esc_html_e( 'City', 'zabun-connect' ); ?></label>
                        <select class="zabun-control" id="hs-city" name="zabun_city">
                            <option value=""><?php esc_html_e( 'All cities', 'zabun-connect' ); ?></option>
                            <?php foreach ( $cities as $c ) : ?>
                                <option value="<?php echo esc_attr( $c ); ?>" <?php selected( $curr_city, $c ); ?>>
                                    <?php echo esc_html( $c ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="zabun-field">
                        <label for="hs-type"><?php esc_html_e( 'Property type', 'zabun-connect' ); ?></label>
                        <select class="zabun-control" id="hs-type" name="zabun_type">
                            <option value=""><?php esc_html_e( 'All types', 'zabun-connect' ); ?></option>
                            <?php foreach ( $types as $tp ) : ?>
                                <option value="<?php echo esc_attr( $tp ); ?>" <?php selected( $curr_type, $tp ); ?>>
                                    <?php echo esc_html( $tp ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="zabun-field">
                        <label for="hs-maxprice"><?php esc_html_e( 'Max price', 'zabun-connect' ); ?></label>
                        <select class="zabun-control" id="hs-maxprice" name="zabun_max_price">
                            <?php foreach ( $price_options as $p_val => $p_label ) : ?>
                                <option value="<?php echo esc_attr( $p_val ); ?>" <?php selected( $curr_max_price, $p_val ); ?>>
                                    <?php echo esc_html( $p_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="zabun-hero-actions">
                        <button class="zabun-btn-more" id="more-toggle" type="button" aria-expanded="false">
                            <span><?php esc_html_e( 'More filters', 'zabun-connect' ); ?></span>
                            <svg width="11" height="7" viewBox="0 0 12 8"><path d="M1 1l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button class="zabun-btn-search" type="submit">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            <span><?php esc_html_e( 'Search', 'zabun-connect' ); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Expanded Filters Drawer -->
                <div class="zabun-expanded-drawer" id="expanded-panel">
                    <div class="zabun-expanded-grid">
                        <div class="zabun-field">
                            <label><?php esc_html_e( 'Min & max price', 'zabun-connect' ); ?></label>
                            <div class="zabun-range-pair">
                                <select class="zabun-control" name="zabun_min_price">
                                    <?php foreach ( $price_options as $p_val => $p_label ) : ?>
                                        <option value="<?php echo esc_attr( $p_val ); ?>" <?php selected( $curr_min_price, $p_val ); ?>>
                                            <?php echo esc_html( $p_label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="to">—</span>
                                <select class="zabun-control" name="zabun_max_price">
                                    <?php foreach ( $price_options as $p_val => $p_label ) : ?>
                                        <option value="<?php echo esc_attr( $p_val ); ?>" <?php selected( $curr_max_price, $p_val ); ?>>
                                            <?php echo esc_html( $p_label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="zabun-field">
                            <label><?php esc_html_e( 'Bedrooms', 'zabun-connect' ); ?></label>
                            <div class="zabun-btngroup" data-group="bedrooms">
                                <button type="button" data-val="" class="<?php echo empty( $curr_beds ) ? 'active' : ''; ?>"><?php esc_html_e( 'Any', 'zabun-connect' ); ?></button>
                                <button type="button" data-val="1" class="<?php echo $curr_beds === '1' ? 'active' : ''; ?>">1+</button>
                                <button type="button" data-val="2" class="<?php echo $curr_beds === '2' ? 'active' : ''; ?>">2+</button>
                                <button type="button" data-val="3" class="<?php echo $curr_beds === '3' ? 'active' : ''; ?>">3+</button>
                                <button type="button" data-val="4" class="<?php echo $curr_beds === '4' ? 'active' : ''; ?>">4+</button>
                                <button type="button" data-val="5" class="<?php echo $curr_beds === '5' ? 'active' : ''; ?>">5+</button>
                            </div>
                        </div>

                        <div class="zabun-field">
                            <label><?php esc_html_e( 'Bathrooms', 'zabun-connect' ); ?></label>
                            <div class="zabun-btngroup" data-group="bathrooms">
                                <button type="button" data-val="" class="<?php echo empty( $curr_baths ) ? 'active' : ''; ?>"><?php esc_html_e( 'Any', 'zabun-connect' ); ?></button>
                                <button type="button" data-val="1" class="<?php echo $curr_baths === '1' ? 'active' : ''; ?>">1+</button>
                                <button type="button" data-val="2" class="<?php echo $curr_baths === '2' ? 'active' : ''; ?>">2+</button>
                                <button type="button" data-val="3" class="<?php echo $curr_baths === '3' ? 'active' : ''; ?>">3+</button>
                            </div>
                        </div>

                        <div class="zabun-field">
                            <label><?php esc_html_e( 'Surface (m²)', 'zabun-connect' ); ?></label>
                            <div class="zabun-range-pair">
                                <input class="zabun-control" type="number" name="zabun_min_area" placeholder="<?php esc_attr_e( 'Min', 'zabun-connect' ); ?>" value="<?php echo esc_attr( $curr_min_area ); ?>" />
                                <span class="to">—</span>
                                <input class="zabun-control" type="number" name="zabun_max_area" placeholder="<?php esc_attr_e( 'Max', 'zabun-connect' ); ?>" value="<?php echo esc_attr( $curr_max_area ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="zabun-expanded-foot">
                        <div class="zabun-sort-line">
                            <label for="hs-sort"><?php esc_html_e( 'Sort by', 'zabun-connect' ); ?></label>
                            <select class="zabun-control" id="hs-sort" name="zabun_orderby">
                                <option value="date" <?php selected( $curr_order, 'date' ); ?>><?php esc_html_e( 'Newest', 'zabun-connect' ); ?></option>
                                <option value="price_asc" <?php selected( $curr_order, 'price_asc' ); ?>><?php esc_html_e( 'Price: Low to High', 'zabun-connect' ); ?></option>
                                <option value="price_desc" <?php selected( $curr_order, 'price_desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'zabun-connect' ); ?></option>
                                <option value="living_area_desc" <?php selected( $curr_order, 'living_area_desc' ); ?>><?php esc_html_e( 'Surface: Large to Small', 'zabun-connect' ); ?></option>
                            </select>
                        </div>
                        <div class="zabun-foot-actions">
                            <button class="zabun-link-reset" type="reset">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12a9 9 0 1 1 3 6.7"/><path d="M3 21v-5h5"/></svg>
                                <span><?php esc_html_e( 'Reset filters', 'zabun-connect' ); ?></span>
                            </button>
                            <button class="zabun-btn-search" type="submit">
                                <span><?php esc_html_e( 'Apply filters', 'zabun-connect' ); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render Featured Listings Shortcode [zabun_featured].
     *
     * @param array $atts
     * @return string
     */
    public function render_featured_shortcode( $atts = [] ): string {
        $atts = shortcode_atts(
            [
                'columns'    => 3,
                'limit'      => 3,
                'status'     => '',
                'pagination' => 'no',
            ],
            $atts,
            'zabun_featured'
        );

        return $this->render_grid_shortcode( $atts );
    }
}
