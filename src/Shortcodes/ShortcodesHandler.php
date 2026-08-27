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
    public function render_card_html( array $item, string $detail_url_base = '' ): string {
        $detail_link = ! empty( $detail_url_base ) 
            ? add_query_arg( 'property_id', $item['external_id'], $detail_url_base ) 
            : add_query_arg( 'property_id', $item['external_id'] );

        $status_label = ucwords( str_replace( '_', ' ', $item['status'] ?? 'for sale' ) );
        $status_class = 'status-' . sanitize_html_class( $item['status'] ?? 'for_sale' );
        
        $price_formatted = ! empty( $item['price'] ) 
            ? '€ ' . number_format( (float) $item['price'], 0, ',', '.' ) 
            : __( 'Price on request', 'zabun-connect' );

        $freq = ! empty( $item['price_frequency'] ) ? ' / ' . esc_html( $item['price_frequency'] ) : '';

        ob_start();
        ?>
        <article class="zabun-card">
            <div class="zabun-card-media">
                <span class="zabun-card-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
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
                <div class="zabun-card-price">
                    <?php echo esc_html( $price_formatted ); ?><span class="zabun-card-price-freq"><?php echo esc_html( $freq ); ?></span>
                </div>
                <h3 class="zabun-card-title">
                    <a href="<?php echo esc_url( $detail_link ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                </h3>
                <?php if ( ! empty( $item['address'] ) || ! empty( $item['city'] ) ) : ?>
                    <div class="zabun-card-address">
                        <span>📍 <?php echo esc_html( trim( ( $item['address'] ? $item['address'] . ', ' : '' ) . $item['city'] ) ); ?></span>
                    </div>
                <?php endif; ?>

                <div class="zabun-card-facts">
                    <?php if ( ! empty( $item['bedrooms'] ) ) : ?>
                        <div class="zabun-fact-item">
                            <span>🛏️</span> <strong><?php echo esc_html( $item['bedrooms'] ); ?></strong> <?php esc_html_e( 'Beds', 'zabun-connect' ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $item['bathrooms'] ) ) : ?>
                        <div class="zabun-fact-item">
                            <span>🚿</span> <strong><?php echo esc_html( $item['bathrooms'] ); ?></strong> <?php esc_html_e( 'Baths', 'zabun-connect' ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $item['living_area'] ) ) : ?>
                        <div class="zabun-fact-item">
                            <span>📐</span> <strong><?php echo esc_html( $item['living_area'] ); ?></strong> m²
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render Single Property Detail Shortcode [zabun_detail].
     *
     * @param array $atts
     * @return string
     */
    public function render_detail_shortcode( $atts = [] ): string {
        $this->enqueue_assets();

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

        $main_img = ! empty( $gallery[0] ) ? $gallery[0] : $property['featured_image'];
        $price_formatted = ! empty( $property['price'] ) 
            ? '€ ' . number_format( (float) $property['price'], 0, ',', '.' ) 
            : __( 'Price on request', 'zabun-connect' );

        $raw = $property['raw_data'] ?? [];
        $description = $raw['description'] ?? ( $raw['remarks'] ?? '' );

        ob_start();
        ?>
        <div class="zabun-detail-wrapper">
            <div class="zabun-detail-header">
                <div class="zabun-detail-title-block">
                    <h1><?php echo esc_html( $property['title'] ); ?></h1>
                    <p class="zabun-card-address">
                        📍 <?php echo esc_html( trim( ( $property['address'] ? $property['address'] . ', ' : '' ) . ( $property['postal_code'] ? $property['postal_code'] . ' ' : '' ) . $property['city'] ) ); ?>
                    </p>
                </div>
                <div class="zabun-detail-price">
                    <?php echo esc_html( $price_formatted ); ?>
                </div>
            </div>

            <?php if ( ! empty( $gallery ) ) : ?>
                <div class="zabun-detail-gallery">
                    <div class="zabun-gallery-main">
                        <img src="<?php echo esc_url( $main_img ); ?>" alt="<?php echo esc_attr( $property['title'] ); ?>" />
                    </div>
                    <?php if ( count( $gallery ) > 1 ) : ?>
                        <div class="zabun-gallery-thumbs">
                            <?php foreach ( $gallery as $index => $img_url ) : ?>
                                <div class="zabun-thumb-item <?php echo $index === 0 ? 'active' : ''; ?>" data-full-img="<?php echo esc_url( $img_url ); ?>">
                                    <img src="<?php echo esc_url( $img_url ); ?>" alt="Gallery Image <?php echo $index + 1; ?>" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="zabun-detail-grid">
                <div class="zabun-detail-main">
                    <h2><?php esc_html_e( 'Property Overview', 'zabun-connect' ); ?></h2>
                    <table class="zabun-facts-table">
                        <tbody>
                            <tr>
                                <th><?php esc_html_e( 'Status', 'zabun-connect' ); ?></th>
                                <td><?php echo esc_html( ucwords( str_replace( '_', ' ', $property['status'] ) ) ); ?></td>
                            </tr>
                            <?php if ( ! empty( $property['property_type'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Type', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['property_type'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['bedrooms'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Bedrooms', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['bedrooms'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['bathrooms'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Bathrooms', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['bathrooms'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['living_area'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Habitable Surface', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['living_area'] ); ?> m²</td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['land_area'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Plot Surface', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['land_area'] ); ?> m²</td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( ! empty( $property['epc_value'] ) ) : ?>
                                <tr>
                                    <th><?php esc_html_e( 'Energy Class (EPC)', 'zabun-connect' ); ?></th>
                                    <td><?php echo esc_html( $property['epc_value'] ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th><?php esc_html_e( 'Reference', 'zabun-connect' ); ?></th>
                                <td><?php echo esc_html( $property['external_id'] ); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if ( ! empty( $description ) ) : ?>
                        <h2><?php esc_html_e( 'Description', 'zabun-connect' ); ?></h2>
                        <div class="zabun-description">
                            <?php echo wp_kses_post( wpautop( $description ) ); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="zabun-detail-sidebar">
                    <div class="zabun-sidebar-card">
                        <h3><?php esc_html_e( 'Interested in this property?', 'zabun-connect' ); ?></h3>
                        <p><?php esc_html_e( 'Contact us today for more information or to schedule a viewing.', 'zabun-connect' ); ?></p>
                        <?php
                        // Allow plugins or theme forms to hook into the inquiry section
                        do_action( 'zabun_property_inquiry_form', $property );
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render Property Search Shortcode [zabun_search].
     *
     * @param array $atts
     * @return string
     */
    public function render_search_shortcode( $atts = [] ): string {
        $this->enqueue_assets();

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
        $statuses = $repo->get_distinct_statuses();

        ob_start();
        ?>
        <div class="zabun-search-bar">
            <form method="get" action="<?php echo esc_url( $atts['action_url'] ); ?>" class="zabun-search-form">
                <div class="zabun-form-field">
                    <label for="zabun-search-input"><?php esc_html_e( 'Keyword', 'zabun-connect' ); ?></label>
                    <input type="text" id="zabun-search-input" name="zabun_search" placeholder="<?php esc_attr_e( 'City, address...', 'zabun-connect' ); ?>" value="<?php echo esc_attr( $_GET['zabun_search'] ?? '' ); ?>" />
                </div>

                <?php if ( ! empty( $statuses ) ) : ?>
                    <div class="zabun-form-field">
                        <label for="zabun-status-select"><?php esc_html_e( 'Status', 'zabun-connect' ); ?></label>
                        <select id="zabun-status-select" name="zabun_status">
                            <option value=""><?php esc_html_e( 'All Statuses', 'zabun-connect' ); ?></option>
                            <?php foreach ( $statuses as $st ) : ?>
                                <option value="<?php echo esc_attr( $st ); ?>" <?php selected( $_GET['zabun_status'] ?? '', $st ); ?>>
                                    <?php echo esc_html( ucwords( str_replace( '_', ' ', $st ) ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $cities ) ) : ?>
                    <div class="zabun-form-field">
                        <label for="zabun-city-select"><?php esc_html_e( 'City', 'zabun-connect' ); ?></label>
                        <select id="zabun-city-select" name="zabun_city">
                            <option value=""><?php esc_html_e( 'All Cities', 'zabun-connect' ); ?></option>
                            <?php foreach ( $cities as $c ) : ?>
                                <option value="<?php echo esc_attr( $c ); ?>" <?php selected( $_GET['zabun_city'] ?? '', $c ); ?>>
                                    <?php echo esc_html( $c ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $types ) ) : ?>
                    <div class="zabun-form-field">
                        <label for="zabun-type-select"><?php esc_html_e( 'Type', 'zabun-connect' ); ?></label>
                        <select id="zabun-type-select" name="zabun_type">
                            <option value=""><?php esc_html_e( 'All Types', 'zabun-connect' ); ?></option>
                            <?php foreach ( $types as $tp ) : ?>
                                <option value="<?php echo esc_attr( $tp ); ?>" <?php selected( $_GET['zabun_type'] ?? '', $tp ); ?>>
                                    <?php echo esc_html( $tp ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="zabun-form-field">
                    <button type="submit" class="zabun-search-submit">
                        <?php esc_html_e( 'Search', 'zabun-connect' ); ?>
                    </button>
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
