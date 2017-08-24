<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\FailureRule\UndefinedSuccessRuleType;
use GuzzleHttp\Psr7\Response;

class SuccessRule {

    /**
     * A list of constants that represent the different types of success rules that you can apply.
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
     * @var string $type The type of success rule this is. Valid types are in the const array self::types
     */
    protected $type;

    /**
     * @var mixed An parameters that you want to pass to the regex rule that will be run as defined by the $type of
     *      success rule you want to run. This could be an array, scalar, object, anything... Just depends on what type
     *      of rule you are running.
     */
    protected $parameters;

    public function __construct() {
    }


    public function setType( $type ) {
        if ( ! in_array( $type, self::types ) ):
            throw new UndefinedSuccessRuleType( "You attempted to set a success rule type of [" . $this->type . "]" );
        endif;
        $this->type = $type;

        return $this;
    }

    /**
     * A fluent method to set the parameters needed for the SuccessRule you are setting.
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
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\UndefinedSuccessRuleType
     * @return \DPRMC\WebBot\StepResult|bool
     */
    public function run( $response ) {

        switch ( $this->type ):
            case self::regex:
                $result = $this->runSuccessRuleRegEx( $this->parameters, $response->getBody() );
                break;
            default:
                throw new UndefinedSuccessRuleType( "You attempted to run a success rule type of [" . $this->type . "]" );
        endswitch;
        // TRUE represents a Failure here.
        if ( true === $result ):
            return new Success( $response );
        endif;

        return false;
    }

    /**
     * @param string $pattern The regex pattern.
     * @param string $string
     *
     * @return bool
     */
    protected function runSuccessRuleRegEx( $pattern, $string ) {
        if ( preg_match( '/' . $pattern . '/', $string ) === 1 ):
            return true;
        endif;

        return false;
    }
}