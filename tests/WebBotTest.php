<?php

namespace DPRMC\WebBot\Tests;

use DPRMC\WebBot\Exceptions\FailureRule\Triggered;
use DPRMC\WebBot\Exceptions\FailureRule\UndefinedFailureRuleType;
use DPRMC\WebBot\FailureRule;
use DPRMC\WebBot\Step;
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
    public function testRunWebBotWithFailureRuleShouldThrowException() {
        $this->expectException( Triggered::class );
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $failureRuleOne = new FailureRule();
        $failureRuleOne->setType( FailureRule::regex )->setParameters( "DPRMC" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )->setUrl( 'https://github.com/dprmc/' )->addFailureRule( 'testFailureRule', $failureRuleOne );

        $bot->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );
    }

    /**
     * Same test as above, but I catch the Triggered exception and test the getResponse() method of the Exception.
     */
    public function testRunWebBotWithFailureRuleShouldThrowExceptionAndResponseBodyNotEmpty() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';

        $failureRuleOne = new FailureRule();
        $failureRuleOne->setType( FailureRule::regex )->setParameters( "DPRMC" );

        $stepOne = new Step();
        $stepOne->setMethod( 'GET' )->setUrl( 'https://github.com/dprmc/' )->addFailureRule( 'testFailureRule', $failureRuleOne );

        try {
            $bot->addStep( $stepOneNameForBot, $stepOne )
                ->run()
                ->getResponseBody( $stepOneNameForBot );
        } catch ( Triggered $e ) {
            $responseBody = $e->getResponse();
            $this->assertNotEmpty( $responseBody );
        }
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
        $failureRuleOne->run( new Response() );
    }


}