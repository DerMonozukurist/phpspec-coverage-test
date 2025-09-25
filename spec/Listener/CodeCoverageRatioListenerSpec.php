<?php

namespace spec\DerMonozukurist\PhpSpec\CoverageTest\Listener;

use DerMonozukurist\PhpSpec\CoverageTest\Exception\LowCoverageRatioException;
use DerMonozukurist\PhpSpec\CoverageTest\Listener\CodeCoverageRatioListener;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\ObjectBehavior;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use spec\DerMonozukurist\PhpSpec\CoverageTest\CoverageExample;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @mixin CodeCoverageRatioListener
 */
class CodeCoverageRatioListenerSpec extends ObjectBehavior
{
    /**
     * @var CodeCoverage
     */
    private $coverage;

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_should_subscribe_to_after_suite_event_with_least_priority()
    {
        $this->getSubscribedEvents()->shouldBe([
            'afterSuite' => ['afterSuite', -1000],
        ]);
    }

    public function it_should_throw_an_error_if_the_minimum_coverage_is_not_met(SuiteEvent $event)
    {
        $driver = $this->createDriverStub([
            13 => Driver::LINE_EXECUTED,
            14 => Driver::LINE_EXECUTED,
            19 => Driver::LINE_NOT_EXECUTED,
            20 => Driver::LINE_NOT_EXECUTED,
        ]);
        $this->beConstructedWith($coverage = new CodeCoverage($driver, new Filter()), 75.0);
        $coverage->start('acme-foobar');
        $coverage->stop();

        $this->shouldThrow(LowCoverageRatioException::class)->during('afterSuite', [$event]);
    }

    public function it_should_throw_an_error_if_the_minimum_coverage_is_not_met_disregarding_lines_without_executables(
        SuiteEvent $event
    ) {
        $driver = $this->createDriverStub([
            7 => Driver::LINE_NOT_EXECUTABLE,
            9 => Driver::LINE_NOT_EXECUTABLE,
            13 => Driver::LINE_EXECUTED,
            14 => Driver::LINE_EXECUTED,
            19 => Driver::LINE_NOT_EXECUTED,
            20 => Driver::LINE_NOT_EXECUTED,
        ]);
        $this->beConstructedWith($coverage = new CodeCoverage($driver, new Filter()), 75.0);
        $coverage->start('acme-foobar');
        $coverage->stop();

        $this->shouldThrow(LowCoverageRatioException::class)->during('afterSuite', [$event]);
    }

    public function it_should_not_throw_an_error_if_minimum_coverage_satisfied_disregarding_lines_without_executables(
        SuiteEvent $event
    ) {
        $driver = $this->createDriverStub([
            7 => Driver::LINE_NOT_EXECUTABLE,
            9 => Driver::LINE_NOT_EXECUTABLE,
            13 => Driver::LINE_EXECUTED,
            14 => Driver::LINE_EXECUTED,
            19 => Driver::LINE_NOT_EXECUTED,
            20 => Driver::LINE_NOT_EXECUTED,
        ]);
        $coverage = new CodeCoverage($driver, new Filter());
        $this->beConstructedWith($coverage, 50.0);

        $coverage->start('acme-foobar');
        $coverage->stop();

        $this->shouldNotThrow(LowCoverageRatioException::class)->during('afterSuite', [$event]);
    }

    public function it_should_not_throw_an_error_during_after_suite_event(SuiteEvent $event)
    {
        $this->coverage->start('acme-foobar');
        $this->coverage->stop();

        $this->afterSuite($event);
    }

    public function let()
    {
        $driver = $this->createDriverStub([
            13 => 1,
            14 => 1,
            19 => 1,
            20 => 1,
        ]);
        $this->coverage = new CodeCoverage($driver, new Filter());
        $this->beConstructedWith($this->coverage, 100.0);
    }

    private function createDriverStub(array $xdebugCoverageData): Driver
    {
        return new class($xdebugCoverageData) extends Driver {
            public function __construct(private array $xdebugCoverageData)
            {
            }

            public function nameAndVersion(): string
            {
                return 'DriverStub';
            }

            public function start(): void
            {
            }

            public function stop(): RawCodeCoverageData
            {
                require_once __DIR__ . '/../CoverageExample.php';
                $file = realpath(__DIR__ . '/../CoverageExample.php');

                $rawCoverage[$file] = $this->xdebugCoverageData;

//                $rawCoverage[$file][13] = self::LINE_EXECUTED;
//                $rawCoverage[$file][14] = self::LINE_EXECUTED;
//
//                $rawCoverage[$file][19] = self::LINE_NOT_EXECUTED;
//                $rawCoverage[$file][20] = self::LINE_NOT_EXECUTED;
//
//                $rawCoverage[$file][7] = self::LINE_NOT_EXECUTABLE;
//                $rawCoverage[$file][9] = self::LINE_NOT_EXECUTABLE;

                return RawCodeCoverageData::fromXdebugWithoutPathCoverage($rawCoverage);
            }
        };
    }

    /**
     * @param string $file
     * @param int $coveredCount
     * @param int $uncoveredCount
     *
     * @return array<string, array>
     */
    private function createRawCoverageArray($file, $coveredCount, $uncoveredCount): array
    {
        return [
            $file => array_fill(10, $coveredCount, [\hash('crc32', random_bytes(8))])
                + array_fill(10 + $coveredCount, $uncoveredCount, []),
        ];
    }
}
