<?php

namespace App\Eden\Managements\Services;

use App\Eden\Champs_libres;
use App\Eden\Exceptions\Eden_exception;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Rapports\Filtres_et_options\Champ;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Table_libre;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class Traduction_service {

    private $traductions_valeurs = false;

    /**
     *
     * Permet de synchroniser les traductions avec la synchronisation
     *
     */
    public function synchroniser(){
        $date_derniere_synchro = parametre('date_derniere_synchro_traductions');

        $url_index = env('EDEN_MODEL_API_URL').'api/traduction_index/list';
        $url_valeur = env('EDEN_MODEL_API_URL').'api/traduction_valeur/list';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                'method'  => 'POST',
            )
        );

        if(!empty($date_derniere_synchro))
            $options['http']['content'] = http_build_query([
                'where' => [
                    [
                        'modifie_le',
                        '>',
                        $date_derniere_synchro
                    ]
                ]
            ]);

        $contexte  = stream_context_create($options);
        $traductions_index = file_get_contents($url_index, false, $contexte);
        $traductions_valeurs = file_get_contents($url_valeur, false, $contexte);

        $traductions_index = json_decode($traductions_index,true);

        if($traductions_index['statut'] !== true)
            throw(new Eden_exception($traductions_index['donnees']));

        $traductions_index = $traductions_index['donnees'];

        $langues = modele('traduction_langue')->get()->pluck('code','id')->toArray();

        $traductions_valeurs = json_decode($traductions_valeurs,true);

        if($traductions_valeurs['statut'] !== true)
            throw($traductions_valeurs['code_erreur']);

        $traductions_valeurs = $traductions_valeurs['donnees'];

        //On structure les valeurs par index
        $traductions_valeurs_organises = array();

        foreach($traductions_valeurs as $traduction_valeur){
            $traductions_valeurs_organises[$traduction_valeur['index']][] = $traduction_valeur;
        }

        $traductions_index_existante = modele('traduction_index')->get()->keyBy('index')->toArray();
        $traductions_valeurs_existante = modele('traduction_valeur')->get()->toArray();

        $traductions_valeurs_existante_organises = array();

        foreach($traductions_valeurs_existante as $traduction_valeur_existante) {

            $traductions_valeurs_existante_organises[$traduction_valeur_existante['index']][$traduction_valeur_existante['langue']] = array(
                'id' => $traduction_valeur_existante['id'],
                'valeur' => $traduction_valeur_existante['traduction_standard']
            );
        }

        $traductions_valeurs_existante = $traductions_valeurs_existante_organises;

        foreach($traductions_index as $traduction_index){

            if(!isset($traductions_index_existante[$traduction_index['index']])){

                $management_traduction_index = management('traduction_index');

                $management_traduction_index->enregistre(
                    array(
                        'index' => $traduction_index['index'],
                        'categorie' => $traduction_index['categorie']
                    )
                );

            }
            else{
                $management_traduction_index = management('traduction_index',$traductions_index_existante[$traduction_index['index']]['id']);

                if($traduction_index['categorie'] != $traductions_index_existante[$traduction_index['index']]['categorie']) {
                    // On met à jour la catégorie au cas où celle-ci à changer
                    $management_traduction_index->enregistre_modele(
                        array(
                            'categorie' => $traduction_index['categorie']
                        )
                    );
                }
            }
        }

        foreach($traductions_valeurs_organises as $index => $traductions_valeurs){

            foreach($traductions_valeurs as $traduction_valeur){

                if(isset($traductions_valeurs_existante[$index][$traduction_valeur['langue']])){

                    $traduction_existante = $traductions_valeurs_existante[$index][$traduction_valeur['langue']];

                    if($traduction_existante['valeur'] == $traduction_valeur['traduction_standard'])
                        continue;

                    management('traduction_valeur',$traduction_existante['id'])->enregistre_modele(
                        array(
                            'traduction_standard' => $traduction_valeur['traduction_standard']
                        )
                    );

                }
                else if(in_array($traduction_valeur['langue'], $langues)) {

                    management('traduction_valeur')->enregistre(
                        array(
                            'index' => $traduction_valeur['index'],
                            'langue' => $traduction_valeur['langue'],
                            'traduction_standard' => $traduction_valeur['traduction_standard']
                        )
                    );
                }
            }

        }

        if(!defined('migration_en_cours'))
            Cache_management::genere_traductions();

        parametre('date_derniere_synchro_traductions',date('Y-m-d H:i:s',strtotime('-5 minutes')));

        return true;

    }

    /**
     *
     * Récupération des traductions par langue d'une catégorie ou de toutes les catégories
     *
     */
    public function traductions_categorie($id_categorie = null,$filtrage = null){

        $langues = langues();

        $traductions = [];

        if(empty($filtrage))
            $sous_categories = $this->sous_categories($id_categorie);

        $categories_index = modele('traduction_index');

        if(!empty($filtrage)) {
            $categories_index = $categories_index->where('traduction_index.index', 'Like', $filtrage . '%');
            $categories_index = $categories_index->where('traduction_index.index', 'Not Like', $filtrage . '%.%');
        }

        $categories_index = $categories_index->get()->pluck('categorie','index')->toArray();

        $index_standards = modele('traduction_valeur')
                ->whereNotNull('traduction_standard');

        if(!empty($filtrage)) {
            $index_standards = $index_standards->where('index', 'Like', $filtrage . '%');
            $index_standards = $index_standards->where('index', 'Not Like', $filtrage . '%.%');
        }

        $index_standards = $index_standards->get()->pluck('index')->toArray();

        foreach($langues as $langue){

            $requete = modele('traduction_valeur')
                ->select('traduction_index.index','traduction_specifique','traduction_standard')
                ->join('traduction_index','traduction_index.index','traduction_valeur.index')
                ->where(function($sous_requete){
                  $sous_requete->whereNull('traduction_index.inactif')
                  ->orWhere('traduction_index.inactif',0);
                })
                ->where('langue',$langue->code);

            if(!empty($id_categorie))
                $requete = $requete->where('categorie',$id_categorie);

            if(!empty($filtrage)) {
                $requete = $requete->where('traduction_index.index', 'Like', $filtrage . '%');
                $requete = $requete->where('traduction_index.index', 'Not Like', $filtrage . '%.%');
            }

            $traductions_valeurs = $requete->get()->keyBy('index')->toArray();

            foreach ($traductions_valeurs as $index_traduction => &$traduction_valeur) {

                $traduction_valeur[$langue->code] = array(
                    'traduction_standard' => $traduction_valeur['traduction_standard'],
                    'traduction_specifique' => $traduction_valeur['traduction_specifique'],
                );

                unset($traduction_valeur['traduction_standard']);
                unset($traduction_valeur['traduction_specifique']);

            }

            $traductions = array_merge_recursive($traductions,$traductions_valeurs);
        }

        $traductions_formatees = array();

        $requete = modele('traduction_index')
            ->select('index')
            ->whereNotIn('index',array_keys($traductions));

        if(!empty($id_categorie))
            $requete = $requete->where('categorie',$id_categorie);

        if(!empty($filtrage)) {
            $requete = $requete->where('traduction_index.index', 'Like', $filtrage . '%');
            $requete = $requete->where('traduction_index.index', 'Not Like', $filtrage . '%.%');
        }

        $traductions_index_manquant = $requete->get()->keyBy('index')->toArray();

        $traductions = array_merge($traductions,$traductions_index_manquant);

        ksort($traductions);

        foreach($traductions as $index_traduction => $traduction){

            $traduction['index'] = $index_traduction;

            if(in_array($index_traduction,$index_standards))
                $traduction['type'] = 'standard';
            else
                $traduction['type'] = 'specifique';

            foreach($langues as $langue){
                if(!isset($traduction[$langue->code])){
                    $traduction[$langue->code] = array(
                        'traduction_standard' => null,
                        'traduction_specifique' => null,
                    );
                }
            }

            $index_sous_categorie = 0;
            $sous_categorie = null;

            $index_categorie = $this->index_categorie($categories_index[$index_traduction],$index_traduction);

            $traduction['index_categorie'] = $index_categorie;

            if(isset($sous_categories[$index_categorie])) {

                $index_sous_categorie = $sous_categories[$index_categorie]['index_correspondance'];
                $sous_categorie = $sous_categories[$index_categorie]['valeur'];
            }
            else{
                $index_sous_categorie = 0;
                $sous_categorie = null;
            }

            if(!isset($traductions_formatees[$index_sous_categorie])){
                $traductions_formatees[$index_sous_categorie] = array(
                    'sous_categorie' => $sous_categorie,
                    'traductions' => []
                );
            }

            $traductions_formatees[$index_sous_categorie]['traductions'][] = $traduction;

        }

        ksort($traductions_formatees);

        return array_values($traductions_formatees);
    }

    /**
     *
     * Permet de calculer et créer un index de tradution en fonction de paramétre
     *
     */
    public function calcul_index_traduction($categorie,$informations,$index_a_creer,$standard = false){

        if($standard === false && env('BASE_TRADUCTION') === true)
            return null;

        $base_index_traduction = implode('.',$informations);
        $indexs = [];
        foreach ($index_a_creer as $index => $valeur) {
            $indexs[] = $base_index_traduction.'.'.$index;
        }

        if($this->traductions_valeurs !== false) {

            $traductions_valeurs = collect([]);

            foreach($indexs as $index){
                if(isset($this->traductions_valeurs[$index]))
                    $traductions_valeurs->push($this->traductions_valeurs[$index]);
            }
        }
        else
            $traductions_valeurs = modele('traduction_valeur')
                ->whereIn('index',$indexs)
                ->where('langue','fr')
                ->get();

        if(!empty($traductions_valeurs))
            $traductions_valeurs = $traductions_valeurs->keyBy('index');

        foreach($index_a_creer as $index => $valeur_par_defaut) {

            $traduction_valeur = $traductions_valeurs[$base_index_traduction.'.'.$index] ?? null;

            if($traduction_valeur == null) {

                $management_traduction_index = management('traduction_index');

                $retour = $management_traduction_index->enregistre(
                    array(
                        'index' => $base_index_traduction . '.' . $index,
                        'categorie' => $categorie,
                    )
                );

                $traduction_valeur = array(
                    'index' => $base_index_traduction . '.' . $index,
                    'langue' => 'fr',
                );

                if ($standard === true)
                    $traduction_valeur['traduction_standard'] = $valeur_par_defaut;
                else
                    $traduction_valeur['traduction_specifique'] = $valeur_par_defaut;

                management('traduction_valeur')->enregistre($traduction_valeur);
            }
            else{

                if($traduction_valeur->traduction_standard != $valeur_par_defaut && $traduction_valeur->specifique != $valeur_par_defaut) {
                    management('traduction_valeur', $traduction_valeur->id)->enregistre(
                        array(
                            'traduction_specifique' => $valeur_par_defaut,
                        )
                    );
                }
            }
        }

        if(!defined('migration_en_cours'))
            Cache_management::genere_traductions();

        return $base_index_traduction;
    }

    /**
     *
     * Permet de créer de multiple index de traduction
     *
     */
    public function calcul_multiple_index_traduction($structure_index_traductions,$standard = false,$regeneration_cache = true){

        if($standard === false && env('BASE_TRADUCTION') === true)
            return null;

        $traductions_valeurs = modele('traduction_valeur')
            ->where('langue', 'fr')
            ->get()->keyBy('index');

        $index_traduction_creer = [];

        foreach($structure_index_traductions as $structure_index_traduction) {

            $informations = $structure_index_traduction['informations_traductions'];

            $index_a_creer = $structure_index_traduction['index_a_creer'];

            $categorie = $structure_index_traduction['categorie'];

            $base_index_traduction = implode('.', $informations);

            $index_traduction_creer[] = $base_index_traduction;

            foreach ($index_a_creer as $index => $valeur_par_defaut) {

                $traduction_valeur = isset($traductions_valeurs[$base_index_traduction . '.' . $index]) ? $traductions_valeurs[$base_index_traduction . '.' . $index] : null;

                if ($traduction_valeur == null) {

                    $management_traduction_index = management('traduction_index');

                    $management_traduction_index->enregistre(
                        array(
                            'index' => $base_index_traduction . '.' . $index,
                            'categorie' => $categorie,
                        )
                    );

                    $traduction_valeur = array(
                        'index' => $base_index_traduction . '.' . $index,
                        'langue' => 'fr',
                    );

                    if ($standard === true)
                        $traduction_valeur['traduction_standard'] = $valeur_par_defaut;
                    else
                        $traduction_valeur['traduction_specifique'] = $valeur_par_defaut;

                    management('traduction_valeur')->enregistre($traduction_valeur);
                } else {

                    if ($traduction_valeur->traduction_standard != $valeur_par_defaut && $traduction_valeur->specifique != $valeur_par_defaut) {
                        management('traduction_valeur', $traduction_valeur->id)->enregistre(
                            array(
                                'traduction_specifique' => $valeur_par_defaut,
                            )
                        );
                    }
                }
            }
        }

        if (!defined('migration_en_cours') && $regeneration_cache === true)
            Cache_management::genere_traductions();

        return $index_traduction_creer;
    }

    /**
     *
     * Permet de récupérer les sous catégories possible en fonction de la catégorie
     *
     */
    public function sous_categories($id_categorie){

        $sous_categories = [];

        if(empty($id_categorie))
            return $sous_categories;

        //Pour les champs libres
        if($id_categorie == 1){

            $sous_categories = Champ_libre::select('eden_champslibres.type_element as index_correspondance','eden_tableslibres.index_traduction as valeur','eden_champslibres.index_traduction')
                ->join('eden_tableslibres','eden_tableslibres.type_element','eden_champslibres.type_element')
                ->whereNotNull('eden_champslibres.index_traduction')
                ->get()
                ->keyBy('index_traduction')
                ->toArray();

            foreach($sous_categories as &$sous_categorie){
                $sous_categorie['valeur'] = traduction($sous_categorie['valeur'].'.nom_table');
            }

        }

        //Pour les tables libres
        else if($id_categorie == 2){

            $sous_categories = Table_libre::select('type_element as index_correspondance','index_traduction')
                ->whereNotNull('index_traduction')
                ->get()
                ->keyBy('index_traduction')
                ->toArray();

            foreach($sous_categories as &$sous_categorie){
                $sous_categorie['valeur'] = traduction($sous_categorie['index_traduction'].'.nom_table');
            }

        }

        //Pour les colonnes listes libres
        else if($id_categorie == 5 || $id_categorie == 6){

            if($id_categorie == 5)
                $modele = new Colonne();

            else if($id_categorie == 6)
                $modele = new Liste_libre_calcul();

            $sous_categories = $modele->select(
                    DB::raw('IF(id_rapport IS NULL OR id_rapport = "",CONCAT("liste_",eden_listeslibres.type_element),CONCAT("rapport_",id_rapport)) as index_correspondance'),
                    DB::raw('IF(id_rapport IS NULL OR id_rapport = "",CONCAT("Liste ",eden_listeslibres.type_element),CONCAT("Rapport ",id_rapport)) as valeur'),
                    'index_traduction'
                )
                ->join('eden_listeslibres','eden_listeslibres.id','liste_libre_id')
                ->whereNotNull('index_traduction')
                ->get()
                ->keyBy('index_traduction')
                ->toArray();
        }

        //Pour les menus
        else if($id_categorie == 7){

            $traduction_index = modele('traduction_index')->where('categorie','7')->get()->pluck('index')->toArray();

            foreach($traduction_index as $index){

                $index = $this->index_categorie(7,$index);

                $id = '';
                $valeur = '';

                $index_tableau = explode('.',$index);

                if($index_tableau[0] == 'menus_extranet') {
                    $id = 'extranet_';
                    $valeur = 'Extranet : ';
                }

                if($index_tableau[1] == 'categorie') {
                    $id .= 'categorie';
                    $valeur .= 'Categorie';
                }

                else if($index_tableau[1] == 'sous_menus') {
                    $id .= 'sous_menus';
                    $valeur .= 'Sous menus';
                }

                else if($index_tableau[1] == 'lien_standard') {
                    $id .= 'lien_standard';
                    $valeur .= 'Lien Standard';
                }

                else if($index_tableau[1] == 'lien') {
                    $id .= 'lien';
                    $valeur .= 'Lien';
                }

                if($id === null)
                    continue;

                $sous_categories[$index] = array(
                    'index_correspondance' => $id,
                    'valeur' => $valeur,
                );
            }

        }
        else if($id_categorie == 8){

            $sous_categories = Champ_libre_liste::select(DB::raw('CONCAT(eden_champslibres.type_element,"_",eden_champslibres.nom_sql) as index_correspondance'),DB::raw('CONCAT(nom_table," : champ ",nom_sql) as valeur'),'eden_champslibres_listes.index_traduction')
                ->join('eden_champslibres','eden_champslibres.id_cl','eden_champslibres_listes.id_cl')
                ->join('eden_tableslibres','eden_tableslibres.type_element','eden_champslibres.type_element')
                ->whereNotNull('eden_champslibres_listes.index_traduction')
                ->get()
                ->keyBy('index_traduction')
                ->toArray();

        }
        else{

            if($id_categorie == 9)
                $liste_formatees = Variables::liste_formatees();

            $traductions_index= modele('traduction_index')
                ->where('categorie',$id_categorie)
                ->get()->pluck('index');

            foreach($traductions_index as $index){

                $decomposition_index = explode('.',$index);

                if(!isset($decomposition_index[1]))
                    continue;

                $index_correspondance = $decomposition_index[1];
                $valeur = str_replace('_',' ',ucfirst($decomposition_index[1]));

                if($id_categorie == 4){
                    if(sizeof($decomposition_index) > 3) {
                        $index_correspondance .= $decomposition_index[2];
                        $valeur .= " : ".str_replace('_',' ',ucfirst($decomposition_index[2]));
                    }
                }


                if($id_categorie == 9)
                    $valeur = $index_correspondance.'. '.(isset($liste_formatees[$index_correspondance]) ? $liste_formatees[$index_correspondance] : '');

                $sous_categories[$index] = array(
                    'index_correspondance' => $index_correspondance,
                    'valeur' => $valeur,
                );
            }
        }

        return $sous_categories;
    }

    /**
     *
     * Permet de récupérer la racine catégorie d'un index
     *
     */
    public function index_categorie($id_categorie,$index){

        $index_categorie = $index;

        if(in_array($id_categorie,array(1,2,5,6,7,8))){

            $tableau_index = explode('.', $index);

            unset($tableau_index[sizeof($tableau_index) - 1]);

            $index_categorie = implode('.', $tableau_index);
        }

        return $index_categorie;
    }

    /**
     *
     * Informations par type element
     *
     */
    public function informations_type_element($type_element = false){

        $informations_type_element = array(
            'tableau_de_bord' => array(
                'categorie' => 11,
                'champs' => array(
                    'nom',
                    'description'
                )
            ),
            'profil' => array(
                'categorie' => 14,
                'champs' => array(
                    'nom',
                )
            ),
            'categorie_rapport' => array(
                'categorie' => 10,
                'champs' => array(
                    'nom',
                )
            ),
            'menus_liens' => array(
                'categorie' => 7,
                'champs' => array(
                    'nom',
                )
            ),
            'menus_categories' => array(
                'categorie' => 7,
                'champs' => array(
                    'nom',
                )
            )
        );

        if($type_element !== false){

            if(isset($informations_type_element[$type_element]))
                return $informations_type_element[$type_element];

            return array();
        }

        return $informations_type_element;
    }

    /**
     *
     * Supprime un index de traduction
     *
     */
    public function supprime_index_traduction($index_traduction,$regeneration_cache = true){

        $traductions_valeurs = modele('traduction_valeur')->where('index','Like',$index_traduction.'.%')->get();

        $index_standard = array();

        foreach($traductions_valeurs as $traduction_valeur){

            if(!isset($index_standard[$traduction_valeur->index]))
                $index_standard[$traduction_valeur->index] = false;

            $management_traduction_valeur = management('traduction_valeur',$traduction_valeur->id);

            if($traduction_valeur->traduction_standard == null)
                $management_traduction_valeur->supprime();
            else {
                $index_standard[$traduction_valeur->index] = true;
                $management_traduction_valeur->enregistre_modele(array('traduction_specifique' => null));
            }
        }

        $traductions_index = modele('traduction_index')->where('index', 'Like', $index_traduction . '.%')->get();

        foreach($traductions_index as $traduction_index){

            if($index_standard[$traduction_index->index] === true)
                continue;

            management('traduction_index',$traduction_index->id)->supprime();
        }

        if($regeneration_cache === true)
            Cache_management::genere_traductions();
    }

    /**
     *
     * Permet de récupérer les données de prod et de préprod et de récupérer les différences
     *
     */
    public function synchronisation_environnement(){

        $environnements = service('variables')->environnements();

        $environnement_actuel = env('APP_ENV');

        if(empty($environnements['url_prod']) || empty($environnements['url_preprod']) || !in_array($environnement_actuel,array('prod','preprod')))
            return array('retour' => false, 'message' => traduction('messages.php.traduction.environnements_non_etablis'));

        $environnement_transfert = $environnement_actuel == 'prod' ? 'preprod' : 'prod';

        $index_traductions_inactifs = array();

        // Récupération des valeurs de l'environnement actuel
        $traductions_specifiques[$environnement_actuel] = modele('traduction_valeur')
            ->avec_inactifs()
            ->select('traduction_valeur.index','traduction_valeur.langue','traduction_valeur.traduction_specifique as valeur','traduction_valeur.inactif','categorie')
            ->join('traduction_index','traduction_index.index','traduction_valeur.index')
            ->where(function($sous_requete){
                $sous_requete->whereNull('traduction_index.inactif')
                    ->orWhere('traduction_index.inactif',0);
            })
            ->whereNotNull('traduction_specifique')
            ->orderBy('categorie')
            ->get()->toArray();

        // Récupération des valeurs de l'environnement de transfert
        $url = $environnements['url_'.$environnement_transfert].'/api/traduction_valeur/list';

        $variables = http_build_query(
            array(
                'select' => array(
                    'traduction_valeur.index',
                    'traduction_valeur.langue',
                    'traduction_valeur.traduction_specifique as valeur',
                    'traduction_valeur.inactif',
                    'categorie',
                ),
                'joins' => array(
                    0 => array(
                        'traduction_index',
                        'traduction_index.index',
                        'traduction_valeur.index'
                    )
                ),
                'where' => array(
                    0 => array(
                        'traduction_specifique',
                        'condition' => 'whereNotNull',
                    ),
                    1 => array(
                        array(
                            'traduction_index.inactif',
                            'condition' => 'whereNull',
                        ),
                        array(
                            'traduction_index.inactif',
                            0,
                            'condition' => 'orWhere',
                        )
                    )
                ),
                'orderBy' => 'categorie',
                'deleted' => 1,
            )
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: ".config('services.eden.cle_api_eden')."\r\n",
                'method'  => 'POST',
                'content' => $variables,
            ),
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        );

        $contexte  = stream_context_create($options);
        $traductions_valeurs = file_get_contents($url, false, $contexte);

        $traductions_valeurs = json_decode($traductions_valeurs,true);

        if($traductions_valeurs['statut'] !== true)
            throw($traductions_valeurs['code_erreur']);

        $traductions = $traductions_valeurs['donnees'];

        $traductions_specifiques[$environnement_transfert] = $traductions;

        // Organisation des données
        foreach($traductions_specifiques as $environnement => $traductions){

            $traductions_organises = array();

            foreach($traductions as $traduction){

                if(!empty($traduction['inactif'])) {
                    $index_traductions_inactifs[$traduction['langue'].'.'.$traduction['index']] = $traduction;
                    continue;
                }

                $traductions_organises[$traduction['langue'].'.'.$traduction['index']] = $traduction;

            }

            $traductions_specifiques[$environnement] = $traductions_organises;
        }

        $traductions_a_traiter = array(
            'ajout' => array(),
            'comparaison' => array(),
        );

        // On récupére les éléments à ajouter non présents dans l'autre environnement
        $traductions_a_traiter['ajout']['prod'] = array_values(array_diff_key($traductions_specifiques['preprod'],$traductions_specifiques['prod'],$index_traductions_inactifs));
        $traductions_a_traiter['ajout']['preprod'] = array_values(array_diff_key($traductions_specifiques['prod'],$traductions_specifiques['preprod'],$index_traductions_inactifs));

        // On récupére les éléments à comparer car leurs valeurs sont différentes
        $cles_a_comparer = array_keys(array_intersect_key($traductions_specifiques['prod'],$traductions_specifiques['preprod']));

        foreach($cles_a_comparer as $cle){

            $valeur_prod = $traductions_specifiques['prod'][$cle]['valeur'];
            $valeur_preprod = $traductions_specifiques['preprod'][$cle]['valeur'];

            if($valeur_prod != $valeur_preprod){

                $langue = $traductions_specifiques['prod'][$cle]['langue'];
                $index = $traductions_specifiques['prod'][$cle]['index'];

                $traductions_a_traiter['comparaison'][$langue][$index] = array(
                    'prod' => $valeur_prod,
                    'preprod' => $valeur_preprod,
                );
            }
        }

        return array('retour' => true, 'traductions_a_traiter' => $traductions_a_traiter);
    }

    /**
     *
     * Traite les choix effectués pour la synchronisation des environnements
     *
     */
    public function validation_synchronisation_environnement($parametres){

        $environnements = service('variables')->environnements();

        $environnement_actuel = env('APP_ENV');

        if(empty($environnements['url_prod']) || empty($environnements['url_preprod']) || !in_array($environnement_actuel,array('prod','preprod')))
            return array('retour' => false, 'message' => traduction('messages.php.traduction.environnements_non_etablis'));

        $environnement_transfert = $environnement_actuel == 'prod' ? 'preprod' : 'prod';

        $traductions_synchronisations = $parametres['traductions_synchronisations'];

        $choix_comparaisons = array();

        if(!empty($parametres['choix_comparaisons']))
            $choix_comparaisons = $parametres['choix_comparaisons'];

        $elements_a_enregistrer = array(
            'prod' => array(),
            'preprod' => array(),
        );

        if(isset($traductions_synchronisations['ajout']['prod']))
            $elements_a_enregistrer['prod'] = $traductions_synchronisations['ajout']['prod'];

        if(isset($traductions_synchronisations['ajout']['preprod']))
            $elements_a_enregistrer['preprod'] = $traductions_synchronisations['ajout']['preprod'];

        if(isset($traductions_synchronisations['comparaison'])) {
            foreach ($traductions_synchronisations['comparaison'] as $langue => $traductions) {

                foreach ($traductions as $index => $valeur) {

                    if (!empty($choix_comparaisons['prod']) && in_array($index, $choix_comparaisons['prod'])) {

                        $elements_a_enregistrer['preprod'][] = array(
                            'langue' => $langue,
                            'index' => $index,
                            'valeur' => $valeur['prod']
                        );

                    } else if (!empty($choix_comparaisons['preprod']) && in_array($index, $choix_comparaisons['preprod'])) {

                        $elements_a_enregistrer['prod'][] = array(
                            'langue' => $langue,
                            'index' => $index,
                            'valeur' => $valeur['preprod']
                        );

                    }
                }

            }
        }

        if(!empty($elements_a_enregistrer[$environnement_transfert])) {

            $url_index = $environnements['url_' . $environnement_transfert] . '/api/ajout_traductions_synchronisation';

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: ".config('services.eden.cle_api_eden')."\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($elements_a_enregistrer[$environnement_transfert])
                ),
                "ssl" => [
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ]
            );

            $contexte = stream_context_create($options);

            try {
                $requete = file_get_contents($url_index, false, $contexte);
            } catch (\Exception $e) {
                return array('retour' => false, 'message' => $e->getMessage());
            }

            $requete = json_decode($requete);

            if($requete->statut !== true)
                throw($requete->code_erreur);

            if ($requete->retour !== true)
                return $requete;

        }

        if(!empty($elements_a_enregistrer[$environnement_actuel])) {

            $retour = $this->ajout_traductions_synchronisation($elements_a_enregistrer[$environnement_actuel]);

            return $retour;

        }

        return array('retour' => true);
    }

    /**
     *
     * Fonction qui enregistre/modifie les traductions lors de la synchronisation des environnements
     *
     */
    public function ajout_traductions_synchronisation($elements){

        $traductions_valeurs = modele('traduction_valeur')
            ->select('id',DB::raw('CONCAT(langue,".",`index`) as index_langue'))
            ->get()->pluck('id','index_langue')->toArray();

        $traductions_index = modele('traduction_index')
            ->get()->pluck('index')->toArray();

        foreach($elements as $element){

            if(empty($element['valeur']))
                $element['valeur'] = '';

            $index_langue = $element['langue'].'.'.$element['index'];

            // On doit modifier les valeurs existantes
            if(isset($traductions_valeurs[$index_langue])){

                management('traduction_valeur',$traductions_valeurs[$index_langue])->enregistre(array(
                    'traduction_specifique' => $element['valeur']
                ));

            }
            // On doit ajouter les valeurs
            else{

                // On doit créer l'index si il n'existe pas avec la catégorie
                if(!isset($traductions_index[$element['index']])){

                    management('traduction_index')->enregistre(array(
                        'categorie' => $element['categorie'],
                        'index' => $element['index'],
                    ));

                }

                management('traduction_valeur')->enregistre(array(
                    'traduction_specifique' => $element['valeur'],
                    'index' => $element['index'],
                    'langue' => $element['langue'],
                ));

            }
        }

        Cache_management::genere_traductions();

        return array('retour' => true);
    }

    public function chargement_valeurs_categorie($categories){

        $this->traductions_valeurs = modele('traduction_valeur')
                    ->select('traduction_valeur.*')
                    ->join('traduction_index','traduction_index.index','traduction_valeur.index')
                    ->whereIn('categorie',$categories)
                    ->where('traduction_valeur.langue','fr')
                    ->get()->keyBy('index');
    }
}
