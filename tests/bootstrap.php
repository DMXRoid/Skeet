<?php
/**
 * PHPUnit bootstrap.
 *
 * Loads Composer's autoloader for third-party/dev dependencies. Skeet's own
 * classes are loaded via Skeet's bespoke autoloader, which individual test
 * cases bootstrap with a test configuration as the suite is built out.
 */

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer dependencies are not installed. Run `composer install`.\n");
    exit(1);
}

require $autoload;
