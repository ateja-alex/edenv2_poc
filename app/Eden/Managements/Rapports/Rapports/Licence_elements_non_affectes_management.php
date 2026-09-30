<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapport_liste_management;
use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des rapports
 *
 */
class Licence_elements_non_affectes_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 *
	 * Liste les éléments non affectés à des ensembles de licence
	 *
	 */
    public function genere($ajax = false) {

        $service_licence = service('licence');

		$types_elements = $service_licence->types_elements();

        $routes = $service_licence->routes_possibles();

        $liste_modules = $service_licence->liste_modules($types_elements);

        $elements_utilises = modele('licence_ensemble_element')
            ->get()->groupBy('type')->map(function($element){
                return array_unique($element->pluck('nom')->toArray());
            })->toArray();

        if(!empty($elements_utilises['1'])) {
            $routes_non_references = array_diff($routes, $elements_utilises['1']);

            foreach($routes_non_references as $index_route_non_reference => $route_non_reference){

                $routes_split = explode('.',$route_non_reference);

                foreach($routes_split as $index => $route_split){

                    if($index == sizeof($routes_split) -1)
                        continue;

                    if(in_array($route_split,$elements_utilises['1']))
                        unset($routes_non_references[$index_route_non_reference]);
                }
            }

            $routes_non_references = array_values($routes_non_references);
        }
        else
            $routes_non_references = $routes;

        if(!empty($elements_utilises['2']))
            $types_elements_non_references = array_values(array_diff($types_elements,$elements_utilises['2']));
        else
            $types_elements_non_references = $types_elements;

         if(!empty($elements_utilises['3']))
            $modules_non_references = array_values(array_diff($liste_modules,$elements_utilises['3']));
        else
            $modules_non_references = $liste_modules;

        $titres = array(
            'Routes',
            'Types éléments',
            'Modules'
        );

        $this->rapport->titres($titres);

        $nombres_lignes = max(sizeof($routes_non_references),sizeof($types_elements_non_references),sizeof($modules_non_references));

        for($compte_ligne = 0; $compte_ligne < $nombres_lignes;$compte_ligne++){

            $ligne = array(
                $routes_non_references[$compte_ligne] ?? null,
                $types_elements_non_references[$compte_ligne] ?? null,
                $modules_non_references[$compte_ligne] ?? null
            );

            $this->rapport->ligne($ligne);
        }

        return $this->rapport->genere($ajax);
    }

}