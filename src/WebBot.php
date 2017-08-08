<?php

namespace DPRMC\WebBot;

use GuzzleHttp\Client;

class WebBot {
    protected $client;
    protected $responses;
    protected $steps;


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
     *
     * @return $this Requied for Fluent interface.
     */
    public function addStep( $name, $step ) {
        $this->steps[ $name ] = $step;

        return $this;
    }

    public function run() {
        foreach ( $this->steps as $name => $step ):
            $this->runStep( $name, $step );
        endforeach;

        return $this;
    }

    /**
     * @param string             $name
     * @param \DPRMC\WebBot\Step $step
     */
    protected function runStep( $name, $step ) {
        $this->initResponseElement( $name );

        $this->responses[ $name ] = $step->run( $this->client );
    }

    /**
     * Before I run a Step, the WebBot needs to initialize the array index where I will be storing the response.
     *
     * @param string $name
     */
    protected function initResponseElement( $name ) {
        $this->responses[ $name ] = null;
    }

    public function getResponseBody( $name ) {
        /**
         * @var \GuzzleHttp\Psr7\Response $response
         */
        $response = $this->responses[ $name ];

        return $response->getBody();
    }
}