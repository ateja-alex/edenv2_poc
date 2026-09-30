<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Managements\Listes_management;
use App\Eden\Models\Easysoft_rapports_informations;
use Log;
use Illuminate\Http\Request;

class Erp_datatable_controller extends Controller
{


    public function maj_liste(Request $request, $type, $id) {

        $management = new Listes_management();

        $filtres = $request->input('filtres');

        $liste_id = $id;
        $type_element = $type;
        $debut = $request->input('start');
        $nombre_elements = $request->input('length');
        $recherche =  $request->input('search.value');
        $index_colonne_tri = $request->input('order.0.column');
        $colonne_tri = $request->input('columns.' . $index_colonne_tri . '.data');
        $sens_tri = $request->input('order.0.dir');

        $colonnes = array();

        foreach ($request->input('columns') as $index_colonne => $donnees_colonne) {

            foreach ($donnees_colonne as $type_donnee => $valeur) {

                $colonnes[$index_colonne][$type_donnee] = $valeur;
            }
        }

        $donnees = $management->obtenir_informations_liste(
            $liste_id,
            $type_element,
            $debut,
            $nombre_elements,
            $recherche,
            $colonnes,
            $colonne_tri,
            $sens_tri
        );
        // foreach ($filtres as $filtre) {

        //     Log::info(base64_decode(Easysoft_rapports_informations::find(199+$filtre)->first()->filtres));
        // }


        return Response(json_encode(
            array(
                "filtres" => $request->input('filtres'),
                "recordsTotal" => $donnees['nombre_enregistrements'],
                "recordsFiltered" => $donnees['nombre_resultats'],
                "data" => $donnees['lignes']
            )),
            200
        );
    }
}
