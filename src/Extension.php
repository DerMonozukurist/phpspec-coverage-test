<?php

declare(strict_types=1);

namespace Mahalay\PhpSpec\CoverageTest;

use Mahalay\PhpSpec\CoverageTest\Listener\CodeCoverageRatioListener;
use Mahalay\PhpSpec\CoverageTest\Listener\NullListener;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params)
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
                    $params = (!empty($params)) ? $params : ($container->getParam('code_coverage_test') ?? []);

                    return $params + ['min_coverage' => 0.0];
                }
            );

        $container
            ->define(
                'event_dispatcher.listeners.code_coverage_test',
                static function (ServiceContainer $container) {
                    $input = $container->get('console.input');
                    if ($input->hasOption('no-coverage') && $input->getOption('no-coverage')) {
                        return new NullListener();
                    }
                    $options = $container->get('code_coverage_test.options');

                    return new CodeCoverageRatioListener($container->get('code_coverage'), $options['min_coverage']);
                },
                ['event_dispatcher.listeners']
            );
    }
}
