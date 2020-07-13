<?php

use Vinelab\NeoEloquent\Eloquent\NeoEloquentFactory;

if (!function_exists('neo_factory')) {
    function neo_factory()
    {
        $factory = app(NeoEloquentFactory::class);

        $arguments = func_get_args();

        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
        } elseif (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        }

        return $factory->of($arguments[0]);
    }
}
