<?php

namespace DPRMC\WebBot\Tests;

use DPRMC\WebBot\Step;
use DPRMC\WebBot\WebBot;
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

    public function testRunWebBotWithOneStepShould() {
        $bot               = new WebBot();
        $stepOneNameForBot = 'test';
        $stepOne           = new Step();
        $stepOne->setMethod( 'GET' )->setUrl( 'github.com' )->setTimeout( 10 )->addFormParam( 'foo', "bar" );
        $body = $bot
            ->addStep( $stepOneNameForBot, $stepOne )
            ->run()
            ->getResponseBody( $stepOneNameForBot );

        $this->assertNotEmpty( $body );
    }


}