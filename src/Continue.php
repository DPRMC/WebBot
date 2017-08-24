<?php

namespace DPRMC\WebBot;

/**
 * Class ContinueToNextStep
 * @package DPRMC\WebBot
 *          When you run a Step and it does not trigger any of the SuccessRules or FailureRules, then nothing
 *          noteworthy must have happened. Return an instance of this class to indicate to the WebBot that it should
 *          continue with the next Step.
 */
class ContinueToNextStep extends StepResult {

}