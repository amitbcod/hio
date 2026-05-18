<?php
$root = __DIR__;
$dir = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && substr($file->getFilename(), -10) === '.blade.php') {
        $files[] = $file->getPathname();
    }
}
$replacements = [
    "operator.accommodation." => "admin.accommodation.",
    "operator.activity." => "admin.activity.",
    "@include('operator.accommodation._steps_sidebar')" => "@include('admin.accommodation._steps_sidebar')",
    "@include('operator.activity._steps_sidebar')" => "@include('admin.activity._steps_sidebar')",
    "@include('operator.registration._sidebar_main')" => "@include('admin._sidebar')",
    "@extends('layouts.app')" => "@extends('layouts.admin')",
];
foreach ($files as $file) {
    $text = file_get_contents($file);
    $new = str_replace(array_keys($replacements), array_values($replacements), $text);
    if ($new !== $text) {
        file_put_contents($file, $new);
    }
}
echo "updated " . count($files) . " files\n";
