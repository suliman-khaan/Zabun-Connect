<?php

namespace ZabunConnect\Admin;

use ZabunConnect\Api\ZabunClient;
use ZabunConnect\Api\ZabunException;

defined( 'ABSPATH' ) || exit;

class ConnectionTest {

    /**
     * Singleton instance.
     *
     * @var ConnectionTest|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return ConnectionTest
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize AJAX action.
     */
    public function init(): void {
        add_action( 'wp_ajax_zabun_test_connection', [ $this, 'handle_ajax_test' ] );
    }

    /**
     * Handle AJAX connection test request.
     */
    public function handle_ajax_test(): void {
        check_ajax_referer( 'zabun_connect_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized access.', 'zabun-connect' ) ], 403 );
        }

        $api_key     = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
        $client_id   = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';
        $server_id   = isset( $_POST['server_id'] ) ? sanitize_text_field( wp_unslash( $_POST['server_id'] ) ) : '';
        $x_client_id = isset( $_POST['x_client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['x_client_id'] ) ) : '';
        $base_url    = isset( $_POST['base_url'] ) ? esc_url_raw( wp_unslash( $_POST['base_url'] ) ) : '';

        // If submitted key is masked or empty, fall back to stored key
        if ( empty( $api_key ) || strpos( $api_key, '•' ) !== false || strpos( $api_key, '*' ) !== false ) {
            $api_key = (string) get_option( 'zabun_connect_api_key', '' );
        }
        if ( empty( $client_id ) || strpos( $client_id, '•' ) !== false ) {
            $client_id = (string) get_option( 'zabun_connect_client_id', '' );
        }
        if ( empty( $server_id ) || strpos( $server_id, '•' ) !== false ) {
            $server_id = (string) get_option( 'zabun_connect_server_id', '' );
        }
        if ( empty( $x_client_id ) || strpos( $x_client_id, '•' ) !== false ) {
            $x_client_id = (string) get_option( 'zabun_connect_x_client_id', '' );
        }

        if ( empty( $api_key ) ) {
            wp_send_json_error( [
                'message' => __( 'Please provide a Zabun API Key first.', 'zabun-connect' ),
            ], 400 );
        }

        try {
            $client = new ZabunClient( [
                'api_key'     => $api_key,
                'client_id'   => $client_id,
                'server_id'   => $server_id,
                'x_client_id' => $x_client_id,
                'base_url'    => ! empty( $base_url ) ? $base_url : null,
            ] );

            $result = $client->test_connection();

            wp_send_json_success( [
                'message' => __( 'Connection successful! Successfully connected and authenticated with Zabun CRM API.', 'zabun-connect' ),
                'data'    => $result,
            ] );
        } catch ( ZabunException $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'Connection failed: %s', 'zabun-connect' ), $e->getMessage() ),
            ], ( $e->getCode() >= 400 && $e->getCode() < 600 ) ? $e->getCode() : 500 );
        } catch ( \Throwable $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'Connection error: %s', 'zabun-connect' ), $e->getMessage() ),
            ], 500 );
        }
    }
}
