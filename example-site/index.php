<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Uncomment to test subsite deployment:
// set_root_url('/myapp');

file_router(function() {
    route(method(GET), url_path('/'), function() {
        echo '<h1>Home</h1>';
        echo '<ul>';
        echo '<li><a href="' . root_url('/api/users') . '">GET /api/users</a></li>';
        echo '<li><a href="' . root_url('/dashboard') . '">Dashboard</a></li>';
        echo '<li><a href="' . root_url('/about') . '">About</a></li>';
        echo '</ul>';
        echo '<p><small>Root URL: <code>' . htmlspecialchars(root_url()) . '</code></small></p>';
    });
});
