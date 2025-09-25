<?php

namespace spec\DerMonozukurist\PhpSpec\CoverageTest;

class CoverageExample
{
    // Did not have executable code (comment)

    private $property = 'value'; // Did not have executable code (property declaration)

    public function executedMethod() // Executable code (method declaration counts as executable)
    {
        $var = 1; // Executed
        return $var; // Executed
    } // Line 13: Did not have executable code (closing brace)

    public function notExecutedMethod() // Executable code (but won't be executed)
    {
        $unused = 2; // Not executed
        return $unused; // Not executed
    } // Did not have executable code (closing brace)

    // Did not have executable code (comment)
} // Did not have executable code (closing brace)