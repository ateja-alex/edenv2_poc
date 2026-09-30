<?php

namespace App\Eden\Managements\Services;

use App\Eden\Managements\Fiches\Document\Fiche_document_management;
use App\Eden\Models\Table_libre;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class Licence_service {

     /**
     *
     * Chargement des ensembles disponibles
     *
     */
    public function ensembles(){

        $ensembles = modele('licence_ensemble')->get();

        $elements = modele('licence_ensemble_element')->get()->groupBy('licence_ensemble_id');

        foreach($ensembles as $ensemble){

            if(!isset($elements[$ensemble->id]))
                continue;

            $ensemble['elements_par_type'] = $elements[$ensemble->id]->groupBy('type');
        }

        return $ensembles;
    }

    /**
     * @return array|mixed
     *
     * Retourne les routes
     *
     */
    public function groupes_routes(){

        $list = \Route::getRoutes()->getRoutesByName();

        $routes = [];
        $sous_route = null;

        $index_routes = [];

        foreach ($list as $name => $route) {

            if (strpos($route->uri, 'eden') === false)
                continue;

            $groupes = explode('.',$name);

            if(sizeof($groupes) == 1 || in_array($groupes[0],['base_eden','eden_cron','maintenance','ecommerce']))
                continue;

            $sous_route = &$routes;

            array_pop($groupes);

            $nom_groupe = '';

            foreach ($groupes as $index => $groupe) {

                $nom_groupe .= $groupe.'.';

                if (!isset($index_routes[$nom_groupe])) {
                    $sous_route[] = [];
                    $index_routes[$nom_groupe] = sizeof($sous_route) - 1;
                }

                $index_route = $index_routes[$nom_groupe];

                if($index == sizeof($groupes)-1) {
                    $sous_route[$index_route]['nom'] = $groupe;
                    $sous_route[$index_route]['index'] = implode('.',$groupes);
                }
                else {

                    if (!isset($sous_route[$index_route]['groupes']))
                        $sous_route[$index_route]['groupes'] = [];

                    $sous_route =& $sous_route[$index_route]['groupes'];
                }
            }
        }

        return $routes;
    }

    /**
     * @return array|mixed
     *
     * Retourne les routes possibles
     *
     */
    public function routes_possibles(){

        $list = \Route::getRoutes()->getRoutesByName();

        $routes_possibles = [];

        foreach ($list as $name => $route) {

            if (strpos($route->uri, 'eden') === false)
                continue;

            $groupes = explode('.',$name);

            if(sizeof($groupes) == 1 || in_array($groupes[0],['base_eden','eden_cron','maintenance','ecommerce']))
                continue;

            unset($groupes[sizeof($groupes)-1]);

            $routes_possibles[] = implode('.',$groupes);
        }

        $routes_possibles = array_unique($routes_possibles);

        return $routes_possibles;
    }

    /**
     *
     * Retourne les modules d'un type d'élement
     *
     */
    public function modules_type_element($types_elements){

        $modules_type_element = [];

        foreach($types_elements as $type_element) {

            try{
                $table_libre = table_libre($type_element);
            }catch(\Exception $e){
                continue;
            }
            
            $modules_ordonees = [];

            if (!empty($table_libre->fiche) || in_array($type_element, Variables::$documents_gescom)) {

                $modules = fiche($type_element, 0)->modules_disponibles(true);

                foreach ($modules as $index_module => $module) {

                    $modules_ordonees[] = array(
                        'nom' => in_array($type_element, Variables::$documents_gescom) ? $module['nom'] : $module . ' (' . $index_module . ')',
                        'valeur' => $type_element . '.' . $index_module
                    );
                }

            }

            $modules_type_element[$type_element] = $modules_ordonees;
        }

        return $modules_type_element;
    }

    /**
     * @param $types_elements*
     *
     * Retourne la liste des modules
     *
     */
    public function liste_modules($types_elements){

        $modules_generaux = $this->modules_generaux();

        $modules = [];

        array_map(function($module) use (&$modules){
            $modules = array_merge($modules,\Arr::pluck($module,'valeur'));
        },$modules_generaux);

        foreach($types_elements as $type_element) {

            try{
                $table_libre = table_libre($type_element);
            }catch(\Exception $e){
                continue;
            }

            if (!empty($table_libre->fiche) || in_array($type_element, Variables::$documents_gescom)) {

                $modules_disponibles = fiche($type_element, 0)->modules_disponibles(true);

                foreach ($modules_disponibles as $index_module => $module) {

                    $modules[] = $type_element . '.' . $index_module;
                }

            }
        }

        return $modules;
    }

    /**
     *
     * Types d'éléments disponibles pour la gestion des licences
     *
     */
    public function types_elements(){

        $types_elements = Table_libre::where(DB::raw('COALESCE(table_systeme,0)'),0)->get()->pluck('type_element')->toArray();

        return $types_elements;
    }

    /**
     *
     * Modules généraux
     *
     */
    public function modules_generaux(){

        $modules_generaux= array(
            'general' => array(
                array(
                    'valeur' => 'general.pieces_jointes',
                    'nom' => 'Pièces jointes'
                ),
                array(
                    'valeur' => 'general.commentaires',
                    'nom' => 'Commentaires'
                ),
                array(
                    'valeur' => 'general.messages',
                    'nom' => 'Messages'
                ),
                array(
                    'valeur' => 'general.affichage_calendrier',
                    'nom' => 'Calendrier'
                ),
                array(
                    'valeur' => 'general.abonnement_fiche',
                    'nom' => 'Abonnement'
                ),
                array(
                    'valeur' => 'general.historique',
                    'nom' => 'Historique'
                ),
                array(
                    'valeur' => 'general.timeline',
                    'nom' => 'Timeline'
                ),
            )
        );

        $management = new Fiche_document_management();

        $management->type_element = 'document';

        $management->type = 'vente';

        $modules = $management->modules_disponibles();

        foreach($modules as $index_module => $module){

            $modules_generaux['document_vente'][] = array(
                'nom' => $module['nom'],
                'valeur' => 'document_vente.'.$index_module
            );
        }

        $management->type = 'achat';

        $modules = $management->modules_disponibles();

        foreach($modules as $index_module => $module){

            $modules_generaux['document_achat'][] = array(
                'nom' => $module['nom'],
                'valeur' => 'document_achat.'.$index_module
            );
        }

        return $modules_generaux;
    }

    /**
     *
     * Synchronisation des licences depuis référence
     *
     */
    public function synchronisation($elements_reference_par_type = null){

        if(env('BASE_LICENCE') === true)
            return self::maj_licences_projets();

        define('synchronisation_licence_reference',true);

        $types_a_synchroniser = array(
            'licence_ensemble',
            'licence_ensemble_element',
            'licence',
            'licence_element'
        );

        $elements_a_supprimer = [];

        // On vient récupérer les différents éléments de EDEN MODEL API, celui ci étant la base des licences
        foreach($types_a_synchroniser as $type) {

            $elements_existants = modele($type)->get();

            $elements_par_type[$type] = $elements_existants;

            if($elements_reference_par_type == null) {

                $url = env('EDEN_MODEL_API_URL') . 'api/' . $type . '/list';

                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                        'method' => 'POST',
                    )
                );

                $contexte = stream_context_create($options);

                $elements = file_get_contents($url, false, $contexte);

                $elements = json_decode($elements, true);

                if ($elements['statut'] !== true)
                    throw($elements['code_erreur']);

                $elements = $elements['donnees'];

            }
            else
                $elements = isset($elements_reference_par_type[$type]) ? $elements_reference_par_type[$type] : [];


            // On récupère les éléments déjà synchronisés
            $cle_externe_existants = $elements_existants
                ->whereIn('specifique',[null,0])
                ->keyBy('cle_externe');

            foreach($elements as $element){

                if(isset($cle_externe_existants[$element['id']])) {

                    $element_existant = $cle_externe_existants[$element['id']];

                    $modifications = [];

                    //On vient récupérer les modifications qui ont été effectuées en les comparant avec la base de données
                    foreach(champs_libres($type) as $champ_libre){

                        if(!in_array($champ_libre->nom_sql,['modifie_le','cree_le','cree_par','modifie_par','cle_externe','nombre','nombre_utilises'])
                            && ($champ_libre->type != 42 && $element_existant->{$champ_libre->nom_sql} != $element[$champ_libre->nom_sql]))
                            $modifications[$champ_libre->nom_sql] = $element[$champ_libre->nom_sql];

                    }

                    // S'il y a une modification, on l'enregistre
                    if(!empty($modifications)){

                        management($type,$element_existant->id,$element_existant)->enregistre($modifications);
                    }

                    unset($cle_externe_existants[$element['id']]);
                    continue;
                }

                $modifications = [];

                // Pour les éléments, on vient vérifier que les nouveaux n'existent pas en spécifique, sinon on vient récupérer l'id de l'ensemble lié
                if($type == 'licence_ensemble_element'){

                    $licence_ensemble_id = $elements_par_type['licence_ensemble']->where('cle_externe',$element['licence_ensemble_id']);

                    if($licence_ensemble_id->isEmpty())
                        continue;

                    $licence_ensemble_id = $licence_ensemble_id->first()->id;

                    $element_existant_specifique = $elements_existants->where('specifique', 1)
                        ->where('type', $element['type'])
                        ->where('nom', $element['nom'])
                        ->where('licence_ensemble_id', $licence_ensemble_id)->first();

                    if (!empty($element_existant_specifique)) {

                        management($type, $element_existant_specifique->id, $element_existant_specifique)->enregistre([
                            'cle_externe' => $element['id'],
                            'specifique' => 0
                        ]);
                        continue;
                    }
                    else
                        $modifications['licence_ensemble_id'] = $licence_ensemble_id;
                }

                // Pour les éléments, on vient vérifier que les nouveaux n'existent pas en spécifique, sinon on vient récupérer l'id de l'ensemble lié et l'id la licence
                if($type == 'licence_element') {

                    $licence_id = $elements_par_type['licence']->where('cle_externe', $element['licence_id']);
                    $ensemble_id = $elements_par_type['licence_ensemble']->where('cle_externe', $element['ensemble_id']);

                    if ($ensemble_id->isEmpty() || $licence_id->isEmpty())
                        continue;

                    $licence_id = $licence_id->first()->id;
                    $ensemble_id = $ensemble_id->first()->id;

                    $element_existant_specifique = $elements_existants->where('specifique', 1)
                        ->where('type', $element['type'])
                        ->where('nom', $element['nom'])
                        ->where('ensemble_id', $ensemble_id)
                        ->where('licence_id', $licence_id)
                        ->first();

                    if (!empty($element_existant_specifique)) {

                        management($type, $element_existant_specifique->id, $element_existant_specifique)->enregistre([
                            'cle_externe' => $element['id'],
                            'specifique' => 0
                        ]);
                        continue;
                    }
                    else {
                        $modifications['licence_id'] = $licence_id;
                        $modifications['ensemble_id'] = $ensemble_id;
                    }
                }

                foreach(champs_libres($type) as $champ_libre){

                    if(!in_array($champ_libre->nom_sql,['modifie_le','cree_le','cree_par','modifie_par','cle_externe','nombre','nombre_utilises']) && $champ_libre->type != 42)
                        $modifications[$champ_libre->nom_sql] = $element[$champ_libre->nom_sql];

                }

                // On enregistre le nouvel élément
                $modifications['cle_externe'] = $element['id'];

                $management_element =  management($type);
                $management_element->enregistre($modifications);
                $elements_par_type[$type]->push($management_element->modele);
            }

            $elements_a_supprimer[$type] = $cle_externe_existants;
        }

        // S'il reste des éléments, cela veut dire qu'ils ont été supprimé de la base MODEL
        foreach(array_reverse($types_a_synchroniser) as $type) {

            //On supprime ceux qui n'appartiennent plus
            foreach ($elements_a_supprimer[$type] as $element) {

                management($type, $element->id, $element)->supprime();
            }
        }

        return true;
    }

    /**
     *
     * Permet de mettre à jour les licences de tous les projets
     *
     */
    public static function maj_licences_projets(){

        $url_index = env('EDEN_CONSOLE_API_URL').'api/projet/list';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: ". env('EDEN_CONSOLE_API_KEY') ."\r\n",
                'method'  => 'POST',
            ),
        );

        $contexte  = stream_context_create($options);
        $requete = file_get_contents($url_index, false, $contexte);

        $requete = json_decode($requete,true);

        if($requete['statut'] !== true)
            throw($requete['code_erreur']);

        $projets_easydev = collect($requete['donnees']);

        $environnements = [
            'version_prod' => 'url_site_suivi_recette',
            'version_preprod' => 'url_preprod_synchronisation'
        ];

        $types_a_synchroniser = array(
            'licence_ensemble',
            'licence_ensemble_element',
            'licence',
            'licence_element'
        );

        $elements_par_type = [];

        foreach($types_a_synchroniser as $type){

            $elements_par_type[$type] = modele($type)->get();
        }

        $variables = http_build_query(
            array(
                'elements_par_type' => $elements_par_type
            )
        );

        $infos_return = array();


        // On vient mettre à jour les licences de tous les projets easydev
        foreach($projets_easydev as $projet) {

            foreach ($environnements as $version => $environnement) {

                if (empty($projet[$environnement]) || $projet[$version] < 2022000069)
                    continue;

                try {
                    $url_index = $projet[$environnement] . '/api/maj_licences_projet';

                    $options = array(
                        'http' => array(
                            'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . $projet['cle_api'] . "\r\n",
                            'method' => 'POST',
                            'content' => $variables,
                        ),
                        "ssl" => [
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ]
                    );

                    $contexte = stream_context_create($options);
                    $requete = file_get_contents($url_index, false, $contexte);

                    if(isset($requete['retour']) && $requete['retour'] !== true)
                        $infos_return[] = 'Erreur maj projet '.$projet['nom'].' : '.$projet['erreur'];

                }
                catch(\Exception $e){

                    $infos_return[] = 'Erreur maj projet '.$projet['nom'];
                }

            }
        }

        return $infos_return;
    }
}
