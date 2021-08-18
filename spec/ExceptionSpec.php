<?php

namespace spec\Mahalay\PhpSpec\CoverageTest;

use Exception as BaseException;
use Mahalay\PhpSpec\CoverageTest\Exception;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Exception
 */
class ExceptionSpec extends ObjectBehavior
{
    function it_should_be_an_Exception()
    {
        $this->shouldHaveType(BaseException::class);
    }
}
