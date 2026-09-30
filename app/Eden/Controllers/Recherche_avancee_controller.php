<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Models\Champ_libre;

class Recherche_avancee_controller extends Controller {

    public function donnees_initialisation(Request $parametres){

        $parametres = $parametres->all();

        $champs_libres = Champ_libre::where(function($where){
            $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
        })->get()->groupBy('type_element');

        $champs_libres = Champ_libre_management::filtrage($parametres['type_element'],$champs_libres);

        return response()->json([
            'champs_libres' => $champs_libres,
            'recherches_par_categories' => Liste_libre_management::informations_recherche_avancee($parametres)
        ]);
    }

    /**
     * @param Request $parametres
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer la liste des recherches avancées disponibles sous certains paramétres
     *
     */
    public function listes(Request $parametres){

        $parametres = $parametres->all();

        return response()->json(Liste_libre_management::informations_recherche_avancee($parametres));
    }

    /**
     * @param Request $parametres
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer les champs libres disponibles pour un type d'élément donné
     *
     */
    public function champs_libres(Request $parametres){

        $parametres = $parametres->all();

        $champs_libres = Champ_libre::where(function($where){
             $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
         })->get()->groupBy('type_element');

        $champs_libres = Champ_libre_management::filtrage($parametres['type_element'],$champs_libres);

        return response()->json($champs_libres);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer une recherche avancée avec la structure
     *
     */
    public function recuperer($id){

        $recherche_avancee = modele('recherche_avancee')->where('id',$id)->first();

        if(!empty($recherche_avancee)){
            $recherche_avancee->structure = management('recherche_avancee',$id,$recherche_avancee)->structure();
        }

        return response()->json($recherche_avancee);
    }

    public function recherche_type($type){

        $recherches_avancees = modele('recherche_avancee')->where('type', $type)->get();

        foreach($recherches_avancees as $recherche_avancee){
            $recherche_avancee->structure = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)->structure();
        }

        return response()->json($recherches_avancees);
    }

    /**
     * @param Request $parametres
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet d'enregistrer une recherche avancée dans la bdd
     *
     */
    public function enregistrer(Request $parametres){

        $parametres = $parametres->all();

        $management = management('recherche_avancee');

        if(isset($parametres['id'])) {
            $recherche_avancee = modele('recherche_avancee')->where('id', $parametres['id'])->first();

            if(!empty($recherche_avancee))
                $management = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee);
        }

        $management->enregistre($parametres);

        $management->modele->structure = $management->structure();

        return response()->json($management->modele);
    }

    public function supprimer($id_recherche_avancee){

        $management = management('recherche_avancee',$id_recherche_avancee);

        $retour = $management->supprime();

        return response()->json(array('retour' => $retour));
    }

}