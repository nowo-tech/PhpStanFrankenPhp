<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/demo',
        __DIR__ . '/vendor',
        __DIR__ . '/tests/Fixtures',
        // PHPStan only feeds matching node types, but unit tests call processNode with
        // wrong node kinds; instanceof guards must stay for early-return coverage.
        ReduceAlwaysFalseIfOrRector::class,
    ])
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    );
