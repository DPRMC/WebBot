<?php

namespace DPRMC\WebBot\Exceptions\FailureRule;

class Triggered extends \Exception {

    /**
     * @var \GuzzleHttp\Psr7\Response $response The response that triggered the error.
     */
    protected $response;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var mixed $parameters
     */
    protected $parameters;

    /**
     * Triggered constructor.
     *
     * @param string                    $message
     * @param \GuzzleHttp\Psr7\Response $response
     * @param string                    $type
     * @param mixed                     $parameters
     */
    public function __construct( $message = "", $response, $type, $parameters ) {

        $code     = 0;
        $previous = null;
        parent::__construct( $message, $code, $previous );
        $this->response = $response;
    }

    /**
     * Getter
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getResponse() {
        return $this->response;
    }
}