<?php
require_once __DIR__ . '/../../vendor/autoload.php';

file_router(function() {
    route(method(GET), url_path('/'), function() {
        echo json_response(HTTP_OK, array(
            'users' => array(
                array('id' => 1, 'name' => 'Alice'),
                array('id' => 2, 'name' => 'Bob'),
            )
        ));
    });

    route(method(POST), url_path('/'), function() {
        $data = json_body();
        echo json_response(HTTP_CREATED, array(
            'message' => 'User created',
            'user' => $data
        ));
    });
});
