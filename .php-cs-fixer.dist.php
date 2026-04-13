<?php declare(strict_types=1);

/**
 * PHPStan Rules
 *
 * @package   PHPStan Rules
 * @author    IWF Web Solutions <web-solutions@iwf.ch>
 * @copyright Copyright (c) 2025-2026 IWF Web Solutions <web-solutions@iwf.ch>
 * @license   https://github.com/iwf-web/phpstan-rules/blob/main/LICENSE.txt MIT License
 * @link      https://github.com/iwf-web/phpstan-rules
 */

require_once __DIR__.'/vendor/autoload.php';

use IWFWeb\CodingStandard\IWFWebStandardRiskySet;
use IWFWeb\CodingStandard\IWFWebStandardSet;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$year = date('Y');
$header = <<<EOF
    PHPStan Rules

    @package   PHPStan Rules
    @author    IWF Web Solutions <web-solutions@iwf.ch>
    @copyright Copyright (c) 2025-{$year} IWF Web Solutions <web-solutions@iwf.ch>
    @license   https://github.com/iwf-web/phpstan-rules/blob/main/LICENSE.txt MIT License
    @link      https://github.com/iwf-web/phpstan-rules
    EOF;

// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
return (new Config())
    ->registerCustomRuleSets([
        new IWFWebStandardSet(),
        new IWFWebStandardRiskySet(),
    ])
    ->setFinder(Finder::create()
        ->in(__DIR__)
        ->ignoreDotFiles(false)
        ->ignoreVCSIgnored(true)
        ->notPath('tests/data'),
    )
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@IWFWeb/standard' => true,
        '@IWFWeb/standard:risky' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
        ],
    ])
;
