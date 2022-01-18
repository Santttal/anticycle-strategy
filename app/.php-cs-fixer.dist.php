<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@PHP80Migration' => true,
        '@PSR12:risky' => true,
        '@PSR12' => true,
        '@Symfony:risky' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'class_definition' => ['multi_line_extends_each_single_line' => true],
        'concat_space' => ['spacing' => 'one'],
        'echo_tag_syntax' => ['format' => 'long'],
        'linebreak_after_opening_tag' => true,
        'list_syntax' => ['syntax' => 'short'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'native_constant_invocation' => false,
        'native_function_invocation' => false,
        'no_useless_else' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
        'phpdoc_summary' => false,
        'psr_autoloading' => true,
        'declare_strict_types' => false,
        'single_line_throw' => false,
    ])
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setFinder($finder)
    ;
