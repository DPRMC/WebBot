<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\Step\InvalidMethodForStep;
use GuzzleHttp\Psr7\Request;

class Step {
    protected $method;
    protected $url;
    protected $headers;
    protected $body;
    protected $timeout;
    protected $formParams;
    protected $responseBody;


    public function __construct() {
        $this->headers    = [];
        $this->formParams = [];
    }

    /**
     * @link http://docs.guzzlephp.org/en/stable/request-options.html#verify Explains the "verify" option set to false.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return \GuzzleHttp\Psr7\Response;
     */
    public function run( &$client ) {
        return $client->send( $this->getRequestObject(), [ 'form_params'     => $this->formParams,
                                                           'allow_redirects' => true,
                                                           'debug'           => false,
                                                           'timeout'         => $this->timeout,
                                                           'sink'            => null,
                                                           'verify'          => false,
        ] );
    }


    protected function getRequestObject() {
        return new Request( $this->method, $this->url, $this->headers, $this->body );
    }

    /**
     * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     *
     * @param string $method The HTTP request method you want to use for this Step. Usually GET or POST
     *
     * @return $this
     * @throws \DPRMC\WebBot\Exceptions\Step\InvalidMethodForStep
     */
    public function setMethod( $method ) {
        if ( ! in_array( strtoupper( $method ), [ 'GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH' ] ) ):
            throw new InvalidMethodForStep( "" );
        endif;
        $this->method = strtoupper( $method );

        return $this;
    }

    /**
     * @param string $url The URL that is the target of the Request for this Step. Case sensitive.
     *
     * @return $this
     */
    public function setUrl( $url ) {
        $this->url = strtoupper( $url );

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout( $timeout ) {
        $this->timeout = (int)$timeout;

        return $this;
    }


    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addFormParam( $name, $value ) {
        $this->formParams[ $name ] = $value;

        return $this;
    }


}