<?php

namespace spec\Mahalay\PhpSpec\CoverageTest\Exception;

use Mahalay\PhpSpec\CoverageTest\Exception;
use Mahalay\PhpSpec\CoverageTest\Exception\LowCoverageRatioException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin LowCoverageRatioException
 */
class LowCoverageRatioExceptionSpec extends ObjectBehavior
{
    function it_should_be_an_Exception()
    {
        $this->shouldHaveType(Exception::class);
    }
}
