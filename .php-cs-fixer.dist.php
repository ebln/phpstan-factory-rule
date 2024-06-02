<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('tests')
    ->in(__DIR__);

$rules = [
    '@PER-CS'                                          => true,
    '@Symfony'                                         => true,
    '@PhpCsFixer'                                      => true,
    '@PHPUnit100Migration:risky'                       => true,
    '@PHP80Migration:risky'                            => true,
    '@PHP82Migration'                                  => true,
    'no_superfluous_phpdoc_tags'                       => true,
    'native_function_invocation'                       => false,
    'concat_space'                                     => ['spacing' => 'one'],
    'phpdoc_types_order'                               => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'alpha'],
    'single_line_comment_style'                        => ['comment_types' => [ /* 'hash' */],],
    'phpdoc_summary'                                   => false,
    'cast_spaces'                                      => ['space' => 'none'],
    'binary_operator_spaces'                           => ['default' => null, 'operators' => ['=' => 'align_single_space_minimal', '=>' => 'align_single_space_minimal_by_scope']],
    'no_unused_imports'                                => true,
    'ordered_imports'                                  => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
    'control_structure_braces'                         => true,
    'control_structure_continuation_position'          => true,
    'date_time_create_from_format_call'                => true,
    'date_time_immutable'                              => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'phpdoc_line_span'                                 => ['const' => 'single', 'method' => 'single', 'property' => 'single'],
    'simplified_null_return'                           => true,
    'statement_indentation'                            => true,
    'blank_line_before_statement'                      => ['statements' => ['continue', 'declare', 'default', 'exit', 'goto', 'include', 'include_once', 'require', 'require_once', 'return', 'switch']],
    'simplified_if_return'                             => true,
    'use_arrow_functions'                              => false,
    'fully_qualified_strict_types'                     => false,
    'phpdoc_to_comment'                                => false,
];

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

if (false) {
    $resolver = new \PhpCsFixer\Console\ConfigurationResolver($config, [], '', new \PhpCsFixer\ToolInfo());
    echo "\n\n# DUMPING EFFECTIVE RULES #################\n";
    var_export($resolver->getRules());
    echo "\n\n###########################################\n";

    die();
}

return $config;
