<?php

namespace App\Eden\Middlewares;

use App\Eden\Models\Utilisateur;

class Editeur extends Eden_utilisateur_connecte {

    public function __construct(){

        $this->type = 3;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, \Closure $next){

        if(!session()->has('utilisateur_eden'))
            return parent::handle($request,$next);

        $utilisateur = null;

        if(session()->has('eden_usurpation_origine'))
            $utilisateur = session()->get('eden_usurpation_origine');

        if(!editeur() && !editeur($utilisateur))
            abort(404);

        return parent::handle($request, $next);
    }

    /**
     * @return true
     *
     * Pas de gestion de licence pour les routes éditeurs
     *
     */
    public function droits_licences(){
        return true;
    }

    /**
     * @return true
     *
     * Pas de gestion de licence pour les routes éditeurs
     *
     */
    public function verification_droits_licences(){
        return true;
    }
}