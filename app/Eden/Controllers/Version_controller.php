<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Parametre;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Version_controller extends Controller {

    /**
     *
     * Permet de récupérer la version en cours
     *
     */
    public function version_actuelle(){

        $version_actuelle = modele('version_eden')->select(DB::raw('max(numero_version) as version_actuelle, max(date_ajout) as date_derniere_maj'))->first()->toArray();

        return json_encode($version_actuelle);
    }

    /**
     *
     * Confirmer la lecture des rappels de version
     *
     */
    public function confirmation_lecture_rappel(){

        $parametre_rappels_version = Parametre::where('nom', 'rappels_version')->first();

        if($parametre_rappels_version != null)
            $parametre_rappels_version->delete();

        return json_encode(true);
    }

    /**
     *
     * Récupere les rappels de version
     *
     */
    public function recuperer_rappels_version(){

        $parametre_rappels_version = parametre('rappels_version');

        if($parametre_rappels_version !== null)
            return $parametre_rappels_version;

        return json_encode(false);
    }
}