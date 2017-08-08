<?php

namespace DPRMC\WebBot\Tests;

use DPRMC\WebBot\Exceptions\Step\InvalidMethodForStep;
use DPRMC\WebBot\Step;
use PHPUnit\Framework\TestCase;

class StepTest extends TestCase {

    public function testSetMethodShouldThrowExceptionIfPassedAnInvalidMethod() {
        $this->expectException( InvalidMethodForStep::class );
        $step = new Step();
        $step->setMethod( 'invalidMethod' );
    }
}