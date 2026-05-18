<?php
function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern)."/*", GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}
$files = rglob('resources/views/admin/*.blade.php');
foreach ($files as $file) {
    $text = file_get_contents($file);
    $new = str_replace("@extends('layouts.app')", "@extends('layouts.admin')", $text);
    $new = str_replace("@extends(\"layouts.app\"", "@extends('layouts.admin')", $new);
    $new = str_replace("route('operator.'", "route('admin.'", $new);
    $new = str_replace("route(\"operator.\"", "route(\"admin.\"", $new);
    $new = str_replace("include('operator.'", "include('admin.'", $new);
    $new = str_replace("include(\"operator.\"", "include(\"admin.\"", $new);
    if ($new !== $text) {
        file_put_contents($file, $new);
        echo "Updated: $file\n";
    }
}
