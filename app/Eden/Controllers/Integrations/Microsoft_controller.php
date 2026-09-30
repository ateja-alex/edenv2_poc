<?php

namespace App\Eden\Controllers\Integrations;

use App\Http\Controllers\Controller;

class Microsoft_controller extends Controller{

    /**
     *
     * Récupère les dossiers de la boite mail d'une adresse email
     *
     */
    public function dossiers_boite_mail($adresse_email){

        $dossiers = service('microsoft_email')->dossiers($adresse_email);

        return response()->json($dossiers);
    }

    /**
     *
     * Récupère les alias de la boite mail d'une adresse email
     *
     */
    public function alias_email($utilisateur_id){

        $utilisateur = modele('utilisateur')->where('id', $utilisateur_id)->first();

        $alias = $utilisateur == null ? [] : service('microsoft_email')->alias_disponibles($utilisateur);

        return response()->json($alias);
    }

}
