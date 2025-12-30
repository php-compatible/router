<?php

/**
 * Call middleware functions in sequence
 * @param callable|string $features,... Middleware to call (variadic)
 * @return callable
 */
function middleware()
{
    $features = func_get_args();

    return function () use ($features) {
        foreach ($features as $feature) {
            // allow invoking a class (single action middleware)
            if (is_string($feature)) {
                $feature = new $feature();
            }
            call_user_func_array($feature, array());
        }
    };
}
