# PhpSpec Code Coverage Test
A [PhpSpec](http://www.phpspec.net/en/stable) extension for testing code coverage without the need for coverage report being generated first.

# Usage
## Config
Create a `phpspec.yaml` or `phpspec.yaml.dist` file containing the following.
```yaml
# phpspec.yaml.dist
formatter.name: pretty
suites:
  default_suite:
    namespace: DerMonozukurist\PhpSpec\CoverageTest
    psr4_prefix: DerMonozukurist\PhpSpec\CoverageTest

extensions:
  FriendsOfPhpSpec\PhpSpec\CodeCoverage\CodeCoverageExtension:
    format:
      - html
    output:
      html: coverage

  DerMonozukurist\PhpSpec\CoverageTest\Extension:
    min_coverage: 100.0
```
Adjust these settings accordingly. Then `phpspec` on!
```shell
$ vendor/bin/phpspec run
```

If you don't have `xdebug` installed use `phpdbg`
```shell
$ phpdbg -qrr vendor/bin/phpspec run
```
