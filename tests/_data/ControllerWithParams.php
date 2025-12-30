<?php

/**
 * Controller that accepts ServerRequest parameter
 */
class ControllerWithRequest
{
    public function __invoke($request)
    {
        echo json_encode(array(
            "method" => $request->getMethod(),
            "path" => $request->getPath()
        ));
    }
}

/**
 * Controller with multiple parameters
 */
class ControllerWithMultipleParams
{
    public function __invoke($request, $extra = null)
    {
        echo json_encode(array(
            "request_exists" => ($request !== null),
            "extra" => $extra
        ));
    }
}

/**
 * Simple class for type-hint testing
 */
class SimpleTypeHint
{
    public $value = 'test';
}

/**
 * Controller with unknown type hint (not ServerRequest)
 */
class ControllerWithUnknownType
{
    public function __invoke(SimpleTypeHint $hint)
    {
        echo json_encode(array("hint_value" => $hint->value));
    }
}

