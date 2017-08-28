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

    public function testWebBotConstructorShouldReturnWebBotObject() {
        $bot = new WebBot();
        $this->assertInstanceOf( WebBot::class, $bot );
    }

    public function testAddStepShouldAddStepObjectToWebBot() {
        $bot = new WebBot();
        $bot->addStep( 'test', new Step() );
        $this->assertInstanceOf( WebBot::class, $bot );
    }

    /**
     * @group md
     */
    public function testRunWebBotWithOneStepShouldGetNonEmptyResponseBody() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';
        $stepOne           = new Step();
        $stepOne->setMethod( 'GET' )->setUrl( 'github.com' )->setTimeout( 10 )->addQueryParam( 'foo', "bar" );
        $body = $bot
            ->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );

        $this->assertNotEmpty( $body );
    }

    public function testRunWebBotWithFormParamsShouldGetValidResponse() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';
        $stepOne           = new Step();
        $stepOne->setMethod( 'POST' )->setUrl( 'github.com' )->setTimeout( 10 )->addFormParam( 'foo', "bar" );
        $body = $bot
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

        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $failureRuleOne = new FailureRule();
        $failureRuleOne->setType( FailureRule::regex )->setParameters( "DPRMC" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addFailureRule( 'testFailureRule', $failureRuleOne )
            ->setBreakOnFailure( true );

        $bot->addStep( $stepOneNameForBot, $stepOne )->run();

        $failure = $bot->getStepResult( $stepOneNameForBot );

        $this->assertInstanceOf( Failure::class, $failure );
    }


    public function testRunWebBotWithFailureRuleThatDoesNotGetTriggeredShouldReturnNonEmptyResponseBody() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $failureRuleOne = new FailureRule();
        $failureRuleOne->setType( FailureRule::regex )->setParameters( "youWillNeverSeeThisTextOnThisPage4397ayir7ya" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )->setUrl( 'https://github.com/dprmc/' )->addFailureRule( 'testFailureRule', $failureRuleOne );

        $body = $bot->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );

        $this->assertNotEmpty( $body );
    }

    public function testAddingInvalidFailureRuleTypeShouldThrowException() {
        $this->expectException( UndefinedFailureRuleType::class );

        $failureRuleOne = new FailureRule();
        $failureRuleOne->setType( 'notValidFailureRuleType' );
    }

    public function testRunningFailureRuleWithInvalidFailureRuleTypeShouldThrowException() {
        $this->expectException( UndefinedFailureRuleType::class );

        $failureRuleOne = new FailureRule();
        $failureRuleOne->run( new Response(), false );
    }

    public function testRunningSuccessRuleShouldYieldInSuccessStepResult() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $successRule = new SuccessRule();
        $successRule->setType( SuccessRule::regex )->setParameters( "DPRMC" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addSuccessRule( 'testSuccessRule', $successRule )
            ->setBreakOnSuccess( true );

        $bot->addStep( $stepOneNameForBot, $stepOne )->run();

        $success = $bot->getStepResult( $stepOneNameForBot );

        $this->assertInstanceOf( Success::class, $success );
    }

    public function testRunningSuccessRuleShouldYieldContinueToNextStepInStepResult() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $successRule = new SuccessRule();
        $successRule->setType( SuccessRule::regex )->setParameters( "YouWillNeverFindThisStringwo475owai7erasloery;a" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )
            ->setUrl( 'https://github.com/dprmc/' )
            ->addSuccessRule( 'testSuccessRule', $successRule )
            ->setBreakOnSuccess( true );

        $bot->addStep( $stepOneNameForBot, $stepOne )->run();

        $continue = $bot->getStepResult( $stepOneNameForBot );

        $this->assertInstanceOf( ContinueToNextStep::class, $continue );
    }

    public function testRunningSuccessRuleWithInvalidSuccessRuleTypeShouldThrowException() {
        $this->expectException( UndefinedSuccessRuleType::class );
        $successRule = new SuccessRule();
        $successRule->setType( 'notValidType' );
        $successRule->run( new Response(), false );
    }

    public function testRunningSuccessRuleWithoutSuccessRuleTypeShouldThrowException() {
        $this->expectException( UndefinedSuccessRuleType::class );
        $successRule = new SuccessRule();
        $successRule->run( new Response(), false );
    }

}