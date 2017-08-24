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
    protected $queryParams;
    protected $responseBody;
    protected $successRules;
    protected $failureRules;


    public function __construct() {
        $this->headers      = [];
        $this->formParams   = [];
        $this->successRules = [];
        $this->failureRules = [];
    }

    /**
     * @link http://docs.guzzlephp.org/en/stable/request-options.html#verify Explains the "verify" option set to false.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return \DPRMC\WebBot\StepResult
     */
    public function run( &$client ) {
        /**
         * @var \GuzzleHttp\Psr7\Response $response
         */
        $response = $client->send( $this->getRequestObject(), [ 'form_params'     => $this->formParams,
                                                                'allow_redirects' => true,
                                                                'debug'           => false,
                                                                'timeout'         => $this->timeout,
                                                                'sink'            => null,
                                                                'verify'          => false,
        ] );

        /**
         *
         */
        foreach ( $this->successRules as $successRule ):
            /**
             * @var \DPRMC\WebBot\FailureRule $successRule
             */
            $stepResult = $successRule->run( $response );
            if ( false !== $stepResult ):
                return $stepResult;
            endif;
        endforeach;

        /**
         *
         */
        foreach ( $this->failureRules as $failureRule ):
            /**
             * @var \DPRMC\WebBot\FailureRule $failureRule
             */
            $stepResult = $failureRule->run( $response );
            if ( false !== $stepResult ):
                return $stepResult;
            endif;
        endforeach;

        return new ContinueToNextStep( $response );
    }


    /**
     * @return \GuzzleHttp\Psr7\Request
     */
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

    /**
     * Fluent interface for adding query parameters to be sent with this step.
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addQueryParam( $name, $value ) {
        $this->queryParams[ $name ] = $value;

        return $this;
    }

    /**
     * Fluent method for adding a failure rule to this step.
     *
     * @param string                    $name        The index of the Failure Rule object in this Step.
     * @param \DPRMC\WebBot\FailureRule $failureRule The failure rule object.
     *
     * @return $this
     */
    public function addFailureRule( $name, $failureRule ) {

        $this->failureRules[ $name ] = $failureRule;

        return $this;
    }


}