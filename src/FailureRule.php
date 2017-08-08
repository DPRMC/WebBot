<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\FailureRule\Triggered;
use DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType;
use GuzzleHttp\Psr7\Response;

class FailureRule {

    const regex = 'regex';

    const types = [
        self::regex,
    ];

    protected $type;
    protected $parameters;

    public function __construct() {
    }

    /**
     * @param Response $response
     *
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\Triggered
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType
     */
    public function run( $response ) {

        switch ( $this->type ):
            case self::regex:
                $result = $this->runFailureRuleRegEx( $this->parameters, $response->getBody() );
                break;
            default:
                throw new UndefinedFailureRuleType( "You attempted to run a failure rule type of [" . $this->type . "]" );
                break;
        endswitch;
        // TRUE represents a Failure here.
        if ( $result === true ):
            throw new Triggered( "The failure rule was triggered." );
        endif;
    }

    /**
     * @param string $pattern The regex pattern.
     * @param string $string
     *
     * @return bool
     */
    protected function runFailureRuleRegEx( $pattern, $string ) {
        if ( preg_match( '/' . $pattern . '/', $string ) === 1 ):
            return true;
        endif;

        return false;
    }
}