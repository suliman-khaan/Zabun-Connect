<?php

namespace ZabunConnect\Api;

defined( 'ABSPATH' ) || exit;

class ZabunException extends \Exception {

    /**
     * Optional response payload from the API.
     *
     * @var mixed
     */
    private $response_data;

    public function __construct( string $message = '', int $code = 0, $response_data = null, ?\Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
        $this->response_data = $response_data;
    }

    /**
     * Get API response data.
     *
     * @return mixed
     */
    public function get_response_data() {
        return $this->response_data;
    }
}
