<?php

namespace app\Eden\Middlewares;

use Closure;

class Frame
{
    /**
     * Handle the given request and get the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next)
    {

        $dest_requete = $request->headers->get('sec-fetch-dest');
        $mode_requete = $request->headers->get('sec-fetch-mode');

        if ($dest_requete === 'document' && $mode_requete === 'navigate') {
            abort(403, 'Accès direct interdit');
        }

        $response = $next($request);

        $id_form = $request->id_form;

        $formulaire_web_domaine = modele('formulaire_web_domaine')
            ->join('eden_formulaireslibres','eden_formulaireslibres.id','formulaire_id')
            ->select('domaine')
            ->where('formulaire_web_id',$id_form)
            ->get()->pluck('domaine')->toArray();
        
        $response->headers->set('Content-Security-Policy', 'frame-ancestors '.implode(' ',$formulaire_web_domaine));

        return $response;
    }
}