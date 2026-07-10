<?php

// Redirect project root requests into the Laravel public folder.
// This is a compatibility fallback for local WAMP setups where the document root is not set to public/.

$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Avoid redirect loops for the public directory itself.
if (strpos($uri, '/public/') === 0) {
    require __DIR__ . '/public/index.php';
    return;
}

$target = '/public' . $uri;

// Preserve query string if present.
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $target, true, 302);
exit;
