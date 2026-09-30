<?php

namespace App\Eden\Middlewares;

use Illuminate\Foundation\Http\Middleware\TrimStrings as TrimStrings;

class Espaces_comme_valeur extends TrimStrings
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'decimal_separateur_milliers'
    ];
}