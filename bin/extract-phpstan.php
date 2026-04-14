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

/**
 * Extracts PHPStan PHP source files from phpstan.phar into vendor/phpstan/phpstan/src/
 * so that any IDE can index them without manual configuration.
 *
 * Run automatically via composer post-install-cmd / post-update-cmd.
 */
$pharPath = __DIR__.'/../vendor/phpstan/phpstan/phpstan.phar';
$outputDir = __DIR__.'/../vendor/phpstan/phpstan/src';

if (!file_exists($pharPath)) {
    echo "phpstan.phar not found — skipping stub generation.\n";

    exit(0);
}

// Clean existing stubs
if (is_dir($outputDir)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($it as $entry) {
        $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
    }
}

$phar = new Phar($pharPath);
$prefix = 'phar://'.realpath($pharPath).'/src/';
$count = 0;

foreach (new RecursiveIteratorIterator($phar) as $file) {
    $pathname = $file->getPathname();

    if (!str_starts_with($pathname, $prefix) || !str_ends_with($pathname, '.php')) {
        continue;
    }

    $relative = substr($pathname, strlen($prefix));
    $dest = $outputDir.'/'.$relative;

    if (!is_dir(dirname($dest))) {
        mkdir(dirname($dest), 0o755, true);
    }

    file_put_contents($dest, file_get_contents($pathname));
    ++$count;
}

echo "Extracted {$count} PHPStan files into vendor/phpstan/phpstan/src/\n";
