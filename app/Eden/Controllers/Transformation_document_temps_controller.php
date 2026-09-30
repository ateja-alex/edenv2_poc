<?php

namespace App\Eden\Controllers;

use App\Eden\Champs\Champ;
use App\Eden\Champs\Champ_date;
use App\Eden\Models\Elements\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Transformation_document_temps_controller extends Controller{

    public function calcul_utilisateurs_concernes(Request $request){

        $utilisateurs_a_transformer = management(
            'transformation_document_temps_modele',
            $request->modele_id_transformation_temps)->calcul_utilisateurs_concernes($request->all());

        return response()->json(array(
            'utilisateurs_a_transformer' => $utilisateurs_a_transformer
        ));
    }

    public function transformation(Request $request){

        $parametres = json_decode(base64_decode($request->parametres),true);

        $management_transformation = management('transformation_document_temps_modele',
            $parametres['modele_id_transformation_temps']);

        $donnees_document = $management_transformation->donnees_pour_transformation($parametres);

        $controller_document = new Document_controller();

        return $controller_document->creer($management_transformation->type_document(),false,false,$donnees_document);
    }
}