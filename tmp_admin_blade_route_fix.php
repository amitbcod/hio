<?php
$root = __DIR__;
$adminViewsDir = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminViewsDir));
$changes = 0;
foreach ($iterator as $file) {
    if (!$file->isFile() || substr($file->getFilename(), -10) !== '.blade.php') {
        continue;
    }
    $path = $file->getPathname();
    $text = file_get_contents($path);
    $new = $text;

    $patterns = [
        "@operator\\.(accommodation|activity)\." => '@admin.$1.',
        "route(\\s*\(\\s*'operator\\.(accommodation|activity)\\." => "route($1'admin.$2.",
    ];
    // Use regex replacements for quotes and double quotes
    $new = preg_replace(
        [
            '/route\(\s*\'operator\.(accommodation|activity)\./',
            '/route\(\s*\"operator\.(accommodation|activity)\./',
            '/@include\(\s*\'operator\.(accommodation|activity)\./',
            '/@include\(\s*\"operator\.(accommodation|activity)\./',
            '/@extends\(\s*\'layouts\.app\'\)/',
            '/@extends\(\s*\"layouts\.app\"\)/',
        ],
        [
            'route(\'admin.$1.',
            'route(\"admin.$1.',
            '@include(\'admin.$1.',
            '@include(\"admin.$1.',
            "@extends('layouts.admin')",
            "@extends(\"layouts.admin\")",
        ],
        $new
    );

    if ($new !== $text) {
        file_put_contents($path, $new);
        $changes++;
        echo "Updated $path\n";
    }
}
echo "Total updated files: $changes\n";
