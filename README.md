# IWF PHPStan Rules

Custom PHPStan rules used across IWF projects to enforce coding standards, security practices, and architectural conventions.

[![License](https://img.shields.io/github/license/iwf-web/phpstan-rules)][license]
[![Version](https://img.shields.io/packagist/v/iwf-web/phpstan-rules?label=latest%20release)][packagist]
[![Version (including pre-releases)](https://img.shields.io/packagist/v/iwf-web/phpstan-rules?include_prereleases&label=latest%20pre-release)][packagist]
[![Downloads on Packagist](https://img.shields.io/packagist/dt/iwf-web/phpstan-rules)][packagist]

## Requirements

- PHP 8.3 or higher
- PHPStan ^2.1

## Installation

```bash
composer require --dev iwf-web/phpstan-rules
```

## Usage

Include the rule set in your `phpstan.neon` or `phpstan.neon.dist`:

```neon
includes:
    - vendor/iwf-web/phpstan-rules/rules.neon
```

### Configuration

Several rules require or accept configuration parameters under the `iwf` key.

#### Controller rules

```neon
parameters:
    iwf:
        controller:
            controllerNamespace: 'App\Controller'
            excludedNamespaces: []
            excludedControllers:
                - 'App\Controller\Api\Security\LoginController'
```

#### Required use aliases

Enforce that specific namespaces are always imported with a defined alias:

```neon
parameters:
    iwf:
        requiredUseAlias:
            aliasDefinitions:
                - { namespace: 'Doctrine\ORM\Mapping', alias: 'ORM' }
                - { namespace: 'Symfony\Component\Validator\Constraints', alias: 'Assert' }
```

#### Attribute requirements

Enforce that certain attributes may only appear alongside required companion attributes:

```neon
parameters:
    iwf:
        attributeRequirements:
            attributeDefinitions:
                -
                    attribute: 'Symfony\Component\Routing\Attribute\Route'
                    requires:
                        - 'OpenApi\Attributes\Tag'
                        - 'Symfony\Component\Security\Http\Attribute\IsGranted'
```

#### Force DateProvider (requires `coala/date-provider-bundle`)

```neon
parameters:
    iwf:
        forceDateProvider:
            allowedFormats:
                - 'Y-m-d'
                - 'Y-m-d H:i:s'
                - 'Y-m-d\TH:i:s'
                - 'Y-m-d\TH:i:sP'
                - 'U'
```

#### Handle-bus traits (requires `coala/messenger-bundle`)

```neon
parameters:
    iwf:
        handleBusTrait:
            handleBusTraitMappings:
                queryBus: 'Coala\MessengerBundle\Messenger\HandleQueryBusTrait'
            handleBusTraitNamespaces:
                - 'App\Controller'
```

#### Require invalid-data-test group (requires `coala/testing-bundle`)

```neon
parameters:
    iwf:
        requireInvalidDataTestGroup:
            requireInvalidDataTestGroupNamespaces:
                - 'App\Tests'
```

---

## Rules

### Common

#### `iwf.mbFunctionUsageRule` — Multibyte function usage

Flags calls to string functions that have a multibyte-safe counterpart and may produce incorrect results when the input contains multibyte characters (e.g. UTF-8).

Affected functions: `chr`, `ord`, `parse_str`, `str_pad`, `str_split`, `stripos`, `stristr`, `strlen`, `strpos`, `strrchr`, `strripos`, `strrpos`, `strstr`, `strtolower`, `strtoupper`, `substr`, `substr_count`.

```php
// ❌ flagged
$len = strlen($userInput);

// ✅ correct
$len = mb_strlen($userInput);
```

---

#### `iwf.noAnnotationAsAttribute` — No legacy Symfony annotation namespaces

Prevents using classes from the legacy `Symfony\...\Annotation\` namespace as PHP 8 attributes. Symfony has migrated all annotations to `Symfony\...\Attribute\`.

```php
// ❌ flagged
#[Symfony\Component\Routing\Annotation\Route('/foo')]

// ✅ correct
#[Symfony\Component\Routing\Attribute\Route('/foo')]
```

---

#### `iwf.requiredUseAlias` — Required import aliases

Enforces that configured namespaces are always imported under a specific alias. Applies to both regular `use` statements and group `use` statements.

```php
// ❌ flagged — missing alias
use Doctrine\ORM\Mapping;

// ✅ correct
use Doctrine\ORM\Mapping as ORM;
```

---

#### `iwf.attributeRequirements` — Attribute companion requirements

Ensures that when a trigger attribute is present on a method, all configured companion attributes are also present.

```php
// ❌ flagged — #[Route] without #[IsGranted]
#[Route('/admin/users')]
public function list(): object { ... }

// ✅ correct
#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
public function list(): object { ... }
```

---

### Controller

#### `iwf.controllerHandleReturnType` — Controller handle() return type

In controllers that use Symfony's `HandleTrait`, actions returning `$this->handle(...)` must declare their return type as `object` (or `mixed`). A more specific type causes a `TypeError` when the message bus returns an unexpected response such as an `ErrorResponse`.

```php
// ❌ flagged — too specific, will TypeError on error responses
public function __invoke(Request $request): RecordsResponse
{
    return $this->handle(new GetRecordsQuery());
}

// ✅ correct
public function __invoke(Request $request): object
{
    return $this->handle(new GetRecordsQuery());
}
```

---

#### `iwf.controllerMissingIsGranted` — Controller missing #[IsGranted]

Every public controller method carrying a `#[Route]` attribute must also carry a `#[IsGranted]` attribute — either on the method itself or on the class. Excludes abstract classes and any configured namespaces or class names.

```php
// ❌ flagged
#[Route('/api/users')]
public function index(): object { ... }

// ✅ correct
#[Route('/api/users')]
#[IsGranted('ROLE_USER')]
public function index(): object { ... }
```

---

### Coala — DateProvider

> These rules are only active when `Coala\DateProviderBundle` is present in the project.

#### `iwf.forceDateProviderNew` / `iwf.forceDateProviderFuncCall` / `iwf.forceDateProviderStaticCall`

Disallows creating `DateTime`/`DateTimeImmutable` without an absolute date string argument, calling time-sensitive functions (`time()`, `date()`, etc.), or using static factory methods that produce the current time. Enforces the use of `DateProviderInterface` instead, which enables deterministic time in tests.

```php
// ❌ flagged
$now = new DateTimeImmutable();
$ts  = time();

// ✅ correct
$now = $this->dateProvider->now();
```

---

### Coala — Messenger

> This rule is only active when `Coala\MessengerBundle` is present in the project.

#### `iwf.useHandleBusTrait`

In configured namespaces, if a class defines a setter method that corresponds to a configured handle-bus trait (e.g. `setQueryBus()`), it must use the matching trait instead of defining the setter manually.

---

### Coala — Testing

> This rule is only active when `Coala\TestingBundle` is present in the project.

#### `iwf.requireInvalidDataTestGroup`

Test methods that call `assertFailingValidation()` must carry `#[Group('invalid-data-test')]`. This allows the test suite to run invalid-data tests in isolation.

```php
// ❌ flagged
public function testInvalidEmail(): void
{
    $this->assertFailingValidation(...);
}

// ✅ correct
#[Group('invalid-data-test')]
public function testInvalidEmail(): void
{
    $this->assertFailingValidation(...);
}
```

---

## Development

### Prerequisites

- Docker with Compose, or a local PHP 8.3+ installation

### Running tests

```bash
bin/test.sh
```

Runs PHPStan and PHPUnit. If a local PHP binary is found it is used directly; otherwise all configured Docker services are run sequentially.

To target a specific PHP version:

```bash
bin/test.sh 8.3
```

### Linting

```bash
bin/lint.sh
```

Runs PHP CS Fixer and applies fixes in place. To check without modifying files (as CI does), run:

```bash
composer lint:check
```

### Running Composer commands

```bash
bin/composer.sh <args>
```

Runs Composer in the local environment or in the default Docker container when no local PHP is available. Examples:

```bash
bin/composer.sh install
bin/composer.sh require --dev some/package
```

### Debugging with Xdebug

Xdebug is included in the local Docker images and configured with `start_with_request=trigger`. To activate it, set the `XDEBUG_TRIGGER` environment variable:

```bash
XDEBUG_TRIGGER=1 bin/test.sh
```

Or configure your IDE to listen on port `9003` and set `XDEBUG_TRIGGER=1` in the Docker run environment.

---

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

This project uses [Conventional Commits](https://www.conventionalcommits.org/) for automated releases and changelog generation.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For available versions, see the [tags on this repository][gh-tags].

## Authors

All authors can be found in the [AUTHORS.md](AUTHORS.md) file.

Contributors can be found in the [CONTRIBUTORS.md](CONTRIBUTORS.md) file.

See also the full list of [contributors][gh-contributors] who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.txt](LICENSE.txt) file for details.

## Acknowledgments

A list of used libraries and code with their licenses can be found in the [ACKNOWLEDGMENTS.md](ACKNOWLEDGMENTS.md) file.

[license]: https://github.com/iwf-web/phpstan-rules/blob/main/LICENSE.txt
[packagist]: https://packagist.org/packages/iwf-web/phpstan-rules
[gh-tags]: https://github.com/iwf-web/phpstan-rules/tags
[gh-contributors]: https://github.com/iwf-web/phpstan-rules/contributors
