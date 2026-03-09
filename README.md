# IWF PHP Coding Standard

Custom PHP-CS-Fixer rule sets for consistent code style across IWF projects.

[![License](https://img.shields.io/github/license/iwf-web/phpstan-rules)][license]
[![Version](https://img.shields.io/packagist/v/iwf-web/phpstan-rules?label=latest%20release)][packagist]
[![Version (including pre-releases)](https://img.shields.io/packagist/v/iwf-web/phpstan-rules?include_prereleases&label=latest%20pre-release)][packagist]
[![Downloads on Packagist](https://img.shields.io/packagist/dt/iwf-web/phpstan-rules)][packagist]

## Rule Sets

This package provides two rule sets:

| Rule Set              | Description                                                  |
| --------------------- | ------------------------------------------------------------ |
| `@IWF/standard`       | Non-risky coding style rules for consistent formatting       |
| `@IWF/standard:risky` | Risky rules that may change code behavior (use with caution) |

Both rule sets build upon the excellent `@PhpCsFixer` rule set (which includes `@Symfony` and `@PSR12`) with customizations tailored for IWF projects.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) ^3.0

### Installation

```bash
composer require --dev iwf-web/phpstan-rules
```

### Usage

Create a `.php-cs-fixer.dist.php` file in your project root:

```php
<?php declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use IWF\CodingStandard\IWFRiskySet;
use IWF\CodingStandard\IWFSet;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return new Config()
    ->registerCustomRuleSets([
        new IWFSet(),
        new IWFRiskySet(),
    ])
    ->setFinder(Finder::create()
        ->in(__DIR__)
    )
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@IWF/standard' => true,
        '@IWF/standard:risky' => true,
    ])
;
```

Run the fixer:

```bash
# Check for violations (dry run)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix violations
vendor/bin/php-cs-fixer fix
```

## Rule Customizations

### @IWF/standard

Key customizations over the base `@PhpCsFixer` rule set:

- **No Yoda style** - Uses natural comparison order (`$value === null` instead of `null === $value`)
- **Strict types at top** - No blank line after opening tag to keep `declare(strict_types=1);` at the very top
- **Simplified class ordering** - Only requires traits to be placed first in classes
- **Preserved DocBlocks** - Single-line DocBlocks are preserved; `@inheritDoc` is not removed
- **Trailing commas everywhere** - In arrays, arguments, parameters, and match expressions
- **PHPUnit flexibility** - Does not require `@covers` annotations on test classes

### @IWF/standard:risky

Key customizations over the base `@PhpCsFixer:risky` rule set:

- **PHPUnit assertions** - Uses `self::` for test case static method calls
- **No forced strict types** - Relies on PHPStan for type safety instead of enforcing `declare(strict_types=1);`
- **Flexible data providers** - Does not enforce naming conventions for PHPUnit data providers
- **Ignored comment tags** - Preserves `php-cs-fixer-ignore` and `todo` comments

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
