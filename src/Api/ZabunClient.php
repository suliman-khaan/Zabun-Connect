<?php

namespace ZabunConnect\Api;

defined( 'ABSPATH' ) || exit;

class ZabunClient {

    public const DEFAULT_BASE_URL = 'https://gateway-cmsapi.v2.zabun.be';
    public const DEFAULT_MEDIA_ID = 14;

    /**
     * API Key.
     *
     * @var string
     */
    private $api_key;

    /**
     * Client ID.
     *
     * @var string
     */
    private $client_id;

    /**
     * Server ID.
     *
     * @var string
     */
    private $server_id;

    /**
     * Company ID (X-CLIENT-ID).
     *
     * @var string
     */
    private $x_client_id;

    /**
     * Media ID.
     *
     * @var int
     */
    private $media_id;

    /**
     * Base URL for the Zabun API.
     *
     * @var string
     */
    private $base_url;

    /**
     * ZabunClient constructor.
     *
     * @param array|string|null $config Optional custom configuration or api_key string.
     * @param string|null $base_url Optional custom base URL.
     */
    public function __construct( $config = null, ?string $base_url = null ) {
        if ( is_array( $config ) ) {
            $this->api_key     = $config['api_key'] ?? (string) get_option( 'zabun_connect_api_key', '' );
            $this->client_id   = $config['client_id'] ?? (string) get_option( 'zabun_connect_client_id', '' );
            $this->server_id   = $config['server_id'] ?? (string) get_option( 'zabun_connect_server_id', '' );
            $this->x_client_id = $config['x_client_id'] ?? (string) get_option( 'zabun_connect_x_client_id', '' );
            $this->media_id    = isset( $config['media_id'] ) ? (int) $config['media_id'] : (int) get_option( 'zabun_connect_media_id', self::DEFAULT_MEDIA_ID );
            $base_url          = $config['base_url'] ?? $base_url;
        } else {
            $this->api_key     = is_string( $config ) && ! empty( $config ) ? $config : (string) get_option( 'zabun_connect_api_key', '' );
            $this->client_id   = (string) get_option( 'zabun_connect_client_id', '' );
            $this->server_id   = (string) get_option( 'zabun_connect_server_id', '' );
            $this->x_client_id = (string) get_option( 'zabun_connect_x_client_id', '' );
            $this->media_id    = (int) get_option( 'zabun_connect_media_id', self::DEFAULT_MEDIA_ID );
        }

        $saved_base_url = (string) get_option( 'zabun_connect_base_url', '' );
        $this->base_url = ! empty( $base_url ) 
            ? untrailingslashit( $base_url ) 
            : ( ! empty( $saved_base_url ) ? untrailingslashit( $saved_base_url ) : self::DEFAULT_BASE_URL );
    }

    /**
     * Execute an HTTP request against the Zabun API.
     *
     * @param string $endpoint API endpoint path.
     * @param string $method HTTP method (GET, POST, etc.).
     * @param array $query_params URL query parameters.
     * @param array $body Request body data.
     * @return array Decoded response payload.
     * @throws ZabunException When the request fails.
     */
    public function request( string $endpoint, string $method = 'GET', array $query_params = [], array $body = [] ): array {
        if ( empty( $this->api_key ) ) {
            throw new ZabunException( __( 'Zabun API key is not configured.', 'zabun-connect' ), 401 );
        }

        $url = $this->base_url . '/' . ltrim( $endpoint, '/' );

        if ( ! empty( $query_params ) ) {
            $url = add_query_arg( $query_params, $url );
        }

        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'api_key'      => $this->api_key,
        ];

        if ( ! empty( $this->client_id ) ) {
            $headers['client_id'] = $this->client_id;
        }
        if ( ! empty( $this->server_id ) ) {
            $headers['server_id'] = $this->server_id;
        }
        if ( ! empty( $this->x_client_id ) ) {
            $headers['X-CLIENT-ID'] = $this->x_client_id;
        }

        $args = [
            'method'      => strtoupper( $method ),
            'headers'     => $headers,
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => true,
        ];

        if ( in_array( strtoupper( $method ), [ 'POST', 'PUT', 'PATCH' ], true ) ) {
            $args['body'] = wp_json_encode( ! empty( $body ) ? $body : new \stdClass() );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            throw new ZabunException(
                'HTTP Request failed: ' . $response->get_error_message(),
                500
            );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $raw_body    = wp_remote_retrieve_body( $response );
        $data        = json_decode( $raw_body, true );

        if ( $status_code < 200 || $status_code >= 300 ) {
            $error_message = isset( $data['message'] ) 
                ? (string) $data['message'] 
                : ( isset( $data['error'] ) ? (string) $data['error'] : ( ! empty( $raw_body ) ? strip_tags( substr( $raw_body, 0, 200 ) ) : 'HTTP ' . $status_code ) );

            throw new ZabunException(
                sprintf( 'Zabun API returned status %d: %s', $status_code, $error_message ),
                $status_code,
                $data
            );
        }

        if ( json_last_error() !== JSON_ERROR_NONE && ! empty( $raw_body ) ) {
            // Response might be plain text (e.g. heartbeat returns "V1 ...")
            return [ 'raw' => $raw_body, 'success' => true ];
        }

        return is_array( $data ) ? $data : [ 'data' => $data ];
    }

    /**
     * Test connection to the Zabun API via heartbeat or option_items.
     *
     * @return array
     * @throws ZabunException
     */
    public function test_connection(): array {
        // Zabun API official heartbeat endpoint
        return $this->request( 'auth/v1/heartbeat', 'GET' );
    }

    /**
     * Fetch property listings via media-sync or property search.
     *
     * @param array $params Query filters or search body.
     * @return array
     * @throws ZabunException
     */
    public function fetch_listings( array $params = [] ): array {
        $sync_source = (string) get_option( 'zabun_connect_sync_source', 'all' );
        $media_id    = $this->media_id ?: self::DEFAULT_MEDIA_ID;
        
        $body = [
            'paging' => [
                'page' => $params['page'] ?? 0,
                'size' => $params['limit'] ?? 100,
            ],
        ];

        // If explicitly set to media_sync, query the specific media ID feed (e.g. 354 items)
        if ( $sync_source === 'media_sync' ) {
            try {
                return $this->request( "api/v1/property/media-sync/{$media_id}", 'POST', [], $body );
            } catch ( ZabunException $e ) {
                return $this->request( 'api/v1/property/search', 'POST', [], $body );
            }
        }

        // Default: query the search endpoint (returns all 838 CRM listings)
        return $this->request( 'api/v1/property/search', 'POST', [], $body );
    }

    /**
     * Fetch single property details by ID.
     *
     * @param string $id Property auto ID.
     * @return array
     * @throws ZabunException
     */
    public function fetch_listing( string $id ): array {
        return $this->request( 'api/v1/property/' . rawurlencode( $id ), 'GET', [ 'extended' => 'true' ] );
    }
}
