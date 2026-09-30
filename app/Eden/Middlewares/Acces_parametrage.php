<?php

namespace App\Eden\Middlewares;
class Acces_parametrage extends Eden_utilisateur_connecte{

    public function __construct(){

        $this->type = 2;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, \Closure $next){

        if(editeur() || !session()->has('utilisateur_eden'))
            return parent::handle($request,$next);

        if(moi()->type_utilisateur != 1 && (!session()->has('eden_usurpation_origine') || \Route::getCurrentRoute()->action['as'] != 'parametrage.usurpation.retour') )
            abort(403);

        $parametres_route = request()->route()->parameters();

        if(isset($parametres_route['type_element'])){

            $table_libre = table_libre($parametres_route['type_element']);

            if($table_libre->table_systeme)
                abort(404);

            if(empty($table_libre->editable_client))
                abort(403);
        }

        return parent::handle($request,$next);
    }
}