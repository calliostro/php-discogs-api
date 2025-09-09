<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('vendor');

$config = new PhpCsFixer\Config();
$config->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
    ])
    ->setRiskyAllowed(true);

// @phpstan-ignore-next-line Method exists but not detected by static analysis
$config->setUnsupportedPhpVersionAllowed(true);

return $config;
