<?php

declare(strict_types=1);

namespace DerMonozukurist\PhpSpec\CoverageTest;

use DerMonozukurist\PhpSpec\CoverageTest\Listener\CodeCoverageRatioListener;
use DerMonozukurist\PhpSpec\CoverageTest\Listener\NullListener;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Console\Input\InputInterface;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params): void
    {
        if (!$container->has('code_coverage')) {
            throw new \RuntimeException(
                'Extension from friends-of-phpspec/phpspec-code-coverage is missing or inactive'
            );
        }

        $container
            ->define(
                'code_coverage_test.options',
                static function (ServiceContainer $container) use ($params) {
                    /** @var array $params */
                    $params = (!empty($params)) ? $params : ($container->getParam('code_coverage_test') ?? []);

                    $ratio = (($params['min_coverage'] ?? 0.0) > 100.0) ? 100.0 : 0.0;

                    return $params + ['min_coverage' => $ratio];
                }
            );

        $container
            ->define(
                'event_dispatcher.listeners.code_coverage_test',
                static function (ServiceContainer $container) use ($params) {
                    /** @var InputInterface $input */
                    $input = $container->get('console.input');
                    if ($input->hasOption('no-coverage') && $input->getOption('no-coverage')) {
                        return new NullListener();
                    }

                    /** @var array $params */
                    $params = (!empty($params)) ? $params : ($container->getParam('code_coverage_test') ?? []);
                    $ratio = (($params['min_coverage'] ?? 0.0) > 100.0) ? 100.0 : 0.0;
                    $options = $params + ['min_coverage' => $ratio];

                    /** @var CodeCoverage $coverage */
                    $coverage = $container->get('code_coverage');

                    return new CodeCoverageRatioListener($coverage, (float)$options['min_coverage']);
                },
                ['event_dispatcher.listeners']
            );
    }
}
