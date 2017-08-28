<?php

namespace DPRMC\WebBot;

use GuzzleHttp\Psr7\Response;

abstract class StepResult {

    /**
     * @var Response $response The response from the Step that triggered this StepResult object to be returned.
     */
    protected $response;

    /**
     * @var bool $breaksOnFailure Set to true if this Step should stop the processing of the WebBot
     */
    protected $breaks = false;


    /**
     * StepResult constructor.
     *
     * @param null $response
     * @param      $breaks
     */
    public function __construct( $response = null, $breaks ) {
        $this->response = $response;
        $this->setBreaks( $breaks );
    }

    public function getResponse() {
        return $this->response;
    }

    /**
     * @return bool Getter that lets the WebBot know if it should stop processing.
     */
    public function breaks() {
        return $this->breaks;
    }

    /**
     * @param bool $break Should this Step halt processing of the WebBot?
     */
    public function setBreaks( $break ) {
        $this->breaks = $break;
    }
}