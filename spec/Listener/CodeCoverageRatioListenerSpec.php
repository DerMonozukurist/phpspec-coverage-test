<?php

namespace spec\Mahalay\PhpSpec\CoverageTest\Listener;

use Mahalay\PhpSpec\CoverageTest\Exception\LowCoverageRatioException;
use Mahalay\PhpSpec\CoverageTest\Listener\CodeCoverageRatioListener;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
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

    public function it_should_throw_an_error_if_the_minimum_coverage_is_not_met(SuiteEvent $event, Driver $driver)
    {
        $rawCoverageArray = $this->createRawCoverageArray('foobar.php', 10, 0)
            + $this->createRawCoverageArray('acme.php', 10, 10);
        $coverage = new CodeCoverage($driver->getWrappedObject(), new Filter());
        $coverage->setData($rawCoverageArray);

        $this->beConstructedWith($coverage, 66.68);

        $this->shouldThrow(LowCoverageRatioException::class)->during('afterSuite', [$event]);
    }

    public function let(Driver $driver)
    {
        $this->coverage = new CodeCoverage($driver->getWrappedObject(), new Filter());
        $this->coverage->setData($this->createRawCoverageArray('foobar.php', 10, 0));
        $this->beConstructedWith($this->coverage, 100);
    }

    /**
     * @param array<string, array> $rawCoverageArray
     */
    private function createDriverStub($rawCoverageArray): Driver
    {
        return new class($rawCoverageArray) implements Driver {
            /**
             * @var array<string, array>
             */
            private $rawCoverageArray;

            public function __construct(array $rawCoverageArray)
            {
                $this->rawCoverageArray = $rawCoverageArray;
            }

            public function nameAndVersion(): string
            {
                return 'DriverStub';
            }

            public function start(bool $determineUnusedAndDead = true): void
            {
            }

            public function stop(): array
            {
                return [];
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
