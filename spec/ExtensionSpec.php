<?php

namespace spec\Mahalay\PhpSpec\CoverageTest;

use Mahalay\PhpSpec\CoverageTest\Extension;
use Mahalay\PhpSpec\CoverageTest\Listener\CodeCoverageRatioListener;
use Mahalay\PhpSpec\CoverageTest\Listener\NullListener;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ObjectBehavior;
use PhpSpec\ServiceContainer;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @mixin Extension
 */
class ExtensionSpec extends ObjectBehavior
{
    function let(ServiceContainer $container, InputInterface $input)
    {
        $container->getParam('code_coverage_test')->willReturn(null);
        $container->has('code_coverage')->willReturn(true);
        $container->get('console.input')->willReturn($input);
        $container->define('code_coverage_test.options', Argument::type('callable'));
        $container->define(
            'event_dispatcher.listeners.code_coverage_test',
            Argument::that(function () {return true;}),
            ['event_dispatcher.listeners']
        );
    }

    function it_should_be_a_PhpSpec_extension()
    {
        $this->shouldHaveType(BaseExtension::class);
    }

    function it_should_throw_an_error_if_PhpSpec_CodeCoverage_extension_is_not_loaded(ServiceContainer $container)
    {
        $container->has('code_coverage')->shouldBeCalledOnce()->willReturn(false);
        $exception = new \RuntimeException(
            'Extension from friends-of-phpspec/phpspec-code-coverage is missing or inactive'
        );
        $this->shouldThrow($exception)->during('load', [$container, []]);
    }

    function it_should_define_its_default_options(ServiceContainer $container)
    {
        $container->define('code_coverage_test.options', Argument::that(function (callable $callable) use($container) {
            return $callable($container->getWrappedObject()) === ['min_coverage' => 0.0];
        }));

        $this->load($container, []);
    }

    function it_should_accept_options_from_parameters(ServiceContainer $container)
    {
        $parameters = ['min_coverage' => 97.12];

        $container->define('code_coverage_test.options', Argument::that(
            function (callable $callable) use($parameters, $container) {
                return $callable($container->getWrappedObject()) === $parameters;
            })
        );

        $this->load($container, $parameters);
    }

    function it_should_accept_options_from_global_parameter_config(ServiceContainer $container)
    {
        $globalParams = ['min_coverage' => 97.12];
        $container->getParam('code_coverage_test')->shouldBeCalledOnce()->willReturn($globalParams);

        $container->define('code_coverage_test.options', Argument::that(
            function (callable $callable) use($globalParams, $container) {
                return $callable($container->getWrappedObject()) === $globalParams;
            })
        );

        $this->load($container, []);
    }

    function it_should_transform_min_coverage_ratio_to_100_percent(ServiceContainer $container)
    {
        $expectedParams = ['min_coverage' => 100.0];

        $container->define('code_coverage_test.options', Argument::that(
            function (callable $callable) use($expectedParams, $container) {
                return $callable($container->getWrappedObject()) === $expectedParams;
            })
        );

        $this->load($container, ['min_coverage' => 192.81]);
    }

    function it_should_register_a_NullListener_if_no_coverage_option_is_used(
        ServiceContainer $container,
        InputInterface $input
    ) {
        $input->hasOption('no-coverage')->willReturn(true);
        $input->getOption('no-coverage')->willReturn(true);

        $container
            ->define(
                'event_dispatcher.listeners.code_coverage_test',
                Argument::that(function (callable $callable) use($container) {
                    return $callable($container->getWrappedObject()) == (new NullListener());
                }),
                ['event_dispatcher.listeners']
            )
            ->shouldBeCalledOnce()
        ;

        $this->load($container, []);
    }

    function it_should_register_CodeCoverageRatioListener_if_no_coverage_option_is_used(
        ServiceContainer $container,
        InputInterface $input,
        Driver $driver
    ) {
        $input->hasOption('no-coverage')->willReturn(false);
        $container
            ->get('code_coverage_test.options')
            ->shouldBeCalledOnce()
            ->willReturn(['min_coverage' => 4.20])
        ;
        $container
            ->get('code_coverage')
            ->shouldBeCalledOnce()
            ->willReturn(new CodeCoverage($driver->getWrappedObject(), new Filter()))
        ;

        $container
            ->define(
                'event_dispatcher.listeners.code_coverage_test',
                Argument::that(function (callable $callable) use($container) {
                    return $callable($container->getWrappedObject()) instanceof CodeCoverageRatioListener;
                }),
                ['event_dispatcher.listeners']
            )
            ->shouldBeCalledOnce()
        ;

        $this->load($container, []);
    }
}
