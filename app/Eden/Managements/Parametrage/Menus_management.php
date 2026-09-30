<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;

class Menus_management {

    /**
     *
     * Permet d'initialiser les menus correctement
     *
     */
    public function recuperation_menus($extranet = false){

        $menu = modele('menus');

        if($extranet)
            $menu = $menu->where('extranet',1);

        $menu = $menu->first();

        $categories = modele('menus_categories')
            ->select('menus_categories.*', DB::raw("'menus_categories' as type_element"))
            ->where('id_menu_parent', $menu->id)
            ->get();

        $liens = modele('menus_liens')
            ->select('menus_liens.*', DB::raw("'menus_liens' as type_element"))
            ->where(function($requete) use ($categories, $menu){
                $requete->where('id_menu_parent', $menu->id)->orWhereIn('id_categorie_parent', $categories->pluck('id'));
            })
            ->get()
            ->groupBy('id_categorie_parent');

        $management_lien = management('menus_liens');

        foreach($categories as $categorie) {

            $categorie->sous_menus = array();

            if (isset($liens[$categorie->id])){

                $sous_menus = $liens[$categorie->id]->sortBy('ordre')->values();

                $sous_menus->each(function($sous_menu) use ($management_lien){

                    $management_lien->modele = $sous_menu;
                    $management_lien->charge_valeurs_champs_multiselection();

                    if($sous_menu->type_lien == 4){
                        
                        $sous_menu->lien = $sous_menu->route;
                        $sous_menu->target = '_blank';
                    }
                    else
                        $sous_menu->lien = route($sous_menu->route, $sous_menu->parametres, false);
                });

                $categorie->sous_menus = $sous_menus;
            }
        }

        if(!empty($liens['']) && $liens['']->isNotEmpty())
            $categories = $categories->concat($liens['']->each(function($sous_menu) use ($management_lien){

                $management_lien->modele = $sous_menu;
                $management_lien->charge_valeurs_champs_multiselection();

                if($sous_menu->type_lien == 4){

                    $sous_menu->lien = $sous_menu->route;
                    $sous_menu->target = '_blank';
                }
                else
                    $sous_menu->lien = route($sous_menu->route, $sous_menu->parametres, false);
            }));

        $menus = $categories->sortBy('ordre')->values();

        return $menus;

    }

    public function donnees_selection_routes(){

        $types_elements = Table_libre::orderBy('element')->get();
        $rapports = Rapport_libre::where(function($requete){
            $requete->whereNull('liste_sur_fiche')
                ->orWhere('liste_sur_fiche','');
        })->where(function($requete){
            $requete->whereNull('export')
                ->orWhere('export','');
        })->orderBy('titre')->get();

        $routes = $this->informations_routes();

        $informations = [
            'parametres_par_type' => [
                1 => $types_elements,
                2 => $rapports,
                5 => $routes['parametres_routes'],
            ],
            'routes' => [
                'routes_frequentes' => $routes['routes_raccourcis'],
                'routes' => $routes['routes'],
            ]
        ];

        return $informations;
    }

    /**
     * @return array
     *
     * Permet de récupérer les informations des routes
     *
     */
    public function informations_routes($nom_route = null){

        // On trie les routes par controlleurs
		$routes_tmp = \Route::getRoutes();
		$tableau_routes = array();

		foreach($routes_tmp as $route) {

			if(!empty($route->getName()) && in_array('GET', $route->methods)){

                if($nom_route != null && $nom_route != $route->getName())
                    continue;

                $name_route = $route->getName();

				if(!isset($route->action['controller']))
                    $controller = "Sans Controller";
                else{

                    $controller_class = explode('\\', explode('@', $route->action['controller'])[0]);

                    $controller = array_pop($controller_class);
                }

                if (!array_key_exists($controller, $tableau_routes))
                    $tableau_routes[$controller] = array();

                $tableau_routes[$controller][] = $name_route;

                $tableau_parametres[$name_route] = array();

                $signature_parametres = $route->signatureParameters();

                foreach($signature_parametres as $parametre){

                    $parametre->obligatoire = !$parametre->isOptional();

                    if(!$parametre->hasType())
                        $tableau_parametres[$name_route][] = $parametre;
                }
			}
		}

        ksort($tableau_routes);

		$tableau_routes_tmp = [];

		foreach($tableau_routes as $controller => $les_noms){

			asort($les_noms);

			$tableau_routes_tmp[$controller] = $les_noms;

		}

		$tableau_routes = $tableau_routes_tmp;

        if($nom_route != null && isset($tableau_parametres[$nom_route]))
            return $tableau_parametres[$nom_route];
        else if($nom_route != null)
            throw new \Exception("La route " . $nom_route . " est liée à une route qui n'existe plus. Veuillez la supprimer.");

        return array(
            'routes' => $tableau_routes,
            'parametres_routes' => $tableau_parametres,
            'routes_raccourcis' => $this->routes_raccourcis()
        );
    }

    /**
     * @return \string[][]
     *
     * Permet de récupérer le raccourci des routes
     *
     */
    public function routes_raccourcis(){

        $routes_raccourcis = array(
			array(
				'url' => 'eden/planning',
				'nom' => 'planning.index',
				'libelle' => 'Planning'
			),
			array(
				'url' => 'eden/projets/saisie_des_temps',
				'nom' => 'saisie_des_temps.index',
				'libelle' => 'Saisie des temps'
			),
			array(
				'url' => 'eden/rapports',
				'nom' => 'base_eden.rapport.liste',
				'libelle' => 'Rapports'
			),
			array(
				'url' => 'eden/parametrage/elements',
				'nom' => 'parametrage.elements',
				'libelle' => 'Autres paramètres'
			),
			array(
				'url' => 'eden/calendrier',
				'nom' => 'calendrier.afficher',
				'libelle' => 'Calendrier'
			),
			array(
				'url' => 'eden/articles',
				'nom' => 'article.liste',
				'libelle' => 'Articles'
			),
            array(
				'url' => 'eden/parametrage/utilisateurs',
				'nom' => 'parametrage.utilisateur.liste',
				'libelle' => 'Utilisateurs'
			),
            array(
				'url' => 'eden/intranet',
				'nom' => 'intranet.index',
				'libelle' => 'Intranet'
			),
		);

        return $routes_raccourcis;
    }
}
