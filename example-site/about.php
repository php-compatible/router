<?php
require_once __DIR__ . '/../vendor/autoload.php';

file_router(function() {
    route(method(GET), url_path('/'), function() {
        echo '<h1>About Page</h1>';
        echo '<p>This is the about page served from about.php</p>';
        echo '<a href="/">Back to Home</a>';
    });
});
