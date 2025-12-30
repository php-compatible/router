<?php

class SingleActionController
{
    public function __invoke()
    {
        echo json_encode(array("message" => "Hello World"));
    }
}
