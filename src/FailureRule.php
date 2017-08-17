<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\FailureRule\Triggered;
use DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType;
use GuzzleHttp\Psr7\Response;

class FailureRule {

    /**
     * A list of constants that represent the different types of failure rules that you can apply.
     */
    const regex = 'regex';

    const notFound = 'notFound';

    /**
     * An array defining all of the failure rule types that I can process.
     */
    const types = [
        self::regex,
        self::notFound,
    ];

    /**
     * @var string $type The type of failure rule this is. Valid types are in the const array self::types
     */
    protected $type;

    /**
     * @var mixed An parameters that you want to pass to the regex rule that will be run as defined by the $type of
     *      failure rule you want to run. This could be an array, scalar, object, anything... Just depends on what type
     *      of rule you are running.
     */
    protected $parameters;

    public function __construct() {
    }

    /**
     * Fluent method to set the type of failure rule this is.
     *
     * @param $type
     *
     * @return $this
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType
     */
    public function setType( $type ) {
        if ( ! in_array( $type, self::types ) ):
            throw new UndefinedFailureRuleType( "You attempted to set a failure rule type of [" . $this->type . "]" );
        endif;
        $this->type = $type;

        return $this;
    }

    /**
     * A fluent method to set the parameters needed for the FailureRule you are setting.
     *
     * @param $parameters
     *
     * @return $this
     */
    public function setParameters( $parameters ) {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param Response $response
     *
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\Triggered
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType
     * @return void
     */
    public function run( $response ) {

        switch ( $this->type ):
            case self::regex:
                $result = $this->runFailureRuleRegEx( $this->parameters, $response->getBody() );
                break;
            default:
                throw new UndefinedFailureRuleType( "You attempted to run a failure rule type of [" . $this->type . "]" );
        endswitch;
        // TRUE represents a Failure here.
        if ( true === $result ):
            throw new Triggered( "The failure rule was triggered.", $response, $this->type, $this->parameters );
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