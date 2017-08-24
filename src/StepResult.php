<?php

namespace DPRMC\WebBot;

use GuzzleHttp\Psr7\Response;

abstract class StepResult {

    /**
     * @var Response $response The response from the Step that triggered this StepResult object to be returned.
     */
    protected $response;

    /**
     * StepResult constructor.
     *
     * @param Response $response
     */
    public function __construct( $response = null ) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }
}