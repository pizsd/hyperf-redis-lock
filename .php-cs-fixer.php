<?php

$header = <<<'EOF'
This file is part of Hyperf.

@link     https://www.hyperf.io
@document https://hyperf.wiki
@contact  group@hyperf.io
@license  https://github.com/hyperf/hyperf/blob/master/LICENSE
EOF;


return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PhpCsFixer' => true,
        '@PHP80Migration' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__))
    ->setUsingCache(false);
