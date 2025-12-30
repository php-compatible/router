<?php
require_once __DIR__ . '/../../vendor/autoload.php';

file_router(function() {
    route(method(GET), url_path('/'), function() {
        echo '<h1>Dashboard</h1>';
        echo '<p>Welcome to the dashboard (served from dashboard/index.php)</p>';
        echo '<a href="/">Back to Home</a>';
    });
});
