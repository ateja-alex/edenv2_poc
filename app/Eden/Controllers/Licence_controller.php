<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Table_libre;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Licence_controller extends Controller {

    /**
     *
     * Affichage des licences
     *
     */
    public function afficher(){

        $licences = modele('licence')->get();

        $ensembles = modele('licence_ensemble')
            ->join('licence_element','ensemble_id','licence_ensemble.id')
            ->where(function($condition){
                $condition->where('licence_element.inactif',0)
                    ->orWhereNull('licence_element.inactif');
            })
            ->where(function($condition){
                $condition->where('licence.inactif',0)
                    ->orWhereNull('licence.inactif');
            })
            ->join('licence','licence.id','licence_id')
            ->select('licence_ensemble.*','licence.id as licence_id')
            ->groupBy('licence.id','licence_ensemble.id')
            ->get()->groupBy('licence_id');

        foreach($licences as $licence){

            $licence->ensembles = isset($ensembles[$licence->id]) ? $ensembles[$licence->id] : [];
        }
        return view('eden::parametrage.licence.index',array(
            'licences' => $licences
        ));
    }

    /**
     *
     * Permet de récupérer les licences.
     *
     */
    public function recuperer_licences(){

        $licences = modele('licence')->get();

        $utilisateurs = modele('utilisateur')
            ->select('licence_id', 'id')
            ->whereNotNull('licence_id')
            ->get()
            ->groupBy('licence_id')
            ->map(function($utilisateurs){
                return $utilisateurs->pluck('id');
            })->toArray();

        forEach($licences as $licence){
            $licence['utilisateurs'] = $utilisateurs[$licence['id']] ?? array();
        }

        return response()->json($licences);
    }
    /**
     *
     * Permet de récupérer les informations nécessaires à la gestion d'une licence
     *
     */
    public function informations(){

        $service_licence = service('licence');

        $types_elements = $service_licence->types_elements();

        $routes = $service_licence->groupes_routes();

        $modules_generaux = $service_licence->modules_generaux();

        $licence_ensemble_id = request()->licence_ensemble_id;

        if(empty($licence_ensemble_id)){
            return response()->json(array(
                'types_elements' =>  $types_elements,
                'routes' => $routes,
                'modules_generaux' => $modules_generaux,
                'modules_type_element' => [],
                'valeurs_elements' => [],
            ));
        }

        $elements_par_type = modele('licence_ensemble_element')
            ->where('licence_ensemble_id',$licence_ensemble_id)
            ->get()->groupBy('type');

        $elements_tries = array(
            'routes' => [],
            'types_elements' => [],
            'modules' => [],
        );

        foreach($elements_par_type as $type => $elements){

            switch ($type) {
                case 1:
                    $type = 'routes';
                    break;
                case 2:
                    $type = 'types_elements';
                    break;
                case 3:
                    $type = 'modules';
                    break;
            }

            $elements_tries[$type] = $elements->pluck('nom')->toArray();
        }

        if(in_array('tous',$elements_tries['types_elements']))
            $modules_type_element = $service_licence->modules_type_element($types_elements);
        else
            $modules_type_element = $service_licence->modules_type_element($elements_tries['types_elements']);

        return response()->json(array(
            'types_elements' =>  $types_elements,
            'routes' => $routes,
            'modules_generaux' => $modules_generaux,
            'modules_type_element' => $modules_type_element,
            'valeurs_elements' => $elements_tries,
        ));
    }

    /**
     *
     * Permet de récupérer les modules de fiche d'un type élément
     *
     */
    public function modules_type_element(){

        $types_elements = request()->types_elements;

        return response()->json(array(
            'modules' => service('licence')->modules_type_element($types_elements)
        ));
    }

    /**
     *
     * Chargement des ensembles disponibles
     *
     */
    public function ensembles(){

        $ensembles_elements = [];

        $licence_id = request()->licence_id;

        if(!empty($licence_id)){

            $ensembles_elements = modele('licence_element')
                ->where('licence_id',$licence_id)->groupBy('ensemble_id')->get()->pluck('ensemble_id')->toArray();
        }

        return response()->json(array(
            'ensembles' => service('licence')->ensembles(),
            'ensembles_elements' => $ensembles_elements
        ));
    }

}