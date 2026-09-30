<?php

namespace App\Eden\Exceptions;

use Throwable;
use Exception;
use App\Exceptions\Handler as ExceptionHandler;

class Handler_8 extends ExceptionHandler
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request,Throwable |  Exception $exception)
    {
        $code_erreur = 500;

        if($this->isHttpException($exception))
            $code_erreur = $exception->getStatusCode();

        $titre_erreur = '';

        if(in_array($code_erreur, [404, 401, 403, 419, 429, 503, 500]))
            $titre_erreur = traduction('interface.erreur_http.titre_erreur_' . $code_erreur);

        if(config('app.debug'))
            return parent::render($request, $exception);

        return response()->view('eden::erreur_http', ['erreur' => $code_erreur, 'titre_erreur' => $titre_erreur], $code_erreur);
    }
}
