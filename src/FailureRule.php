<?php

namespace DPRMC\WebBot;

use DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType;
use GuzzleHttp\Psr7\Response;

class FailureRule {

    /**
     * A list of constants that represent the different types of failure rules that you can apply.
     */
    const regex           = 'regex';
    const responseCodeNot = 'responseCodeNot'; // Typically 200 is passed in to this failure rule.
    const notFound        = 'notFound';

    /**
     * An array defining all of the failure rule types that I can process.
     */
    const types = [
        self::regex,
        self::responseCodeNot,
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

    public static function instance() {
        return new static;
    }

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
     * @param bool     $breaksOnFailure
     *
     * @return bool|\DPRMC\WebBot\Failure
     * @throws \DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType
     */
    public function run( $response, $breaksOnFailure ) {

        switch ( $this->type ):
            case self::regex:
                $result = $this->runFailureRuleRegEx( $this->parameters, $response->getBody() );
                break;
            case self::responseCodeNot:
                $result = $this->runFailureRuleNotResponseCode( 200, $response->getStatusCode() );
                break;
            default:
                throw new UndefinedFailureRuleType( "You attempted to run a failure rule type of [" . $this->type . "]" );
        endswitch;
        // TRUE represents a Failure here.
        if ( true === $result ):
            return new Failure( $response, $breaksOnFailure );
        endif;

        return false;
    }

    /**
     * @param string $pattern The regex pattern.
     * @param string $string
     *
     * @return bool True means failure
     */
    protected function runFailureRuleRegEx( $pattern, $string ) {
        if ( preg_match( '/' . $pattern . '/', $string ) === 1 ):
            return true;
        endif;

        return false;
    }

    /**
     * Pass in the code that would indicate success. Any other code indicates a failure.
     *
     * @param integer $codeThatMeansSuccess Typically 200
     * @param         $actual
     *
     * @return bool
     */
    protected function runFailureRuleNotResponseCode( $codeThatMeansSuccess, $actual ) {
        if ( $codeThatMeansSuccess == $actual ):
            return true;
        endif;

        return false;
    }
}