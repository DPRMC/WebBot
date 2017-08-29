<?php

namespace DPRMC\WebBot\Tests;

use DPRMC\WebBot\ContinueToNextStep;
use DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType;
use DPRMC\WebBot\Exceptions\FailureRule\UndefinedSuccessRuleType;
use DPRMC\WebBot\Failure;
use DPRMC\WebBot\FailureRule;
use DPRMC\WebBot\Step;
use DPRMC\WebBot\Success;
use DPRMC\WebBot\SuccessRule;
use DPRMC\WebBot\WebBot;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class WebBotTest extends TestCase {

    //public function testTEst(){
    //    $step = Step::instance();
    //    var_dump($step); flush(); die();
    //}

    public function testWebBotConstructorShouldReturnWebBotObject() {
        $bot = WebBot::instance();
        $this->assertInstanceOf( WebBot::class, $bot );
    }

    public function testAddStepShouldAddStepObjectToWebBot() {
        $bot = WebBot::instance()->addStep( 'test', new Step() );
        $this->assertInstanceOf( WebBot::class, $bot );
    }


    public function testRunWebBotWithOneStepShouldGetNonEmptyResponseBody() {
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'GET' )->setUrl( 'github.com' )->setTimeout( 10 )->addQueryParam( 'foo', "bar" );
        $body              = WebBot::instance()
            ->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );
        $this->assertNotEmpty( $body );
    }

    public function testRunWebBotWithFormParamsShouldGetValidResponse() {
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'POST' )->setUrl( 'github.com' )->setTimeout( 10 )->addFormParam( 'foo', "bar" );
        $body              = WebBot::instance()
            ->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );
        $this->assertNotEmpty( $body );
    }

    /**
     * Kind of a weird failure rule to test. I request our github page, and set a failure rule for text that I KNOW will
     * be present. There is nothing really causing an error on that page, but this sufficiently tests the code.
     */
    public function testRunWebBotWithFailureRuleShouldCreateFailureStep() {
        $failureRuleOne    = FailureRule::instance()->setType( FailureRule::regex )->setParameters( "DPRMC" );
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addFailureRule( 'testFailureRule', $failureRuleOne )
            ->setBreakOnFailure( true );
        $failure           = WebBot::instance()->addStep( $stepOneNameForBot, $stepOne )->run()->getStepResult( $stepOneNameForBot );
        $this->assertInstanceOf( Failure::class, $failure );
    }


    public function testRunWebBotWithFailureRuleThatDoesNotGetTriggeredShouldReturnNonEmptyResponseBody() {
        $failureRuleOne    = FailureRule::instance()->setType( FailureRule::regex )->setParameters( "youWillNeverSeeThisTextOnThisPage4397ayir7ya" );
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'GET' )->setUrl( 'https://github.com/dprmc/' )->addFailureRule( 'testFailureRule', $failureRuleOne );
        $body              = WebBot::instance()->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );
        $this->assertNotEmpty( $body );
    }

    public function testAddingInvalidFailureRuleTypeShouldThrowException() {
        $this->expectException( UndefinedFailureRuleType::class );
        FailureRule::instance()->setType( 'notValidFailureRuleType' );
    }

    public function testRunningFailureRuleWithInvalidFailureRuleTypeShouldThrowException() {
        $this->expectException( UndefinedFailureRuleType::class );
        FailureRule::instance()->run( new Response(), false );
    }

    public function testRunningSuccessRuleShouldYieldInSuccessStepResult() {
        $successRule       = SuccessRule::instance()->setType( SuccessRule::regex )->setParameters( "DPRMC" );
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addSuccessRule( 'testSuccessRule', $successRule )
            ->setBreakOnSuccess( true );
        $success           = WebBot::instance()->addStep( $stepOneNameForBot, $stepOne )->run()->getStepResult( $stepOneNameForBot );
        $this->assertInstanceOf( Success::class, $success );
    }

    public function testRunningSuccessRuleShouldYieldContinueToNextStepInStepResult() {
        $successRule       = SuccessRule::instance()->setType( SuccessRule::regex )->setParameters( "YouWillNeverFindThisStringwo475owai7erasloery;a" );
        $stepOneNameForBot = 'test';
        $stepOne           = Step::instance()->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addSuccessRule( 'testSuccessRule', $successRule )
            ->setBreakOnSuccess( true );
        $continue          = WebBot::instance()->addStep( $stepOneNameForBot, $stepOne )->run()->getStepResult( $stepOneNameForBot );
        $this->assertInstanceOf( ContinueToNextStep::class, $continue );
    }

    public function testRunningSuccessRuleWithInvalidSuccessRuleTypeShouldThrowException() {
        $this->expectException( UndefinedSuccessRuleType::class );
        SuccessRule::instance()->setType( 'notValidType' )->run( new Response(), false );
    }

    public function testRunningSuccessRuleWithoutSuccessRuleTypeShouldThrowException() {
        $this->expectException( UndefinedSuccessRuleType::class );
        SuccessRule::instance()->run( new Response(), false );
    }

}