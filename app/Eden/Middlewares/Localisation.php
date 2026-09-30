<?php

namespace App\Eden\Middlewares;

use Closure;
use App;

class Localisation
{

    protected $langages = ['en', 'fr'];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!session()->has('locale')) {

            session()->put('locale', $request->getPreferredLanguage($this->langages));
        }

        
        App::setLocale(session('locale'));

        return $next($request);
    }
}
