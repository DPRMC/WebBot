<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\WebBot\ResponseObjectNotSetForIndex;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;


class WebBot {


    /**
     * @var \GuzzleHttp\Client $client
     */
    protected $client;


    /**
     * @var array $steps An array of Step objects where the index is the step name.
     */
    protected $steps;

    /**
     * @var array $stepResults An array of StepResult objects where the index is the step name.
     */
    protected $stepResults;

    /**
     * @var array $responses An array of Response objects where the index is the step name.
     */
    protected $responses;


    public static function instance() {
        return new static;
    }

    /**
     * WebBot constructor.
     * @link http://docs.guzzlephp.org/en/stable/quickstart.html#cookies Explains the cookies setting in the Client.
     */
    public function __construct() {
        $this->client = new Client( [ 'cookies' => true ] );
    }


    /**
     * Fluent method to add a Step object to an existing WebBot. You need to specify a unique 'name' for this Step.
     * The name you give to the Step will be used by the WebBot.
     *
     * @param string             $name
     * @param \DPRMC\WebBot\Step $step
     * @param boolean            $debug
     *
     * @return $this Required for Fluent interface.
     */
    public function addStep( $name, $step, $debug = false ) {
        $step->setDebug( $debug );
        $this->steps[ $name ] = $step;
        $this->initResponseElement( $name );


        return $this;
    }

    /**
     * @return $this
     */
    public function run() {
        foreach ( $this->steps as $name => $step ):
            $stepResult = $this->runStep( $name, $step );
            // @TODO Create a StepResult method called shouldBreakRun(), so the WebBot will return $this and not execute any further Steps.
            if ( $stepResult->breaks() ):
                return $this;
            endif;
        endforeach;

        return $this;
    }

    protected function initStepResultElements() {
        foreach ( $this->steps as $name => $step ):
            $this->initStepResultElement( $name );
        endforeach;
    }

    /**
     * @param string             $name
     * @param \DPRMC\WebBot\Step $step
     *
     * @return \DPRMC\WebBot\StepResult;
     */
    protected function runStep( $name, $step ) {
        $this->initResponseElement( $name );
        $this->initStepResultElement( $name );
        $stepResult                 = $step->run( $this->client );
        $this->stepResults[ $name ] = $stepResult;
        $this->responses[ $name ]   = $stepResult->getResponse();

        return $stepResult;
    }

    /**
     * Before I run a Step, the WebBot needs to initialize the array index where I will be storing the StepResult.
     *
     * @param string $name
     */
    protected function initStepResultElement( $name ) {
        $this->stepResults[ $name ] = null;
    }

    /**
     * Before I run a Step, the WebBot needs to initialize the array index where I will be storing the response.
     *
     * @param string $name
     */
    protected function initResponseElement( $name ) {
        $this->responses[ $name ] = new Response();
    }

    /**
     * @param string $name The step name of the Response who's body you want.
     *
     * @return \GuzzleHttp\Psr7\Stream|\Psr\Http\Message\StreamInterface
     * @throws \DPRMC\WebBot\Exceptions\WebBot\ResponseObjectNotSetForIndex
     */
    public function getResponseBody( $name ) {
        /**
         * @var \GuzzleHttp\Psr7\Response $response
         */
        $response = $this->responses[ $name ];

        if ( Response::class !== get_class( $response ) ):
            throw new ResponseObjectNotSetForIndex( "The response object was not set for the step labeled: " . $name );
        endif;

        return $response->getBody();
    }

    /**
     * @param $name
     *
     * @return \DPRMC\WebBot\StepResult Could be Success, Failure, or ContinueToNextStep
     */
    public function getStepResult( $name ) {
        return $this->stepResults[ $name ];
    }

    /**
     * @return array An array of StepResult objects.
     */
    public function getStepResults() {
        return $this->stepResults;
    }
}