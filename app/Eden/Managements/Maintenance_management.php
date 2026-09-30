<?php

namespace App\Eden\Managements;

use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Listes_libres;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Colonne;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Formulaire_valeur_par_defaut;
use App\Eden\Models\Formulaire;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;

use App\Eden\Variables;
use App\Eden\Rapports_libres;
use App\Eden\Tables_libres;
use Schema;
use Illuminate\Database\Schema\Blueprint;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Process;
use Artisan;

use App\Eden\Managements\Script_management;
use App\Eden\Exceptions\Eden_exception;


/**
 * Gestion des mises à jour de l'ERP
 */
class Maintenance_management {

    /**
     * Étapes de la mise à jour de la structure, à chaque déploiement, dans l'ordre.
     * Idempotentes : seules les migrations non appliquées sont jouées.
     * Base existante requise : une base vide n'est pas prise en charge (partir d'un dump).
     * Pas de mise_a_jour_composer : les dépendances sont installées au build de l'image.
     */
    public const ETAPES_MIGRATIONS = [
        'lancement_script_avant',
        'generer_tables_champs_libres',
        'maj_vue_sql',
        'maj_rapports_libres',
        'generer_listes_libres',
        'maj_formulaires_libres',
        'maj_sous_formulaires',
        'maj_version_eden',
        'maj_traductions',
        'maj_crons',
        'maj_utilisateurs_easydev',
        'generer_licences',
        'lancement_script_apres',
    ];

    /**
     * Lance toutes les étapes de migration (commande eden:migrate).
     * Le cache est vidé une seule fois, à la fin ; une exception interrompt la suite.
     *
     * @param callable|null $sur_etape appelée avec le nom de l'étape avant son lancement
     */
    public static function lancer_migrations(?callable $sur_etape = null) {

        if(!defined('migration_en_cours'))
            define('migration_en_cours', true);

        ini_set('memory_limit', -1);
        set_time_limit(0);

        foreach(self::ETAPES_MIGRATIONS as $etape) {

            if($sur_etape !== null)
                $sur_etape($etape);

            self::$etape();
        }

        Cache_management::vider_tout();

        // les workers rechargent le paramétrage généré
        Artisan::call('queue:restart');
    }

    /**
     *
     * Mise à jour des champs libres (migrations / seeds)
     *
     */
    public static function maj_rapports_libres($type_element = false,$service_traduction = null) {

        if($service_traduction == null)
            $service_traduction = service('traduction');

        $infos_return = array();

        if(!Schema::hasTable('eden_rapports')) {
            return;
        }

        $rapports = Rapports_libres::rapports_defaut();
        $rapports_standard = Rapports_libres::rapports_standard();
        $rapports_colonnes_et_calculs_standard = Rapports_libres::rapports_colonnes_et_calculs_standard();

        if($type_element !== false) {

            if(isset($rapports[$type_element])) {
                $rapports = $rapports[$type_element];
                $rapports = [$type_element => $rapports];
            } else {
                return [];
            }
        }

        $rapports_bdd = Rapport_libre::get()->keyBy('id_rapport');

        $listes_libres = [];

        foreach($rapports as $id_rapport => $rapport) {

            $id_rapport = strtolower($id_rapport);

            $nouveau_rapport = $rapports_bdd[$id_rapport] ?? null;

            if($nouveau_rapport === null)
                $nouveau_rapport = new Rapport_libre;

            foreach($rapport as $cle => $valeur) {

                // cas spécifique ou le rapport est accompagné d'une liste libre
                if($cle == 'liste_libre') {
                    $nouveau_rapport->type_rapport='liste_libre';

                    if(empty($rapport['type_element']) && !empty($valeur['type_element']))
                        $nouveau_rapport->type_element = $valeur['type_element'];

                    continue;
                }

                if($cle == 'kanban_colonnes' && is_array($valeur)) {

                    $nouveau_rapport->$cle = json_encode($valeur);
                }
                else {

                    $nouveau_rapport->$cle = $valeur;
                }
            }

            $nouveau_rapport->id_rapport = $id_rapport;

            if(empty($nouveau_rapport->index_traduction)) {
                $nouveau_rapport->index_traduction = $service_traduction->calcul_index_traduction(
                    10,
                    array(
                        'rapport',
                        $nouveau_rapport->id_rapport,
                    ),
                    array(
                        'description' => $nouveau_rapport->description,
                        'titre' => $nouveau_rapport->titre,
                    ),
                    in_array($id_rapport,$rapports_standard) ? true : false
                );
            }

            $nouveau_rapport->save();

            $infos_return[] = 'On crée ou modifie le rapport '.$id_rapport;

            // doit on créer une liste libre ?
            if(!isset($rapport['liste_libre']))
                continue;

            // on force la valeur
            $rapport['liste_libre']['id_rapport'] = $id_rapport;

            $listes_libres[] = $rapport['liste_libre'];
        }

        self::cree_infos_listes_libres($listes_libres,$service_traduction,isset($rapports_colonnes_et_calculs_standard[$id_rapport]) ? $rapports_colonnes_et_calculs_standard[$id_rapport] : array());

        return $infos_return;
    }

    /**
     *
     * Mise à jour des champs libres (migrations / seeds)
     *
     */
    public static function maj_listes_libres($type_element = false,$service_traduction = null) {

        if($service_traduction == null)
            $service_traduction = service('traduction');

        $listes_libres = Listes_libres::listes_libres_defaut();
        $listes_colonnes_et_calculs_standard = Listes_libres::listes_colonnes_et_calculs_standard();

        if($type_element !== false) {

            if(isset($listes_libres[$type_element])) {
                $listes_libres = $listes_libres[$type_element];
                $listes_libres = [$type_element => $listes_libres];
            } else {
                return [];
            }
        }

        $infos_return = array();

        foreach($listes_libres as $type_element => &$liste_libre) {
            $type_element = strtolower($type_element);
            $liste_libre['type_element'] = $type_element;
        }

        self::cree_infos_listes_libres($listes_libres,$service_traduction,isset($listes_colonnes_et_calculs_standard[$type_element]) ? $listes_colonnes_et_calculs_standard[$type_element] : array());

        return $infos_return;
    }

    /**
     *
     * Mise à jour des champs libres (migrations / seeds)
     *
     */
    public static function maj_listes_libres_fiches($type_element = false,$service_traduction = null) {

        if($service_traduction == null)
            $service_traduction = service('traduction');

        $listes_libres = Listes_libres::listes_libres_fiches_defaut();
        $listes_fiches_standard = Listes_libres::listes_libres_fiches_standard();
        $listes_fiche_colonnes_et_calculs_standard = Listes_libres::listes_fiche_colonnes_et_calculs_standard();

        $infos_return = array();

        if($type_element !== false) {

            if(isset($listes_libres[$type_element])) {
                $listes_libres = $listes_libres[$type_element];
                $listes_libres = [$type_element => $listes_libres];
            } else {
                return [];
            }
        }

        foreach($listes_libres as $id_rapport => &$liste_libre) {

            if(empty($liste_libre['fiche']) || empty($liste_libre['cle_etrangere'])){
                unset($listes_libres[$id_rapport]);
                continue;
            }

            $id_rapport = strtolower($id_rapport);

            // on force la valeur
            $liste_libre['id_rapport'] = $id_rapport;

            $liste_libre['standard'] = in_array($id_rapport,$listes_fiches_standard);
        }

        self::cree_infos_listes_libres($listes_libres,$service_traduction,isset($listes_fiche_colonnes_et_calculs_standard[$id_rapport]) ? $listes_fiche_colonnes_et_calculs_standard[$id_rapport] : array());

        return $infos_return;
    }

    /**
     *
     * Mise à jour des listes libres export (migrations / seeds)
     *
     */
    public static function maj_listes_libres_export($type_element = false,$service_traduction = null) {

        if($service_traduction == null)
            $service_traduction = service('traduction');

        $listes_libres = Listes_libres::listes_libres_export_defaut();
        $listes_export_standard = Listes_libres::listes_libres_export_standard();
        $listes_export_colonnes_standard = Listes_libres::listes_export_colonnes_standard();

        $infos_return = array();

        if($type_element !== false) {

            if(isset($listes_libres[$type_element])) {
                $listes_libres = $listes_libres[$type_element];
                $listes_libres = [$type_element => $listes_libres];
            } else {
                return [];
            }
        }

        foreach($listes_libres as $id_rapport => &$liste_libre) {
            $id_rapport = strtolower($id_rapport);

            // on force la valeur
            $liste_libre['id_rapport'] = $id_rapport;
            $liste_libre['export'] = 1;
            $liste_libre['standard'] = in_array($id_rapport,$listes_export_standard);
        }

        self::cree_infos_listes_libres($listes_libres,$service_traduction,isset($listes_export_colonnes_standard[$id_rapport]) ? $listes_export_colonnes_standard[$id_rapport] : array());

        return $infos_return;
    }

    public static function cree_infos_listes_libres($listes_libres,$service_traduction,$informations_standard = array()){

        $ids_rapports = array_map(function($liste_libre){
            if(!empty($liste_libre['id_rapport']))
                return $liste_libre['id_rapport'];
        },$listes_libres);

        $listes_libres_rapport = Liste_libre::whereIn('id_rapport', $ids_rapports)->get()->keyBy('id_rapport');
        $rapports = Rapport_libre::whereIn('id_rapport', $ids_rapports)->get()->keyBy('id_rapport');

        $liste_types_elements = array_map(function($liste_libre){
            if(empty($liste_libre['id_rapport']))
                return $liste_libre['type_element'];
        },$listes_libres);

        $listes_libres_elements = Liste_libre::whereIn('type_element', $liste_types_elements)
            ->where(function($r) { $r->whereNull('id_rapport')->orWhere('id_rapport', ''); })->get()->keyBy('type_element');

        $listes_ids = array_merge($listes_libres_rapport->pluck('id')->toArray(),$listes_libres_elements->pluck('id')->toArray());

        $colonnes = Colonne::whereIn('liste_libre_id', $listes_ids)
            ->select(DB::raw("IF(nom = '#' OR nom IS NULL OR nom = '',nom,index_traduction) as identifiant_colonne,listes_libres_colonnes.*"))
            ->get();
        $calculs = Liste_libre_calcul::whereIn('liste_libre_id', $listes_ids)->get()->groupBy('liste_libre_id');
        $filtres = Liste_libre_filtre::whereIn('liste_libre_id', $listes_ids)
            ->select(DB::raw("CONCAT(type_element,'|',nom_sql) as identifiant_filtre,eden_listes_libres_filtres.*"))
            ->get()->groupBy('liste_libre_id');
        $couleurs = Liste_libre_couleur::whereIn('liste_libre_id', $listes_ids)->get();
        $filtres_couleurs = modele('recherche_avancee')
            ->where('type','listes_libres_couleur')
            ->whereIn('id_cible', $couleurs->pluck('id')->toArray())
            ->get()
            ->keyBy('id_cible');

        foreach($couleurs as $couleur){
            if(!empty($filtres_couleurs[$couleur->id]))
                $couleur->filtres = $filtres_couleurs[$couleur->id];
        }

        $couleurs = $couleurs->groupBy('liste_libre_id');

        $filtres_colonnes_calculs = modele('recherche_avancee')
            ->whereLike('type', 'liste_libre_colonne_calcul_%')
            ->get()->groupBy('type');

        foreach($colonnes as $colonne){
            if(!empty($filtres_colonnes_calculs['liste_libre_colonne_calcul_'.$colonne->id])){
                $colonne->filtres_calcul = $filtres_colonnes_calculs['liste_libre_colonne_calcul_'.$colonne->id];
            }
        }

        $colonnes = $colonnes->groupBy('liste_libre_id');

        foreach($listes_libres as $liste_libre){

            $modele_liste_libre = new Liste_libre;

            $elements_enfants = [];

            if(!empty($liste_libre['id_rapport']) && isset($listes_libres_rapport[$liste_libre['id_rapport']]))
                $modele_liste_libre = $listes_libres_rapport[$liste_libre['id_rapport']];
            else if(!empty($liste_libre['type_element']) && empty($liste_libre['id_rapport']) && isset($listes_libres_elements[$liste_libre['type_element']]))
                $modele_liste_libre = $listes_libres_elements[$liste_libre['type_element']];

            if(!empty($modele_liste_libre->id)) {
                $elements_enfants = [
                    'colonnes' => ($colonnes[$modele_liste_libre->id] ?? collect([]))->keyBy('identifiant_colonne'),
                    'calculs' => ($calculs[$modele_liste_libre->id] ?? collect([]))->keyBy('index_traduction'),
                    'filtres' => ($filtres[$modele_liste_libre->id] ?? collect([]))->keyBy('identifiant_filtre'),
                    'couleurs' => ($couleurs[$modele_liste_libre->id] ?? collect([]))->keyBy('couleur'),
                ];

                if(!empty($modele_liste_libre->id_rapport))
                    $elements_enfants['rapport'] = $rapports[$modele_liste_libre->id_rapport] ?? null;
            }

            self::cree_infos_liste_libre($liste_libre,$modele_liste_libre,$informations_standard,$elements_enfants,$service_traduction);
        }
    }

    /**
     *
     * Attention cette méthode est à appeler uniquement depuis ce management !!
     *
     * Il doit être utilisé que dans le cadre des migrations
     *
     */
    public static function cree_infos_liste_libre($liste_libre, $modele_liste_libre,$informations_standard = array(),$elements_enfants = [],$service_traduction = null) {

        if($service_traduction == null)
            $service_traduction = service('traduction');

        $nouvelle_liste_libre = $modele_liste_libre;

        // on met à jour les informations générales de la liste
        $informations_sur_les_listes = array(

            'type_element',
            'id_rapport',
            'limit',
            'orderby',
            'orderby_sens',
            'avec_inactifs',
            'bloquer_tri',
            'fiche',
            'cle_etrangere',
            'cle_primaire',
            'type_element_primaire',
            'export',
			'formulaire_libre',
			'desactiver_filtres',
			'desactiver_recherche',
			'desactiver_actions',
			'desactiver_options',
			'desactiver_export',
			'desactiver_creation',
			'condition_desactiver_creation',
			'desactiver_drag_drop_kanban',
			'desactiver_kanban_sans_valeur',
			'tri_kanban',
            'kanban_afficher_sans_valeur',
            'desactiver_options_individuelle',
            'lignes_par_page',
            'formulaire_modale',
            'afficher_images',
            'modele_email_defaut',
            'desactiver_actions_individuelle',
		);

        foreach($informations_sur_les_listes as $nom_info) {

            if(isset($liste_libre[$nom_info]))
                $nouvelle_liste_libre->$nom_info = $liste_libre[$nom_info];
            elseif(in_array($nom_info,['desactiver_options','desactiver_actions','desactiver_creation']))
                $nouvelle_liste_libre->$nom_info = 0;
        }

        $nouvelle_liste_libre->save();

        if(!empty($nouvelle_liste_libre->fiche)
            || !empty($nouvelle_liste_libre->export)
            || !empty($nouvelle_liste_libre->intranet)
        ){

            $nouveau_rapport = $elements_enfants['rapport'] ?? new Rapport_libre;

            $nouveau_rapport->id_rapport = $nouvelle_liste_libre->id_rapport;

            if(!empty($nouvelle_liste_libre->fiche)) {
                $nouveau_rapport->liste_sur_fiche = 1;

                if(!empty($liste_libre['titre']))
                    $titre_modele = $liste_libre['titre'];
                else
                    $titre_modele = 'Fiche ' . $liste_libre['fiche'].' : '.$liste_libre['type_element'];
            }

            else if(!empty($nouvelle_liste_libre->export)) {
                $nouveau_rapport->export = 1;

                if(!empty($liste_libre['titre']))
                    $titre_modele = $liste_libre['titre'];
                else
                    $titre_modele = 'Export ' . $liste_libre['type_element'];
            }

            else if(!empty($nouvelle_liste_libre->intranet)) {
                $nouveau_rapport->intranet = 1;

                if(!empty($liste_libre['titre']))
                    $titre_modele = $liste_libre['titre'];
                else
                    $titre_modele = 'Intranet ' . $liste_libre['type_element'];
            }

            if(!empty($liste_libre['index_traduction']))
                $nouveau_rapport->index_traduction = $liste_libre['index_traduction'];
            else if (empty($nouveau_rapport->index_traduction)) {

                $nouveau_rapport->index_traduction = $service_traduction->calcul_index_traduction(
                    10,
                    array(
                        'rapport',
                        $nouveau_rapport->id_rapport,
                    ),
                    array(
                        'titre' => $titre_modele,
                    ),
                    isset($liste_libre['standard']) ? $liste_libre['standard'] : false
                );
            }

            if(isset($liste_libre['inactif']))
                $nouveau_rapport->inactif = $liste_libre['inactif'];

            $nouveau_rapport->type_rapport = 'liste_libre';

            $nouveau_rapport->save();
        }

        $calculs_standard = array();

        if(isset($informations_standard['calculs']))
            $calculs_standard = $informations_standard['calculs'];

        // les calculs
        if(isset($liste_libre['calculs'])) {

            foreach($liste_libre['calculs'] as $calcul) {

                if (isset($calcul['index_traduction']) && parametre('S20220729_regenerer_migrations_listes_libres_specifique') == 1)
                    $index_traduction = $calcul['index_traduction'];
                else {

                    $identifiant ='';

                    if(!empty($calcul['nom_sql']))
                        $identifiant .= $calcul['nom_sql'].'_';

                    if(!empty($calcul['type_calcul']))
                        $identifiant .= $calcul['type_calcul'].'_';

                    if(!empty($calcul['unite']))
                        $identifiant .= $calcul['unite'].'_';

                    if(!empty($calcul['split']))
                        $identifiant .= $calcul['split'].'_';

                    if(!empty($calcul['top']))
                        $identifiant .= $calcul['top'];

                    $nom_calcul = strtolower(retraite_caracteres_speciaux($calcul['nom'], '_'));

                    $standard = false;

                    if (isset($calculs_standard[$identifiant])) {
                        $standard = true;
                        $base_index_traduction = explode('.', $calculs_standard[$identifiant]);
                    } else {
                        $base_index_traduction = array(
                            !empty($liste_libre['id_rapport']) ? 'rapport' : 'liste',
                            !empty($liste_libre['id_rapport']) ? $liste_libre['id_rapport'] : $liste_libre['type_element'],
                            'calcul',
                            $nom_calcul
                        );
                    }

                    $index_traduction = $service_traduction->calcul_index_traduction(
                        6,
                        $base_index_traduction,
                        array(
                            'nom' => $calcul['nom']
                        ),
                        $standard
                    );
                }

                if(isset($elements_enfants['calculs'][$index_traduction])){
                    $nouveau_calcul = $elements_enfants['calculs'][$index_traduction];
                    unset($elements_enfants['calculs'][$index_traduction]);
                }
                else
                    $nouveau_calcul = new Liste_libre_calcul;

                $nouveau_calcul->liste_libre_id = $nouvelle_liste_libre->id;
                $nouveau_calcul->nom_sql = $calcul['nom_sql'];
                $nouveau_calcul->type_element = '';
                $nouveau_calcul->type_calcul = $calcul['type_calcul'];
                $nouveau_calcul->nom = $calcul['nom'];
                $nouveau_calcul->split = $calcul['split'] ?? '';
                $nouveau_calcul->unite = $calcul['unite'] ?? '';
                $nouveau_calcul->afficher_somme = $calcul['afficher_somme'] ?? 0;
                $nouveau_calcul->toujours_deploye = $calcul['toujours_deploye'] ?? 0;
                $nouveau_calcul->champ_reference = $calcul['champ_reference'] ?? null;
                $nouveau_calcul->index_traduction = $index_traduction;

                if(isset($calcul['ordre']))
                    $nouveau_calcul->ordre = $calcul['ordre'];

                $nouveau_calcul->save();
            }
        }

        // les filtres
        if(isset($liste_libre['filtres'])) {

            $identifiants_filtres_traites = [];

            foreach($liste_libre['filtres'] as $filtre) {

                if(!isset($filtre['type_element']))
                    $filtre['type_element'] = '';

                $identifiant_filtre = $filtre['type_element'].'|'.$filtre['nom_sql'];

                if(!empty($identifiants_filtres_traites[$identifiant_filtre]))
                    continue;

                $identifiants_filtres_traites[$identifiant_filtre] = true;

                if(isset($elements_enfants['filtres'][$identifiant_filtre])){
                    $nouveau_filtre = $elements_enfants['filtres'][$identifiant_filtre];
                    unset($nouveau_filtre->identifiant_filtre);
                    unset($elements_enfants['filtres'][$identifiant_filtre]);
                }
                else
                    $nouveau_filtre = new Liste_libre_filtre;

                $nouveau_filtre->liste_libre_id = $nouvelle_liste_libre->id;
                $nouveau_filtre->nom_sql = $filtre['nom_sql'];
                $nouveau_filtre->type_element = $filtre['type_element'];

                if(isset($filtre['type_filtre']))
                    $nouveau_filtre->type_filtre = $filtre['type_filtre'];
                else
                    $nouveau_filtre->type_filtre = '';

                $nouveau_filtre->afficher_categories_liste = '';
                if(isset($filtre['afficher_categories_liste']))
                    $nouveau_filtre->afficher_categories_liste = $filtre['afficher_categories_liste'];

                if(isset($filtre['methode_filtre']))
                    $nouveau_filtre->methode_filtre = $filtre['methode_filtre'];
                else
                    $nouveau_filtre->methode_filtre = '';

                if(isset($filtre['emplacement']))
                    $nouveau_filtre->emplacement = $filtre['emplacement'];
                else
                    $nouveau_filtre->emplacement = '';

                if(isset($filtre['ordre']))
                    $nouveau_filtre->ordre = $filtre['ordre'];

                if(isset($filtre['champ_de_liaison']))
                    $nouveau_filtre->champ_de_liaison = $filtre['champ_de_liaison'];

                $nouveau_filtre->save();
            }
        }

        $colonnes_standard = array();

        if(isset($informations_standard['colonnes']))
            $colonnes_standard = $informations_standard['colonnes'];

        // les colonnes
        if(isset($liste_libre['colonnes'])) {

            foreach($liste_libre['colonnes'] as $colonne) {

                $nom_colonne = $colonne['nom'];

                $index_traduction = null;

                if($nom_colonne != '#' && !empty($nom_colonne)) {

                    if (isset($colonne['index_traduction']) && parametre('S20220729_regenerer_migrations_listes_libres_specifique') == 1) {
                        $index_traduction = $colonne['index_traduction'];
                    } else {

                        $type = !empty($colonne['type']) ? $colonne['type'] : "standard";

                        if(!empty($colonne['methode']))
                            $type = 'methode';

                        else if(!empty($colonne['champ']))
                            $type = 'champ';

                        if ($type == 'standard' || $type == 'liaison' || $type == 'concatenation') {
                            $valeur = empty($colonne['valeur']) ? 'id' : $colonne['valeur'];
                            $index = 'type_1_' . $valeur;
                        } else if ($type == 'methode')
                            $index = 'type_2_' . $colonne['methode']. (isset($colonne['arguments']) ? $colonne['arguments'] : '');
                        else if ($type == 'champ')
                            $index = 'type_3_' . $colonne['champ'];

                        $nom_colonne = strtolower(retraite_caracteres_speciaux($nom_colonne, '_'));

                        $standard = false;

                        if (isset($colonnes_standard[$index])) {
                            $standard = true;
                            $base_index_traduction = explode('.', $colonnes_standard[$index]);
                        } else {
                            $base_index_traduction = array(
                                !empty($liste_libre['id_rapport']) ? 'rapport' : 'liste',
                                !empty($liste_libre['id_rapport']) ? $liste_libre['id_rapport'] : $liste_libre['type_element'],
                                'colonne',
                                $nom_colonne
                            );
                        }

                        $index_traduction = $service_traduction->calcul_index_traduction(
                            5,
                            $base_index_traduction,
                            array(
                                'nom' => $colonne['nom']
                            ),
                            $standard
                        );
                    }

                    $identifiant_colonne = $index_traduction;
                }
                else
                    $identifiant_colonne = '#';

                if(isset($elements_enfants['colonnes'][$identifiant_colonne])){
                    $nouvelle_colonne = $elements_enfants['colonnes'][$identifiant_colonne];
                    unset($nouvelle_colonne->identifiant_colonne);
                    unset($elements_enfants['colonnes'][$identifiant_colonne]);
                }
                else
                    $nouvelle_colonne = new Colonne;

                $nouvelle_colonne->liste_libre_id = $nouvelle_liste_libre->id;
                $nouvelle_colonne->nom = $colonne['nom'];

                if(isset($colonne['valeur']))
                    $nouvelle_colonne->valeur = $colonne['valeur'];

                if(isset($colonne['ordre']))
                    $nouvelle_colonne->ordre = $colonne['ordre'];

                if(isset($colonne['methode']))
                    $nouvelle_colonne->methode = $colonne['methode'];

                if(isset($colonne['lien_vers_element']))
                    $nouvelle_colonne->lien_vers_element = $colonne['lien_vers_element'];

                if(!empty($colonne['lien_vers_autre_element']))
                    $nouvelle_colonne->lien_vers_autre_element = $colonne['lien_vers_autre_element'];

                if(isset($colonne['type']))
                    $nouvelle_colonne->type = $colonne['type'];

                if(isset($colonne['champ']))
                    $nouvelle_colonne->champ = $colonne['champ'];

                if(isset($colonne['tri_desactive']))
                    $nouvelle_colonne->tri_desactive = $colonne['tri_desactive'];

                if(isset($colonne['tri_par_defaut']))
                    $nouvelle_colonne->tri_par_defaut = $colonne['tri_par_defaut'];

                if(isset($colonne['sens_tri_par_defaut']))
                    $nouvelle_colonne->sens_tri_par_defaut = $colonne['sens_tri_par_defaut'];

                if(isset($colonne['responsive']))
                    $nouvelle_colonne->responsive = $colonne['responsive'];

                if(isset($colonne['standard']))
                    $nouvelle_colonne->standard = $colonne['standard'];

                if(isset($colonne['retour_a_la_ligne_impossible']))
                    $nouvelle_colonne->retour_a_la_ligne_impossible = $colonne['retour_a_la_ligne_impossible'];

                if(isset($colonne['arguments']))
                    $nouvelle_colonne->arguments = $colonne['arguments'];

                if(isset($colonne['alignement_colonne']))
                    $nouvelle_colonne->alignement_colonne = $colonne['alignement_colonne'];

                if($index_traduction !== null)
                    $nouvelle_colonne->index_traduction = $index_traduction;

                $filtres_calcul = collect([]);

                if(isset($nouvelle_colonne->filtres_calcul)){
                    $filtres_calcul = $nouvelle_colonne->filtres_calcul;
                    unset($nouvelle_colonne->filtres_calcul);
                }

                $nouvelle_colonne->save();

                if(!empty($colonne['filtres_calcul'])){

                    $type_element_calcul = service('lien_champ')->type_element_lien_champ($colonne['source_calcul']);

                    foreach($colonne['filtres_calcul'] as $id_cible => $filtre_calcul){

                        $filtre_existant = $filtres_calcul->where('id_cible', $id_cible)->first();

                        if(!empty($filtre_existant)){
                            $management_recherche_avancee = management('recherche_avancee', $filtre_existant->id, $filtre_existant);

                            $filtres_calcul_appliques_actuels = $management_recherche_avancee->structure(true);

                            unset($filtres_calcul[$filtres_calcul->search($filtre_existant)]);

                            if ($filtres_calcul_appliques_actuels != $filtre_calcul)
                                $management_recherche_avancee->enregistre(['structure' => $filtre_calcul]);
                        }
                        else {
                            management('recherche_avancee')->enregistre([
                                'type_element' => $type_element_calcul ,
                                'type' => 'liste_libre_colonne_calcul_'.$nouvelle_colonne->id,
                                'id_cible' => $id_cible,
                                'structure' => $filtre_calcul
                            ]);
                        }
                        
                    }

                }

                foreach($filtres_calcul as $filtre_calcul){
                    management('recherche_avancee', $filtre_calcul->id, $filtre_calcul)->supprime();
                }
            }
        }

        // les couleurs
        if(isset($liste_libre['couleurs'])) {

            foreach($liste_libre['couleurs'] as $couleur) {

                $filtres_couleurs = null;

                if(isset($elements_enfants['couleurs'][$couleur['couleur']])){
                    $nouvelle_couleur = $elements_enfants['couleurs'][$couleur['couleur']];

                    if(!empty($nouvelle_couleur->filtres)) {
                        $filtres_couleurs = $nouvelle_couleur->filtres;
                        unset($nouvelle_couleur->filtres);
                    }

                    unset($elements_enfants['couleurs'][$couleur['couleur']]);
                }
                else
                    $nouvelle_couleur = new Liste_libre_couleur();

                $nouvelle_couleur->liste_libre_id = $nouvelle_liste_libre->id;
                $nouvelle_couleur->couleur = $couleur['couleur'];

                $nouvelle_couleur->save();

                if(!empty($filtres_couleurs)) {
                    $management_recherche_avancee = management('recherche_avancee', $filtres_couleurs->id, $filtres_couleurs);

                    $filtres_appliques_actuels = $management_recherche_avancee->structure(true);

                    if(empty($couleur['filtres']))
                        $management_recherche_avancee->supprime();
                    else if ($filtres_appliques_actuels != $couleur['filtres'])
                        $management_recherche_avancee->enregistre(['structure' => $couleur['filtres']]);
                }
                else if(!empty($couleur['filtres']))
                    management('recherche_avancee')->enregistre([
                        'type_element' => $nouvelle_liste_libre->type_element,
                        'type' => 'listes_libres_couleur',
                        'id_cible' => $nouvelle_couleur->id,
                        'structure' => $couleur['filtres']
                    ]);
            }
        }

        $recherche_avancee = modele('recherche_avancee')
            ->where('type','filtres_appliques')
            ->where('id_cible',$nouvelle_liste_libre->id)
            ->first();

        if(!empty($liste_libre['filtres_appliques']) && is_array($liste_libre['filtres_appliques'])){

            if(!empty($recherche_avancee)) {
                $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                $filtres_appliques_actuels = $management_recherche_avancee->structure(true);

                if ($filtres_appliques_actuels != $liste_libre['filtres_appliques'])
                    $management_recherche_avancee->enregistre(['structure' => $liste_libre['filtres_appliques']]);
            }
            else
                management('recherche_avancee')->enregistre([
                    'type_element' => $nouvelle_liste_libre->type_element,
                    'type' => 'filtres_appliques',
                    'id_cible' => $nouvelle_liste_libre->id,
                    'structure' => $liste_libre['filtres_appliques']
                ]);
        }
        else if(!empty($recherche_avancee))
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();

        foreach($elements_enfants as $type => $elements){

            if($type == 'rapport')
                continue;

            foreach($elements as $element){

                if($type == 'couleurs' && !empty($element->filtres))
                    management('recherche_avancee',$element->filtres->id,$element->filtres)->supprime();

                $element->delete();
            }
        }

        return $nouvelle_liste_libre;
    }

    /**
     *
     * Mise à jour des tables libres (migrations / seeds)
     *
     */
    public static function maj_tables_libres($type_element = false, $initialisation = false) {

        $tables_libres = Tables_libres::tables_libres();
        $tables_libres_standard = Tables_libres::tables_libres_standard();

        if($type_element !== false) {

            if(isset($tables_libres[$type_element])) {
                $tables_libres = $tables_libres[$type_element];
                $tables_libres = [$type_element => $tables_libres];
            } else {
                return [];
            }
        }

        $infos_return = array();

        $informations_tables = [
            'tables' => collect(DB::select('SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = "'.env('DB_DATABASE').'"'))->pluck('table_name')->toArray(),
            'colonnes_par_type_element' => collect(DB::select('SELECT table_name,column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "'.env('DB_DATABASE').'"'))
                ->groupBy('table_name')->map(fn($champs) => $champs->pluck('column_name'))->toArray(),
            'indexes_par_table' => collect(DB::select('SELECT table_name,index_name FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = "'.env('DB_DATABASE').'"'))
                ->groupBy('table_name')->map(fn($indexes) => $indexes->pluck('index_name'))->toArray(),
        ];

        if($type_element !== false)
            $tables_libres_existantes = Table_libre::where('type_element',$type_element)->get()->keyBy('type_element');
        else
            $tables_libres_existantes = Table_libre::get()->keyBy('type_element');

        // étape 1, on crée les tables libres
        foreach($tables_libres as $table_libre_modele) {

            try {

                $la_table_libre = $tables_libres_existantes[$table_libre_modele['type_element']] ?? null;

                if($la_table_libre !== null) {

                    $changement_valeur = false;

                    // on vérifie que la table existe réellement sur la bdd
                    if($la_table_libre->vue_sql != 1)
                        Table_libre_management::cree_table($table_libre_modele['type_element'], $initialisation, $informations_tables);

                    $la_table_libre->desactiver_gestion_traduction = true;

                    // Si le champ existe dans le fichier de migration spécifique et est égale à ce qu'il y a en base, on skip
                    // On check un à un les options du champ_libre dans les migrations
                    foreach ($table_libre_modele as $nom_option => $valeur) {

                        // La bdd est à jour par rapport aux migrations
                        if (!array_key_exists($nom_option,$la_table_libre->toArray()) || $valeur == $la_table_libre->$nom_option)
                            continue;
                        else {
                            $infos_return[] = 'On change '.$table_libre_modele['type_element'].'.'.$nom_option.' en "'.$valeur.'"';
                            $la_table_libre->$nom_option = $valeur;
                            $changement_valeur = true;
                        }
                    }

                    if(empty($la_table_libre->index_traduction) && !in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue'))&& !$initialisation) {

                        $la_table_libre->index_traduction = service('traduction')->calcul_index_traduction(
                            2,
                            array(
                                    'tables_libres',
                                    $table_libre_modele['type_element'],
                                ),
                                array(
                                'nom_table' => $table_libre_modele['nom_table'],
                                'element' => $table_libre_modele['element'],
                                'element_pluriel' => $table_libre_modele['element_pluriel']
                            ),
                            in_array($table_libre_modele['type_element'],$tables_libres_standard)
                        );

                        $changement_valeur = true;
                    }

                    if($changement_valeur)
                        $la_table_libre->save();

                    continue;
                }

                $index_traduction = null;

                if(!in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue')) && !$initialisation) {
                    $index_traduction = service('traduction')->calcul_index_traduction(
                        2,
                        array(
                            'tables_libres',
                            $table_libre_modele['type_element'],
                        ),
                        array(
                            'nom_table' => $table_libre_modele['nom_table'],
                            'element' => $table_libre_modele['element'],
                            'element_pluriel' => $table_libre_modele['element_pluriel']
                        ),
                        in_array($table_libre_modele['type_element'], $tables_libres_standard)
                    );
                }

                // on crée la table sur la BDD
                $retour = Table_libre_management::enregistre($table_libre_modele['element'], array(

                    'nom_table' => $table_libre_modele['nom_table'],
                    'element' => $table_libre_modele['element'],
                    'element_pluriel' => $table_libre_modele['element_pluriel'],
                    'creation_rapide' => isset($table_libre_modele['creation_rapide']) ? $table_libre_modele['creation_rapide'] : null,
                    'fiche' => isset($table_libre_modele['fiche']) ? $table_libre_modele['fiche'] : null,
                    'disponible_recherche_rapide' => isset($table_libre_modele['disponible_recherche_rapide']) ? $table_libre_modele['disponible_recherche_rapide'] : null,
                    'index_traduction' => $index_traduction,
                    'table_systeme' => isset($table_libre_modele['table_systeme']) ? $table_libre_modele['table_systeme'] : null,
                    'vue_sql' => $table_libre_modele['vue_sql'] ?? 0,
                    'non_logue' => $table_libre_modele['non_logue'] ?? 0,
                ), $table_libre_modele['type_element'], $initialisation);

            } catch (\Exception $exception) {
                $message = "Erreur lors de la création/mise à jour de la table '" . json_encode($table_libre_modele) . "' : "  . $exception->getMessage();
                log_eden($message);
                throw new \Exception($message, $exception->getCode(), $exception);
            }
        }

        return $infos_return;
    }

    /**
     *
     * Mise à jour des champs libres (migrations / seeds)
     *
     */
    public static function maj_champs_libres($type_element = false, $initialisation = false) {

        $champs_libres = \App\Eden\Champs_libres::champs_libres_defaut();

        $infos_return = array();

        $cles_etrangeres = (array) DB::select('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = "'.env('DB_DATABASE').'"');
        $cles_etrangeres = array_column($cles_etrangeres,'CONSTRAINT_NAME');

        $colonnes_par_type_element = collect(DB::select('SELECT table_name,column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "'.env('DB_DATABASE').'"'))
            ->groupBy('table_name')->map(fn($champs) => $champs->pluck('column_name'))->toArray();

        $index_existants_par_type_element = collect(DB::select('SELECT table_name,index_name FROM information_schema.statistics WHERE table_schema="'.env('DB_DATABASE').'"'))
            ->groupBy('table_name')->map(fn($champs) => $champs->pluck('index_name'))->toArray();

        $recherches_avancees = modele('recherche_avancee')
            ->where('type','like','champs_libres.%')
            ->get()->groupBy('type');

        $erreurs_cle_etrangere = array();

        if($type_element !== false) {

            if(isset($champs_libres[$type_element])) {
                $champs_libres = $champs_libres[$type_element];
                $champs_libres = [$type_element => $champs_libres];
            } else {
                $champs_libres = [];
            }
        }

        if($type_element !== false){
            $champs_libres_existants = Champ_libre::where('type_element',$type_element)->get()->groupBy('type_element')->map(fn($champs) => $champs->keyBy('nom_sql'));
            $tables_libres_existantes = Table_libre::where('type_element',$type_element)->get()->keyBy('type_element');
        }
        else {
            $champs_libres_existants = Champ_libre::get()->groupBy('type_element')->map(fn($champs) => $champs->keyBy('nom_sql'));
            $tables_libres_existantes = Table_libre::get()->keyBy('type_element');
        }

        foreach($champs_libres as $type_element => $champs_libres_du_type_element) {

            $table_libre = $tables_libres_existantes[$type_element] ?? null;

            if(empty($table_libre))
                continue;

            $champs_libres_type_element = $champs_libres_existants[$type_element] ?? [];

            $champs_libres_bdd = [];

            // on crée le champ libre si nécessaire
            foreach($champs_libres_du_type_element as $nom_sql => $champ_libre_modele) {

                if(in_array($nom_sql,array('modifie_le','cree_le','cree_par','modifie_par','cle_externe')))
                    continue;

                $champ_standard = !isset($champ_libre_modele['standard']) || !empty($champ_libre_modele['standard']) ? true : false;

                $le_champ_libre = $champs_libres_type_element[$nom_sql] ?? null;

                if($le_champ_libre !== null){

                    // Si le champ existe dans le fichier de migration spécifique et est égale à ce qu'il y a en base, on skip
                    $le_champ_libre->desactiver_gestion_traduction = true;

                    $changement_valeur = false;

                    $filtres_type_element_ajax = [];

                    // On check un à un les options du champ_libre dans les migrations
                    foreach ($champ_libre_modele as $nom_option => $valeur) {

                        if($nom_option == 'index_traduction')
                            continue;

                        if($nom_option == 'filtres'){
                            $filtres_type_element_ajax = $valeur;
                            continue;
                        }

                        // La bdd est à jour par rapport aux migrations
                        if($valeur == $le_champ_libre->$nom_option)
                            continue;
                        else {

                            $infos_return[] = 'On change '.$type_element.'.'.$nom_sql.'.'.$nom_option.' en "'.$valeur.'"';

                            if($le_champ_libre->type == -2)
                                $valeur = str_replace("\n", "\\n", $valeur);

                            $le_champ_libre->$nom_option = $valeur;

                            $changement_valeur = true;
                        }
                    }

                    $filtres_existant = ($recherches_avancees['champs_libres.'.$type_element.'.'.$nom_sql] ?? collect([]))->keyBy('id_cible');

                    if(!empty($filtres_type_element_ajax) || $filtres_existant->isNotEmpty()) {

                        $types_elements_filtres = array_unique(array_merge(
                            array_keys($filtres_existant->toArray()),
                            array_keys($filtres_type_element_ajax)
                        ));

                        foreach($types_elements_filtres as $type_element_filtre) {

                            if(!empty($filtres_existant[$type_element_filtre])) {

                                $management_recherche_avancee = management('recherche_avancee', $filtres_existant[$type_element_filtre], $filtres_existant[$type_element_filtre]);

                                $filtres_appliques_actuels = $management_recherche_avancee->structure(true);

                                if(empty($filtres_type_element_ajax[$type_element_filtre]))
                                    $management_recherche_avancee->supprime();
                                else if ($filtres_appliques_actuels != $filtres_type_element_ajax[$type_element_filtre])
                                    $management_recherche_avancee->enregistre(['structure' => $filtres_type_element_ajax[$type_element_filtre]]);
                            }
                            else if(!empty($filtres_type_element_ajax[$type_element_filtre]))
                                management('recherche_avancee')->enregistre([
                                    'type_element' => $type_element_filtre,
                                    'type' => 'champs_libres.'.$type_element.'.'.$nom_sql,
                                    'id_cible' => $type_element_filtre,
                                    'structure' => $filtres_type_element_ajax[$type_element_filtre]
                                ]);
                        }
                    }

                    // on vérifie si la table pivot existe
                    if($champ_libre_modele['type'] == 10 && $table_libre->vue_sql != 1) {

                        $nom_de_la_table = $type_element . '_' . $nom_sql;

                        if(empty($le_champ_libre->table_pivot)) {

                            $le_champ_libre->table_pivot = $nom_de_la_table;

                            //on vériie que la table n'existe pas avant de procéder à la création
                            if (!\Schema::hasTable($nom_de_la_table)) {

                                \Schema::create($nom_de_la_table, function ($table) {
                                    $table->increments('id')->unsigned();
                                    $table->integer('cle_locale')->unsigned();
                                });

                                Champ_libre_management::cree_colonne_sur_table($type_element, 'valeur', $le_champ_libre->type_reference, $le_champ_libre, true);
                            }

                            $changement_valeur = true;
                        }

                        $nom_contrainte = 'CE_table_pivot_' . $le_champ_libre->id_cl;

                        if(!in_array($nom_contrainte,$cles_etrangeres)) {

                            try {
                                DB::select('ALTER TABLE `' . $nom_de_la_table . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`cle_locale`) REFERENCES `' . $type_element . '`(`id`);');
                            } catch (\Exception $e) {
                                $erreurs_cle_etrangere[] = 'Erreur lors de la création de la clé étrangére sur la table pivot '.$nom_de_la_table;
                            }
                        }

                        if($le_champ_libre->type_reference == 42 && !empty($le_champ_libre->type_element_ajax)){

                            $nom_contrainte = 'CE_valeur_table_pivot_' . $le_champ_libre->id_cl;

                            if(!in_array($nom_contrainte,$cles_etrangeres) && $le_champ_libre->vue_sql != 1) {

                                try {
                                    DB::select('ALTER TABLE `' . $nom_de_la_table . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`valeur`) REFERENCES `' . $le_champ_libre->type_element_ajax . '`(`id`);');
                                    $infos_return[] = "on crée la clé étrangére sur valeur de la table " . $nom_de_la_table;
                                } catch (\Exception $e) {
                                    $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur valeur de la table ' . $nom_de_la_table . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $nom_de_la_table]) . '"> Liste des erreurs de clés étrangères.<a>';
                                }
                            }
                        }
                    }

                    $nouvel_index_traduction = 'champs_libres.'.$type_element.'.'.$nom_sql;

                    if($le_champ_libre->index_traduction != $nouvel_index_traduction && !$initialisation && !in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue'))) {

                        $le_champ_libre->index_traduction = service('traduction')->calcul_index_traduction(
                            1,
                            array(
                                'champs_libres',
                                $type_element,
                                $nom_sql,
                            ),
                            array(
                                'nom' => $champ_libre_modele['nom']
                            ),
                            $champ_standard
                        );

                        $changement_valeur = true;
                    }

                    if($changement_valeur)
                        $le_champ_libre->save();

                    $champs_libres_bdd[$le_champ_libre->nom_sql] = $le_champ_libre;

                    continue;
                }

                if (isset($champ_libre_modele['taille_libelle']))
                    unset($champ_libre_modele['taille_libelle']);

                if (isset($champ_libre_modele['taille_champ']))
                    unset($champ_libre_modele['taille_champ']);

                if (isset($champ_libre_modele['taille_avant']))
                    unset($champ_libre_modele['taille_avant']);

                if (isset($champ_libre_modele['taille_apres']))
                    unset($champ_libre_modele['taille_apres']);

                $champ_libre = new Champ_libre;

                foreach($champ_libre_modele as $cle => $valeur){

                    if($cle == 'filtres'){
                        foreach($valeur as $type_element_filtre => $structure){
                            management('recherche_avancee')->enregistre([
                                'type_element' => $type_element_filtre,
                                'type' => 'champs_libres.'.$type_element.'.'.$nom_sql,
                                'id_cible' => $type_element_filtre,
                                'structure' => $structure
                            ]);
                        }
                        continue;
                    }

                    $champ_libre->$cle = $valeur;

                }

                $champ_libre->type_element = $type_element;
                $champ_libre->nom_sql = $nom_sql;

                if(!in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue')) && !$initialisation) {
                    $champ_libre->index_traduction = service('traduction')->calcul_index_traduction(
                        1,
                        array(
                            'champs_libres',
                            $type_element,
                            $nom_sql,
                        ),
                        array(
                            'nom' => $champ_libre_modele['nom']
                        ),
                        $champ_standard
                    );
                }

                $champ_libre->save();

                // on crée une table pivot
                if($champ_libre->type == 10 && $table_libre->vue_sql != 1) {

                    $nom_de_la_table = $type_element.'_'.$nom_sql;

                    if(empty($champ_libre->table_pivot)) {
                        $champ_libre->table_pivot = $nom_de_la_table;

                        $champ_libre->save();
                    }

                    //on vériie que la table n'existe pas avant de procéder à la création
                    if(!\Schema::hasTable($nom_de_la_table)) {

                        \Schema::create($nom_de_la_table, function($table)
                        {
                            $table->increments('id')->unsigned();
                            $table->integer('cle_locale')->unsigned();
                        });


                        Champ_libre_management::cree_colonne_sur_table($type_element, 'valeur', $champ_libre->type_reference, $champ_libre, true);
                    }

                    $nom_contrainte = 'CE_table_pivot_' . $champ_libre->id_cl;

                    if(!in_array($nom_contrainte,$cles_etrangeres)) {

                        try {
                            DB::select('ALTER TABLE `' . $nom_de_la_table . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`cle_locale`) REFERENCES `' . $type_element . '`(`id`);');
                        } catch (\Exception $e) {
                            $erreurs_cle_etrangere[] = 'Erreur lors de la création de la clé étrangére sur la table pivot '.$nom_de_la_table;
                        }
                    }

                    if($champ_libre->type_reference == 42 && !empty($champ_libre->type_element_ajax) && !in_array($champ_libre->type_element_ajax, Variables::$documents_gescom_lignes)){

                        $nom_contrainte = 'CE_valeur_table_pivot_' . $champ_libre->id_cl;

                        if(!in_array($nom_contrainte,$cles_etrangeres)) {

                            try {
                                DB::select('ALTER TABLE `' . $nom_de_la_table . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`valeur`) REFERENCES `' . $champ_libre->type_element_ajax . '`(`id`);');
                                $infos_return[] = "on crée la clé étrangére sur valeur de la table " . $nom_de_la_table;
                            } catch (\Exception $e) {
                                $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur valeur de la table ' . $nom_de_la_table . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $nom_de_la_table]) . '"> Liste des erreurs de clés étrangères.<a>';
                            }
                        }
                    }
                }

                $champs_libres_bdd[$champ_libre->nom_sql] = $champ_libre;

                $infos_return[] = 'On crée le champ libre '.$type_element.'.'.$nom_sql;
            }

            if($table_libre->vue_sql != 1) {

                // on crée les colonnes si nécessaire et on gére les vues
                foreach ($champs_libres_du_type_element as $nom_sql => $champ_libre_modele) {

                    // on crée le champ sur la BDD
                    if (empty($colonnes_par_type_element[$type_element]) || !in_array($nom_sql, $colonnes_par_type_element[$type_element])) {

                        $le_champ_libre = $champs_libres_bdd[$nom_sql] ?? null;

                        Champ_libre_management::cree_colonne_sur_table($type_element, $nom_sql, $champ_libre_modele['type'], $le_champ_libre);

                        $infos_return[] = 'On crée la colonne ' . $nom_sql . ' sur la table ' . $type_element;
                    }

                    //On crée les index
                    if(!empty($champ_libre_modele['index']) && $champ_libre_modele['index'] == 1){

                        if(empty($index_existants_par_type_element[$type_element]) || !in_array($nom_sql,$index_existants_par_type_element[$type_element]))
							try {
								DB::select('CREATE INDEX `' . $nom_sql . '` ON `' . $type_element . '` (`' . $nom_sql . '`);');
								$infos_return[] = "on crée l'index" . $nom_sql . " de la table " . $type_element;
							}
							catch(\Exception $e){
                                $erreurs_cle_etrangere[] = "Erreur lors de la création de l'index " . $nom_sql . " de la table " . $type_element;
							}
                    }

                    //Pour les champs du type 42 on crée les foreign key
                    if(isset($champs_libres_bdd[$nom_sql]) && $champ_libre_modele['type'] == 42 && !empty($champ_libre_modele['type_element_ajax'])){

                        $nom_contrainte = 'CE_champ_libre_' . $champs_libres_bdd[$nom_sql]['id_cl'];
                        $table_libre_cible = $tables_libres_existantes[$champ_libre_modele['type_element_ajax']] ?? null; 

                        if(!in_array($nom_contrainte,$cles_etrangeres) && isset($table_libre_cible) && $table_libre_cible->vue_sql != 1) {

                            try {
                                DB::select('ALTER TABLE `' . $type_element . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`' . $nom_sql . '`) REFERENCES `' . $champ_libre_modele['type_element_ajax'] . '`(`id`);');
                                $infos_return[] = "on crée la clé étrangére sur " . $nom_sql . " de la table " . $type_element;
                            } catch (\Exception $e) {
                                $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur ' . $nom_sql . ' de la table ' . $type_element . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $type_element]) . '"> Liste des erreurs de clés étrangères.<a>';
                            }
                        }
                    }
                }

                // On crée les champs par défaut
                $champs = [
                    'modifie_le'  => [
                        'nom' => 'Modifié le',
                        'nom_sql' => 'modifie_le',
                        'type' => 5,
                    ],
                    'cree_le' 	  => [
                        'nom' => 'Créé le',
                        'nom_sql' => 'cree_le',
                        'type' => 5,
                    ],
                    'cree_par' 	  => [
                        'nom' => 'Créé par',
                        'nom_sql' => 'cree_par',
                        'type' => 42,
                        'type_element_ajax' => 'utilisateur',
                    ],
                    'modifie_par' => [

                        'nom' => 'Modifié par',
                        'nom_sql' => 'modifie_par',
                        'type' => 42,
                        'type_element_ajax' => 'utilisateur',
                    ],
                    'cle_externe' => [
                        'nom' => 'Clé Externe',
                        'nom_sql' => 'cle_externe',
                        'type' => 0,
                    ],
                ];

                foreach($champs as $nom_sql => $donnees) {
                    if(!isset($champs_libres_type_element[$nom_sql]))
                        Champ_libre_management::enregistre($type_element, $donnees, $initialisation);
                }
            }
        }

        self::mise_en_place_cle_etrangere($cles_etrangeres,$erreurs_cle_etrangere);

        parametre('erreurs_cle_etrangere',json_encode($erreurs_cle_etrangere));

        if(parametre('S20220802_script_mise_en_place_traductions') == 1) {
            Maintenance_management::cree_index_traduction_champs_par_defaut();

            //On gére les traductions sur les éléments plus présents dans les migrations notamment les champs des vue sql
            $champs_libres_non_traduit = Champ_libre::whereNull('index_traduction')->get();

            foreach($champs_libres_non_traduit as $champ_non_traduit) {
                if (!in_array($champ_non_traduit->type_element, array('traduction_valeur', 'traduction_index', 'traduction_langue'))) {

                    $champ_non_traduit->index_traduction = service('traduction')->calcul_index_traduction(
                        1,
                        array(
                            'champs_libres',
                            $champ_non_traduit->type_element,
                            $champ_non_traduit->nom_sql,
                        ),
                        array(
                            'nom' => $champ_non_traduit->nom
                        )
                    );

                    $champ_non_traduit->save();
                }
            }
        }

        return $infos_return;
    }

    /**
     *
     * Mise à jour des tables et des colonnes (migrations)
     *
     */
    public static function maj_tables($type_element = false) {

        $tables = [];
        $infos_return = array();
        $infos_return['table'] = array();
        $infos_return['colonne'] = array();

        // on vient ajouter les tables standards
        $repertoire = scandir(app_path('Eden/Migrations/Tables'));

        foreach($repertoire as $fichier) {

            if($fichier == '.' || $fichier == '..')
                continue;

            $contenu_tmp = require(app_path('Eden/Migrations/Tables/'.$fichier));

            $index_tableau = str_replace('.php', '', $fichier);

            $tables[$index_tableau] = $contenu_tmp;
        }

        // on vient ajouter les tables spécifiques
        if(is_dir(app_path('Migrations/Tables'))) {

            $repertoire = scandir(app_path('Migrations/Tables'));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Tables/'.$fichier));

                $index_tableau = str_replace('.php', '', $fichier);

                if(isset($tables[$index_tableau]))
                    $tables[$index_tableau] = array_merge($tables[$index_tableau], $contenu_tmp);
                else
                    $tables[$index_tableau] = $contenu_tmp;


            }
        }

        if($type_element !== false) {

            if(isset($tables[$type_element])) {
                $tables = $tables[$type_element];
                $tables = [$type_element => $tables];
            } else {
                $tables = [];
            }
        }

        foreach ($tables as $unetable => $colonnes){

            if(!Schema::hasTable($unetable)){

                $infos_return['table'][] = 'On crée la table '.$unetable;

                // on va chercher la clée primaire
                $cle_primaire = false;

                foreach($colonnes as $colonne => $infos_colonne) {

                    if(isset($infos_colonne['cle_primaire']) && $infos_colonne['cle_primaire'] === true) {


                        Schema::create($unetable, function (Blueprint $table) use ($colonne)  {

                            $table->increments($colonne)->unsigned();

                            $table->engine = 'InnoDB';
                            $table->charset = 'utf8';
                            $table->collation = 'utf8_unicode_ci';
                        });

                        $cle_primaire = true;

                        break;
                    }
                }

                if($cle_primaire === false)
                    throw new \App\Eden\Exceptions\Eden_exception("La table $unetable n'a pas pu être créée, car aucune clé primaire n'a été trouvée");

            }

            Schema::table($unetable, function (Blueprint $table) use ($colonnes,$unetable)  {

                foreach($colonnes as $unecolonne => $parametre){

                    if(Schema::hasColumn($unetable,$unecolonne)){

                        if(isset($parametre['index']) && $parametre['index'] == true){

                            $indexes = Schema::getIndexes($unetable);

                            foreach ($indexes as $index) {
                                if (in_array($unecolonne, $index['columns'])) {
                                    continue 2;
                                }
                            }

                            $table->index($unecolonne,$unecolonne);
                        }

                        continue;
                    }

                    $infos_return['colonne'][] = 'On crée la colonne '.$unecolonne.' sur la table '. $unetable;

					$type = $parametre['type'];

                    if(isset($parametre['cle_primaire']) && $parametre['cle_primaire'] === true){

                        $table->increments($unecolonne);

                    }

                    else {

                        if($type == 'string' && !isset($parametre['taille']))
                            $parametre['taille'] = 255;

                        if($type == 'double' && !isset($parametre['taille']))
                            $parametre['taille'] = 17.6;


						$addColumnParameters = [];
						if(!isset($parametre["null"]) || isset($parametre["null"]) && $parametre["null"] == true)
							$addColumnParameters["nullable"] = true;

						if(isset($parametre['taille']) && $parametre['taille'] > 0)
							$addColumnParameters["length"] = $parametre['taille'];

						if(isset($parametre['unsigned']) && $parametre['unsigned'] == true)
							$addColumnParameters["unsigned"] = true;

						$table->addColumn($type, $unecolonne, $addColumnParameters);

                        if(isset($parametre['index']) && $parametre['index'] == true)
                            $table->index($unecolonne,$unecolonne);

                    }
                }
            });

        }

        return $infos_return;

    }

    /**
     *
     * Mise à jour des utilisateurs EasyDev
     *
     */
    public static function maj_utilisateurs_easydev($utilisateurs_modele = null) {
        $infos_return = array();

        if(env('BASE_TRADUCTION') === true)
            return self::maj_utilisateurs_easydev_projets();

        if($utilisateurs_modele == null) {

            $url_index = env('EDEN_MODEL_API_URL').'api/utilisateur/list';

            $variables = http_build_query(
                array(
                    'deleted' => 1,
                )
            );

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                    'method' => 'POST',
                    'content' => $variables,
                )
            );

            $contexte = stream_context_create($options);
            $requete = file_get_contents($url_index, false, $contexte);

            $requete = json_decode($requete, true);

            if ($requete['statut'] !== true)
                throw($requete['code_erreur']);

            $utilisateurs_modele = $requete['donnees'];
        }

        $utilisateurs_modele_par_id = collect($utilisateurs_modele)->keyBy('id');

        $utilisateurs_actuels = modele('utilisateur')->avec_inactifs()->get();

        $colonnes_maj = array(
            'id' => 'cle_externe',
            'email' => 'email',
            'nom' => 'nom',
            'prenom' => 'prenom',
            'mot_de_passe' => 'mot_de_passe',
            'inactif' => 'inactif',
            'avatar' => 'avatar',
            'super_admin' => 'super_admin',
            'langue' => 'langue',
            'droit_usurpation' => 'droit_usurpation',
            'type_utilisateur' => 'type_utilisateur',
            'autorise_a_se_connecter' => 'autorise_a_se_connecter',
            'service' => 'service'
        );

        $options_avatar = stream_context_create(array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: ZQXGIPSSENMFGSTXKR23Z4H3KVOBOZMM\r\n",
            ),
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ));

        foreach($utilisateurs_actuels as $utilisateur_actuel){

            $utilisateur_modele = null;

            if(isset($utilisateurs_modele_par_id[$utilisateur_actuel->cle_externe]))
                $utilisateur_modele = $utilisateurs_modele_par_id[$utilisateur_actuel->cle_externe];

            //L'utilisateur existe, on vient mettre à jour ses infos
            if($utilisateur_modele !== null){

                foreach($colonnes_maj as $colonne_maj => $colonne_a_maj){

                    if(!isset($utilisateur_modele[$colonne_maj]))
                        $utilisateur_modele[$colonne_maj] = null;

                    $utilisateur_actuel->{$colonne_a_maj} = $utilisateur_modele[$colonne_maj];

                    if($colonne_a_maj == 'avatar') {
                        try {
                            \Storage::put("public\\" . $utilisateur_actuel->{$colonne_a_maj}, file_get_contents(env('EDEN_MODEL_API_URL').'storage/' . $utilisateur_actuel->{$colonne_a_maj}, false, $options_avatar));
                        }
                        catch(\Exception $e){
                            $infos_return[] = 'Erreur affectation avatar '.$utilisateur_actuel->email;
                        }
                    }
                }

                $utilisateur_actuel->save();

                unset($utilisateurs_modele_par_id[$utilisateur_modele['id']]);
            }
        }

        //On crée les utilisateurs restants
        foreach($utilisateurs_modele_par_id as $utilisateur_a_creer){

            $nouveau_utilisateur = modele('utilisateur');

            foreach($colonnes_maj as $colonne_maj => $colonne_a_maj){

                if(!isset($utilisateur_a_creer[$colonne_maj]))
                    $utilisateur_a_creer[$colonne_maj] = null;

                $nouveau_utilisateur->{$colonne_a_maj} = $utilisateur_a_creer[$colonne_maj];

                if($colonne_a_maj == 'avatar') {

                    try {
                        \Storage::put("public\\" . $nouveau_utilisateur->{$colonne_a_maj}, file_get_contents(env('EDEN_MODEL_API_URL').'storage/' . $nouveau_utilisateur->{$colonne_a_maj}));
                    }
                    catch(\Exception $e){
                        $infos_return[] = 'Erreur affectation avatar '.$utilisateur_a_creer['email'];
                    }
                }
            }

            $nouveau_utilisateur->save();
        }

        return $infos_return;
    }

    /**
     *
     * Mise à jour des champs de formulaires libres (migrations / seeds)
     *
     */
    public static function maj_formulaires_libres($type_element = false) {

        $infos_return = array();

        // On Regarde si il faut créer des formulaires

        $formulaires = array();
        $formulaires_standard = array();

        // on vient ajouter les formulaires standards
        $repertoire = scandir(app_path('Eden/Migrations/Formulaires_libres'));

        foreach($repertoire as $fichier) {

            if($fichier == '.' || $fichier == '..')
                continue;

            $contenu_tmp = require(app_path('Eden/Migrations/Formulaires_libres/'.$fichier));

            $index_tableau = str_replace('.php', '', $fichier);

            $formulaires[$index_tableau] = $contenu_tmp;
            $formulaires_standard[$index_tableau] = $contenu_tmp;
        }

        // on vient ajouter les formulaires spécifiques
        if(is_dir(app_path('Migrations/Formulaires_libres'))) {

            $repertoire = scandir(app_path('Migrations/Formulaires_libres'));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Formulaires_libres/'.$fichier));

                $index_tableau = str_replace('.php', '', $fichier);

                if(isset($formulaires[$index_tableau]))
                    $formulaires[$index_tableau] = array_merge($formulaires[$index_tableau], $contenu_tmp);
                else
                    $formulaires[$index_tableau] = $contenu_tmp;


            }
        }

        if($type_element !== false) {

            if(isset($formulaires[$type_element])) {

                $formulaires = $formulaires[$type_element];
                $formulaires = [$type_element => $formulaires];
            }
            else {

                return;
            }
        }

        $les_formulaires = Formulaire::get()->keyBy('nom_formulaire');

        // on supprime les champs
        Formulaires_champs::whereIn('nom_formulaire',array_keys($formulaires))->delete();
        // on supprime les valeurs par défaut
        Formulaire_valeur_par_defaut::whereIn('nom_formulaire', array_keys($formulaires))->delete();

        $service_traduction = service('traduction');

        $service_traduction->chargement_valeurs_categorie([12]);

        foreach ($formulaires as $nom_formulaire => $formulaire){

            $le_formulaire = $les_formulaires[$nom_formulaire] ?? null;

            if ($le_formulaire !== null) {

                // Si le formulaire existe dans le fichier de migration spécifique et est égale à ce qu'il y a en base, on skip
                if (isset($formulaire['type_element'])) {

                    if ($formulaire['type_element'] != $le_formulaire->type_element){

                        $infos_return[] = 'On change '.$le_formulaire['nom_formulaire'].'.type_element en "'.$formulaire['type_element'].'"';
                        $le_formulaire->type_element = $formulaire['type_element'];
                    }
                }

                if (isset($formulaire['titre_formulaire'])) {

                    if ($formulaire['titre_formulaire'] != $le_formulaire->titre_formulaire){

                        $infos_return[] = 'On change '.$le_formulaire['nom_formulaire'].'.titre_formulaire en "'.$formulaire['titre_formulaire'].'"';
                        $le_formulaire->titre_formulaire = $formulaire['titre_formulaire'];
                    }
                }

                if (isset($formulaire['type_formulaire'])) {

                    if ($formulaire['type_formulaire'] != $le_formulaire->type_formulaire){

                        $infos_return[] = 'On change '.$le_formulaire['nom_formulaire'].'.type_formulaire en "'.$formulaire['type_formulaire'].'"';
                        $le_formulaire->type_formulaire = $formulaire['type_formulaire'];
                    }
                }

                if (isset($formulaire['vue_js'])) {

                    // On check un à un les options du formulaire libre dans les migrations
                    foreach ($formulaire['vue_js'] as $nom_option => $valeur) {

                        // La bdd est à jour par rapport aux migrations
                        if ($valeur == $le_formulaire->$nom_option)
                            continue;

                        else{

                            $infos_return[] = 'On change '.$le_formulaire['nom_formulaire'].'.'.$nom_option.' en "'.$valeur.'"';
                            $le_formulaire->$nom_option = $valeur;
                        }
                    }
                }

                if (isset($formulaire['index_traduction'])) {

                    if ($formulaire['index_traduction'] != $le_formulaire->type_element){

                        $infos_return[] = 'On change '.$le_formulaire['index_traduction'].'.index_traduction en "'.$formulaire['index_traduction'].'"';
                        $le_formulaire->index_traduction = $formulaire['index_traduction'];
                    }
                }

                $le_formulaire->save();

            }
            else {

                $le_formulaire = new Formulaire;

                $le_formulaire->nom_formulaire = $nom_formulaire;

                if (isset($formulaire['vue_js']['vuejs_data']))
                    $le_formulaire->vuejs_data = $formulaire['vue_js']['vuejs_data'];

                if (isset($formulaire['vue_js']['vuejs_methods']))
                    $le_formulaire->vuejs_methods = $formulaire['vue_js']['vuejs_methods'];

                if (isset($formulaire['vue_js']['surcharger_la_vue']))
                    $le_formulaire->surcharger_la_vue = $formulaire['vue_js']['surcharger_la_vue'];

                if (isset($formulaire['type_element']))
                    $le_formulaire->type_element = $formulaire['type_element'];

                if (isset($formulaire['titre_formulaire']))
                    $le_formulaire->titre_formulaire = $formulaire['titre_formulaire'];

                if (isset($formulaire['index_traduction']))
                    $le_formulaire->index_traduction = $formulaire['index_traduction'];

                if (isset($formulaire['type_formulaire']))
                    $le_formulaire->type_formulaire = $formulaire['type_formulaire'];

                $le_formulaire->save();

                $infos_return[] = 'On crée le formulaire '.$le_formulaire->nom_formulaire;
            }

            if(empty($le_formulaire->index_traduction)){

                $type_element = empty($le_formulaire->type_element) ? str_replace(array('fiche_','creation_volee_','extranet_'),'',$le_formulaire->nom_formulaire) : $le_formulaire->type_element;

                try {
                    $table_libre = table_libre($type_element);
                }
                catch(\Exception $e){
                    $table_libre = null;
                }

                if(!empty($table_libre) || !empty($le_formulaire->titre_formulaire)) {

                    $le_formulaire->index_traduction = $service_traduction->calcul_index_traduction(
                        12,
                        array(
                            'formulaire',
                            $le_formulaire->nom_formulaire
                        ),
                        array(
                            'titre' => empty($le_formulaire->titre_formulaire) ? ucfirst($table_libre->nom_table) : $le_formulaire->titre_formulaire,
                        ),
                        isset($formulaires_standard[$le_formulaire->nom_formulaire]) ? true : false
                    );

                }

                $le_formulaire->save();
            }

            // Puis on les re crée
            if(isset($formulaire['champs_libres'])) {

                foreach($formulaire['champs_libres'] as $champ) {

                    $champ_formulaire = new Formulaires_champs;

                    if(!isset($champ['taille_avant']))
                        $champ['taille_avant'] = 0;

                    if(!isset($champ['taille_libelle']))
                        $champ['taille_libelle'] = 2;

                    if(!isset($champ['taille_champ']))
                        $champ['taille_champ'] = 4;

                    if(!isset($champ['taille_apres']))
                        $champ['taille_apres'] = 0;

                    if(!isset($champ['valeur_html']))
                        $champ['valeur_html'] = null;

                    if(!isset($champ['type_champ']))
                        $champ['type_champ'] = null;

                    if(!isset($champ['id_editeur']))
                        $champ['id_editeur'] = null;

                    if(!isset($champ['nom_vue']))
                        $champ['nom_vue'] = null;

                    if(!isset($champ['type_vue']))
                        $champ['type_vue'] = null;

                    if(!isset($champ['condition_affichage_v_if']))
                        $champ['condition_affichage_v_if'] = null;

                    if(!isset($champ['condition_affichage_en_v_show']))
                        $champ['condition_affichage_en_v_show'] = 0;

                    if(!isset($champ['condition_obligatoire']))
                        $champ['condition_obligatoire'] = null;

                    if(!isset($champ['condition_lecture_seule']))
                        $champ['condition_lecture_seule'] = null;

                    if(!isset($champ['nom_sous_formulaire']))
                        $champ['nom_sous_formulaire'] = null;

                    if(!isset($champ['nom_affichage_sous_formulaire']))
                        $champ['nom_affichage_sous_formulaire'] = null;

                    $champ_formulaire->nom_formulaire = $le_formulaire->nom_formulaire;
                    $champ_formulaire->type_element = $champ['type_element'];
                    $champ_formulaire->nom_sql = $champ['nom_sql'];
                    $champ_formulaire->taille_avant = $champ['taille_avant'];
                    $champ_formulaire->taille_libelle = $champ['taille_libelle'];
                    $champ_formulaire->taille_champ = $champ['taille_champ'];
                    $champ_formulaire->taille_apres = $champ['taille_apres'];
                    $champ_formulaire->ordre = $champ['ordre'];
                    $champ_formulaire->valeur_html = $champ['valeur_html'];
                    $champ_formulaire->type_champ = $champ['type_champ'];
                    $champ_formulaire->id_editeur = $champ['id_editeur'];
                    $champ_formulaire->nom_vue = $champ['nom_vue'];
                    $champ_formulaire->type_vue = $champ['type_vue'];
                    $champ_formulaire->condition_affichage_v_if = $champ['condition_affichage_v_if'];
                    $champ_formulaire->condition_affichage_en_v_show = $champ['condition_affichage_en_v_show'];
                    $champ_formulaire->condition_obligatoire = $champ['condition_obligatoire'];
                    $champ_formulaire->condition_lecture_seule = $champ['condition_lecture_seule'];
                    $champ_formulaire->nom_sous_formulaire = $champ['nom_sous_formulaire'];
                    $champ_formulaire->nom_affichage_sous_formulaire = $champ['nom_affichage_sous_formulaire'];

                    $champ_formulaire->save();

                }
            }

            // Puis on les re crée
            if(isset($formulaire['valeurs_par_defaut'])) {

                foreach($formulaire['valeurs_par_defaut'] as $valeur_par_defaut) {

                    $valeur_par_defaut_formulaire = new Formulaire_valeur_par_defaut;

                    $valeur_par_defaut_formulaire->nom_formulaire = $le_formulaire->nom_formulaire;
                    $valeur_par_defaut_formulaire->nom_sql = $valeur_par_defaut['nom_sql'];
                    $valeur_par_defaut_formulaire->valeur = $valeur_par_defaut['valeur'];

                    $valeur_par_defaut_formulaire->save();

                }
            }
        }



        return $infos_return;
    }

    /**
     *
     * Mise à jour des sous-formulaires (migrations / seeds)
     *
     */
    public static function maj_sous_formulaires($type_element = false) {


        $infos_return = array();

        $sous_formulaires = array();
        $sous_formulaires_standard = array();

        $repertoires_migrations = [
            'Eden/Migrations/Sous_formulaire',
            'Migrations/Sous_formulaire'
        ];

        foreach ($repertoires_migrations as $index_repertoir_migrations => $repertoire_migrations){

            if(is_dir(app_path($repertoire_migrations))) {

                $repertoire = scandir(app_path($repertoire_migrations));

                foreach($repertoire as $fichier) {

                    if($fichier == '.' || $fichier == '..')
                        continue;

                    $contenu_tmp = require(app_path($repertoire_migrations . '/' .$fichier));

                    $index_tableau = str_replace('.php', '', $fichier);

                    if($index_repertoir_migrations === 0)
                        $sous_formulaires_standard[$index_tableau] = $contenu_tmp;

                    if(isset($sous_formulaires[$index_tableau]))
                        $sous_formulaires[$index_tableau] = array_merge($sous_formulaires[$index_tableau], $contenu_tmp);
                    else
                        $sous_formulaires[$index_tableau] = $contenu_tmp;

                }
            }

        }

        if($type_element !== false) {

            if(isset($sous_formulaires[$type_element])) {

                $sous_formulaires = $sous_formulaires[$type_element];
                $sous_formulaires = [$type_element => $sous_formulaires];
            }
            else {

                return;
            }
        }

        foreach ($sous_formulaires as $nom_sous_formulaire => $infos_sous_formulaire){

            $le_sous_formulaire = modele('eden_sous_formulaire')->where('nom_sous_formulaire', $nom_sous_formulaire)->first();

            $maj_sous_formulaire = true;

            if(!empty($le_sous_formulaire)){

                $maj_sous_formulaire = false;

                $le_sous_formulaire = $le_sous_formulaire->toArray();

                foreach ($infos_sous_formulaire as $index_sous_formulaire => $valeur_sous_formulaire){

                    if($maj_sous_formulaire === true)
                        continue;

                    if((!isset($le_sous_formulaire[$index_sous_formulaire]) && $valeur_sous_formulaire !== null) || (isset($le_sous_formulaire[$index_sous_formulaire])) && $le_sous_formulaire[$index_sous_formulaire] != $valeur_sous_formulaire)
                        $maj_sous_formulaire = true;

                }

            }

            if($maj_sous_formulaire === true){

                if(!empty($le_sous_formulaire))
                    $retour = management('eden_sous_formulaire', $le_sous_formulaire['id'])->enregistre($infos_sous_formulaire);
                else
                    $retour = management('eden_sous_formulaire')->enregistre($infos_sous_formulaire);

                if($retour != true)
                    $infos_return[] = $retour;
            }

        }

        return $infos_return;

    }

    /**
     *
     * Mise à jour des champs libres listes (migrations / seeds)
     *
     */
    public static function maj_champs_libres_listes($type_element = false) {

        $infos_return = array();

        $tables = array();

        // on vient ajouter les formulaires spécifiques
        if(is_dir(app_path('Migrations/Champs_libres_listes'))) {

            $repertoire = scandir(app_path('Migrations/Champs_libres_listes'));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Champs_libres_listes/'.$fichier));

                $index_tableau = str_replace('.php', '', $fichier);

                if(isset($tables[$index_tableau]))
                    $tables[$index_tableau] = array_merge($tables[$index_tableau], $contenu_tmp);
                else
                    $tables[$index_tableau] = $contenu_tmp;
            }
        }

        if($type_element !== false) {

            if(isset($tables[$type_element])) {
                $tables = $tables[$type_element];
                $tables = [$type_element => $tables];
            } else {
                return;
            }
        }

        if(empty($tables))
            return;

        foreach ($tables as $unetable => $le_repertoire){

            $type_element_cl = $le_repertoire['options']['type_element'];
            $nom_sql_cl = $le_repertoire['options']['nom_sql'];

            $le_champ_libre = Champ_libre::where('type_element',$type_element_cl)->where('nom_sql',$nom_sql_cl)->first();
            if ($le_champ_libre == null)
                continue;

            $id_cl = $le_champ_libre['id_cl'];

            foreach ($le_repertoire['champs'] as $index_traduction => $option) {

                $count = Champ_libre_liste::where('id_cl',$id_cl)->where('index_traduction',$index_traduction)->count();

                if ($count > 0) {

                    $le_champ_liste = Champ_libre_liste::where('id_cl',$id_cl)->where('index_traduction',$index_traduction)->first();
                    $changement= false;

                    if ($le_champ_liste['type_valeur'] != $option['type_valeur']) {

                        $changement = true;
                        $le_champ_liste['type_valeur'] = $option['type_valeur'];
                    }

                    if ($le_champ_liste['couleur'] != $option['couleur']) {

                        $changement = true;
                        $le_champ_liste['couleur'] = $option['couleur'];
                    }

                    if ($le_champ_liste['couleur_fond'] != $option['couleur_fond']) {

                        $changement = true;
                        $le_champ_liste['couleur_fond'] = $option['couleur_fond'];
                    }

                    if ($le_champ_liste['couleur_police'] != $option['couleur_police']) {

                        $changement = true;
                        $le_champ_liste['couleur_police'] = $option['couleur_police'];
                    }

                    if ($le_champ_liste['ordre'] != $option['ordre']) {

                        $changement = true;
                        $le_champ_liste['ordre'] = $option['ordre'];
                    }

                    if (isset($option['icone']) && $le_champ_liste['icone'] != $option['icone']) {

                        $changement = true;
                        $le_champ_liste['icone'] = $option['icone'];
                    }

                    $le_champ_liste->save();

                    if ($changement)
                        $infos_return[] = 'On modifie le champ libre liste '.$le_repertoire['options']['type_element'].'.'.$le_repertoire['options']['nom_sql'];

                    continue;
                }

                $nouveau_champ_libre_liste = new Champ_libre_liste;

                $nouveau_champ_libre_liste->index_traduction = $index_traduction;
                $nouveau_champ_libre_liste->valeur = $option['valeur'];
                $nouveau_champ_libre_liste->id_cl = $id_cl;
                $nouveau_champ_libre_liste->type_valeur = $option['type_valeur'];
                $nouveau_champ_libre_liste->couleur = $option['couleur'];
                $nouveau_champ_libre_liste->couleur_fond = $option['couleur_fond'];
                $nouveau_champ_libre_liste->couleur_police = $option['couleur_police'];
                $nouveau_champ_libre_liste->ordre = $option['ordre'];

                if(isset($option['icone']))
                    $nouveau_champ_libre_liste->icone = $option['icone'];

                $nouveau_champ_libre_liste->save();

                $infos_return[] = 'On crée le champ libre liste '.$le_repertoire['options']['type_element'].'.'.$le_repertoire['options']['nom_sql'];
            }
        }

        return $infos_return;
    }

    /**
     *
     * Mise à jour des champs libres listes formatées (migrations / seeds)
     *
     */
    public static function maj_champs_libres_listes_formatees($type_element = false) {

        $infos_return = array();

        $tables = array();

        // on vient ajouter les formulaires spécifiques
        if(is_dir(app_path('Migrations/Champs_libres_listes_formatees'))) {

            $repertoire = scandir(app_path('Migrations/Champs_libres_listes_formatees'));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Champs_libres_listes_formatees/'.$fichier));

                $index_tableau = str_replace('.php', '', $fichier);

                if(isset($tables[$index_tableau]))
                    $tables[$index_tableau] = array_merge($tables[$index_tableau], $contenu_tmp);
                else
                    $tables[$index_tableau] = $contenu_tmp;
            }
        }

        if($type_element !== false) {

            if(isset($tables[$type_element])) {
                $tables = $tables[$type_element];
                $tables = [$type_element => $tables];
            } else {
                return;
            }
        }

        if(empty($tables))
            return;

        $ids_liste_choix = Champs_liste_formatee::select('id_liste_choix')->distinct()->get()->pluck('id_liste_choix')->toArray();

        foreach ($tables as $unetable => $le_repertoire){

            $id_liste_choix = $le_repertoire['id_liste_choix'];

            if(in_array($id_liste_choix,$ids_liste_choix))
                unset($ids_liste_choix[array_search($id_liste_choix,$ids_liste_choix)]);

            foreach ($le_repertoire['champs'] as $id_valeur => $option) {

                $count = Champs_liste_formatee::where('id_liste_choix',$id_liste_choix)->where('id_valeur',$id_valeur)->count();

                if ($count > 0) {

                    $le_champ_liste = Champs_liste_formatee::where('id_liste_choix',$id_liste_choix)->where('id_valeur',$id_valeur)->first();
                    $changement= false;

                    if ($le_champ_liste['valeur'] != $option['valeur']) {

                        $changement = true;
                        $le_champ_liste['valeur'] = $option['valeur'];
                    }

                    if ($le_champ_liste['desactivee'] != $option['desactivee']) {

                        $changement = true;
                        $le_champ_liste['desactivee'] = $option['desactivee'];
                    }

                    if ($le_champ_liste['couleur'] != $option['couleur']) {

                        $changement = true;
                        $le_champ_liste['couleur'] = $option['couleur'];
                    }

                    if ($le_champ_liste['couleur_fond'] != $option['couleur_fond']) {

                        $changement = true;
                        $le_champ_liste['couleur_fond'] = $option['couleur_fond'];
                    }

                    if ($le_champ_liste['couleur_police'] != $option['couleur_police']) {

                        $changement = true;
                        $le_champ_liste['couleur_police'] = $option['couleur_police'];
                    }

                    if ($le_champ_liste['ordre'] != $option['ordre']) {

                        $changement = true;
                        $le_champ_liste['ordre'] = $option['ordre'];
                    }

                    if ($le_champ_liste['icone'] != $option['icone']) {

                        $changement = true;
                        $le_champ_liste['icone'] = $option['icone'];
                    }

                    $le_champ_liste->save();

                    if ($changement)
                        $infos_return[] = 'On modifie le champ libre liste formatee '.$id_liste_choix.'.'.$id_valeur;

                    continue;
                }

                $nouveau_champ_libre_liste_formatee = new Champs_liste_formatee();

                $nouveau_champ_libre_liste_formatee->id_valeur = $id_valeur;
                $nouveau_champ_libre_liste_formatee->id_liste_choix = $id_liste_choix;
                $nouveau_champ_libre_liste_formatee->valeur = $option['valeur'];
                $nouveau_champ_libre_liste_formatee->desactivee = $option['desactivee'];
                $nouveau_champ_libre_liste_formatee->couleur = $option['couleur'];
                $nouveau_champ_libre_liste_formatee->couleur_fond = $option['couleur_fond'];
                $nouveau_champ_libre_liste_formatee->couleur_police = $option['couleur_police'];
                $nouveau_champ_libre_liste_formatee->ordre = $option['ordre'];
                $nouveau_champ_libre_liste_formatee->icone = $option['icone'];

                $nouveau_champ_libre_liste_formatee->save();

                $infos_return[] = 'On crée le champ libre liste formatee '.$id_liste_choix.'.'.$id_valeur;
            }
        }

        //On supprime les listes formatées qui ne sont plus présentes dans les migrations
        Champs_liste_formatee::whereIn('id_liste_choix',$ids_liste_choix)->delete();

        return $infos_return;
    }

    /**
     *
     * Mise à jour des listes libres autresvues (migrations / seeds)
     *
     */
    public static function maj_liste_libre_autresvues() {

        $infos_return = array();
        $tables = array();

        // on vient ajouter les autresvues spécifiques
        if(is_dir(app_path('Migrations/Listes_libres_autresvues'))) {

            $repertoire = scandir(app_path('Migrations/Listes_libres_autresvues'));

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Listes_libres_autresvues/'.$fichier));

                $index_tableau = str_replace('.php', '', $fichier);

                if(isset($tables[$index_tableau])) {

                    foreach($contenu_tmp as $nom_colonne => $infos) {

                        $tables[$index_tableau][$nom_colonne] = $infos;
                    }
                }
                else {

                    $tables[$index_tableau] = $contenu_tmp;
                }
            }
        }

        $count = Liste_libre_autresvues::count();

        if ($count > 0) {

            $autresvues = Liste_libre_autresvues::get();

            foreach ($autresvues as $autrevue) {

                $autrevue->delete();
            }
        }

        foreach ($tables as $unetable => $le_repertoire){
            foreach ($le_repertoire as $autrevue) {

                $nouvelle_autrevue = new Liste_libre_autresvues;

                if(!isset($autrevue['liste_libre_id_1']) || !isset($autrevue['liste_libre_id_2'])) {
                    exception("Le paramétrage d'une 'autre vue' n'est pas correct dans les migrations");
                    continue;
                }

                // On vérifie si les chaines de caractères possèdent un id rapport
                $liste_1_rapport_boolean = strpos($autrevue['liste_libre_id_1'], "+");
                $liste_2_rapport_boolean = strpos($autrevue['liste_libre_id_2'], "+");

                // On va chercher la liste 1 en prenant id rapport en compte
                if (!$liste_1_rapport_boolean)
                    $liste_1 = Liste_libre::where("type_element",$autrevue['liste_libre_id_1'])->first();

                else{

                    $tableau_chaine = explode("+", $autrevue['liste_libre_id_1']);
                    $liste_1 = Liste_libre::where("type_element",$tableau_chaine[0])->where("id_rapport",$tableau_chaine[1])->first();
                }

                // On va chercher la liste 1 en prenant id rapport en compte
                if (!$liste_2_rapport_boolean)
                    $liste_2 = Liste_libre::where("type_element",$autrevue['liste_libre_id_2'])->first();

                else{

                    $tableau_chaine = explode("+", $autrevue['liste_libre_id_2']);
                    $liste_2 = Liste_libre::where("type_element",$tableau_chaine[0])->where("id_rapport",$tableau_chaine[1])->first();
                }

                // Verifcation si les listes sont null
                if ($liste_1 == null) {
                    $liste_1 = Liste_libre::where("id",$autrevue['liste_libre_id_1'])->first();
                }
                if ($liste_2 == null) {
                    $liste_2 = Liste_libre::where("id",$autrevue['liste_libre_id_2'])->first();
                }

                // Les ID affecté sont dynamique et non lié à l'id dans le fichier de migration mais l'id en base du projet
                if($liste_1 != null && $liste_2 != null){

                    $nouvelle_autrevue->liste_libre_id_1 = $liste_1->id;
                    $nouvelle_autrevue->liste_libre_id_2 = $liste_2->id;

                    $nouvelle_autrevue->save();
                }
            }
        }
        return $infos_return;
    }

    /**
     *
     * Pour des questions de rétrocompatibilité, on est obligés de générer les fichiers de migrations spécifiques
     * Avec le paramétrage actuel des clients
     *
     */
    public static function genere_fichiers_parametrage_actuel() {

        // les tables libres
        $tables_libres = Table_libre::get();

        foreach($tables_libres as $table_libre) {

            Table_libre_management::generer_fichier_migration($table_libre->type_element);
        }

        // les listes libres
        $listes_libres = Liste_libre::get();

        foreach($listes_libres as $liste_libre) {

            Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id);
        }
    }

    /**
     *
     * Teste une liste d'urls et vérifie leur fonctionnement
     *
     */
    public static function test_urls() {

        $urls = service('url_test_erreur')->retourne_url_a_tester();

        $client = new \GuzzleHttp\Client();

        $ok = array();
        $ko = array();

        foreach ($urls as $url) {

            $headers = get_headers(asset($url).'?eden_cron_id_utilisateur='.moi()->id.'&eden_cron_mdp=1', 1);

            if($headers[0] == 'HTTP/1.0 200 OK') {

                $ok[$url] = true;
            }
            else {

                $ko[$url] = $headers[0];
            }

        }

        dd($ok, $ko);
    }

    public static function test_url($url) {

        $client = new \GuzzleHttp\Client();

        $ok = array();
        $ko = array();

        $temps_debut = microtime(true);

        $context = stream_context_create( [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        if(empty(moi()))
            $headers = get_headers(asset($url).'?eden_cron_id_utilisateur='.modele('utilisateur')->first()->id.'&eden_cron_mdp=1', 1, $context);
        else
            $headers = get_headers(asset($url).'?eden_cron_id_utilisateur='.moi()->id.'&eden_cron_mdp=1', 1, $context);

        $duree = round(microtime(true) - $temps_debut, 2);

        $content = null;
        $erreur = '';



        if(in_array($headers[0], array('HTTP/1.0 200 OK', 'HTTP/1.1 200 OK'))) {

            if(strpos($url, 'maintenance/test_url/element') !== false)
                $content = json_decode(file_get_contents(asset($url).'?eden_cron_id_utilisateur='.moi()->id.'&eden_cron_mdp=1'));

            if(strpos($url, 'maintenance/test_url/feature') !== false)
                $content = json_decode(file_get_contents(asset($url).'?eden_cron_id_utilisateur='.moi()->id.'&eden_cron_mdp=1'));


            if($content !== null && !empty($content->erreur))
                $erreur = $content->erreur;

            if(!empty($erreur))
                return ['success' => false, 'duree' => $duree, 'code_retour' => $headers[0], 'erreur' => $erreur];
            else
                return ['success' => true, 'duree' => $duree, 'code_retour' => $headers[0], 'erreur' => $erreur];

        }
        else if(in_array($headers[0], array('HTTP/1.0 302 Found', 'HTTP/1.1 302 Found'))) {

            return ['success' => false, 'duree' => $duree, 'code_retour' => $headers[0], 'erreur' => $erreur, 'redirection' => str_replace('https://' . $_SERVER['HTTP_HOST'] . '/','',$headers['Location'][0])];

        }
        else {

            return ['success' => false, 'duree' => $duree, 'code_retour' => $headers[0], 'erreur' => $erreur];
        }
    }

    /**
     *
     * Permet de mettre à jour les champs libres des vues
     *
     */
    public static function maj_champs_libres_vues(){

        //Gestion de la traduction des champs libres
        $tables_vue_sql = Table_libre::where('vue_sql',1)->get();

        foreach($tables_vue_sql as $table_vue_sql){

            if(empty($table_vue_sql->index_traduction)){

                $table_vue_sql->index_traduction = service('traduction')->calcul_index_traduction(
                    2,
                    array(
                        'tables_libres',
                        $table_vue_sql->type_element,
                    ),
                    array(
                        'nom_table' => $table_vue_sql->nom_table,
                        'element' => $table_vue_sql->element,
                        'element_pluriel' => $table_vue_sql->element_pluriel
                    )
                );

                $table_vue_sql->save();
            }
        }

        $champs_libres = Champ_libre::whereNotNull('type_element_origine')
            ->whereNotNull('nom_sql_origine')->get();

        $champs_libres_type_element_origine = array_unique($champs_libres->pluck('type_element_origine')->toArray());

        $champs_libres_initial_par_type_element = Champ_libre::select(DB::raw("CONCAT(type_element,'.',nom_sql) as type_element_nom_sql"),'eden_champslibres.*')->whereIn('type_element',$champs_libres_type_element_origine)
            ->get()->keyBy('type_element_nom_sql');

        foreach($champs_libres as $champ_libre){

            if(isset($champs_libres_initial_par_type_element[$champ_libre->type_element_origine.'.'.$champ_libre->nom_sql_origine])){

                unset($champs_libres_initial_par_type_element[$champ_libre->type_element_origine.'.'.$champ_libre->nom_sql_origine]->type_element_nom_sql);

                service('vue_sql')->gestion_champ_libre($champ_libre->type_element, $champs_libres_initial_par_type_element[$champ_libre->type_element_origine.'.'.$champ_libre->nom_sql_origine], array(),$champ_libre);

            }
        }
    }
    /**
     *
     * Met à jour les versions d'eden dans la bdd
     *
     */
    public static function maj_version_eden(){

        $rappels_version_organisees = array();

        $derniere_version_ajouter = modele('version_eden')->select(DB::raw('max(numero_version) as max_numero_version'))->first()->max_numero_version;

        if($derniere_version_ajouter == null)
            $derniere_version_ajouter = 0;

        $versions = json_decode(file_get_contents('../app/Eden/Versioning.json'), true);

        $aujourdhui = date('Y-m-d');

        foreach($versions as $numero_version => $version){
            if($numero_version <= $derniere_version_ajouter)
                unset($versions[$numero_version]);
        }

        ksort($versions);

        $cle = config('services.eden.cle_api_eden');

        foreach($versions as $numero_version => $version){

            $rappels_version = array('version' => $numero_version);

            foreach($version as $ticket_id => $ticket_dans_version) {

                if(isset($ticket_dans_version['rappel'])){

                    $rappel = $ticket_dans_version['rappel'];

                    if($rappel['type_rappel'] == 2){

                        if(isset($rappel['projets_concernes']) && in_array($cle,$rappel['projets_concernes'])){

                            $rappels_version['rappels'][] = array(
                                'ticket'=>array(
                                    "id" => $ticket_id,
                                    "titre" => $ticket_dans_version['titre'],
                                    "description" => $ticket_dans_version['description'],
                                ),
                                'referent' => $rappel['referent'],
                                'description' => $rappel['description'],
                            );
                        }

                    }
                    else{

                        $rappels_version['rappels'][] = array(
                            'ticket'=>array(
                                "id" => $ticket_id,
                                "titre" => $ticket_dans_version['titre'],
                                "description" => $ticket_dans_version['description'],
                            ),
                            'referent' => $rappel['referent'],
                            'description' => $rappel['description'],
                        );
                    }
                }

                management('version_eden')->enregistre(array(
                    "numero_version" => $numero_version,
                    "titre" => $ticket_dans_version['titre'],
                    "description" => $ticket_dans_version['description'],
                    "date_ajout" => $aujourdhui
                ));

            }

            if(!empty($rappels_version['rappels']))
                $rappels_version_organisees[] = $rappels_version;

        }


        if(!empty($rappels_version_organisees)){

            $parametre_rappels_version = parametre('rappels_version');

            if($parametre_rappels_version != null)
                $rappels_version_organisees = array_merge($rappels_version_organisees,json_decode($parametre_rappels_version,true));

            parametre('rappels_version',json_encode($rappels_version_organisees));
        }

        return true;
    }

    /**
     *
     * Permet de créer les index de traduction pour les champs modifie_le,cree_le,modifie_par,cree_par,cle_externe
     *
     */
    public static function cree_index_traduction_champs_par_defaut(){

        $tables_libres = Tables_libres::tables_libres_standard();

        $champs_par_defaut = Champ_libre::whereIn('nom_sql',array('modifie_le','modifie_par','cree_le','cree_par','cle_externe'))->get();

        foreach($champs_par_defaut as $champ){

            if(!empty($champ->index_traduction))
                continue;

            $champ->index_traduction = service('traduction')->calcul_index_traduction(
                1,
                array(
                    'champs_libres',
                    $champ->type_element,
                    $champ->nom_sql,
                ),
                array(
                    'nom' => $champ->getOriginal('nom')
                ),
                (in_array($champ->type_element,$tables_libres) ? true : false)
            );

            $champ->save();
        }

    }

    /**
     *
     * Maj des traductions
     *
     */
    public static function maj_traductions(){

        if(env('BASE_TRADUCTION') === true)
            return array();

        $nombre_index = modele('traduction_index')->count();

        if($nombre_index == 0){

            $chemin = public_path('eden/traductions/fichier_initialisation.sql');
            \Illuminate\Support\Facades\DB::unprepared(file_get_contents($chemin));
        }

        return service('traduction')->synchroniser();
    }

    /**
     *
     * Génère le nouveau fichier de migration pour les formulaires libres
     *
     */
    public static function generer_fichier_migration_formulaire($nom_formulaire) {

        // On vérifie que le dossier migration existe bien en spécifique
        $chemin_dossier_migrations = app_path().'/Migrations/Formulaires_libres';

        if(!\File::isDirectory($chemin_dossier_migrations))
            \File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
            return false;

        $le_formulaire = Formulaire::where('nom_formulaire',$nom_formulaire)->first();
        $les_champs = Formulaires_champs::where('nom_formulaire',$nom_formulaire)->get();
        $valeur_par_defaut_formulaire = Formulaire_valeur_par_defaut::where('nom_formulaire',$nom_formulaire)->get();

        // On crée a le contenu du nouveau fichier de migration
        $le_formulaire = $le_formulaire->toArray();
        $les_champs = $les_champs->toArray();
        $valeur_par_defaut_formulaire = $valeur_par_defaut_formulaire->toArray();


        $texte =
            '<?php
		return [
		\'type_element\' => "'.$le_formulaire['type_element'].'",
		\'index_traduction\' => "'.$le_formulaire['index_traduction'].'",
		\'type_formulaire\' => "'.$le_formulaire['type_formulaire'].'",
		\'vue_js\' => [
		\'vuejs_data\' => "'.$le_formulaire['vuejs_data'].'",
		\'vuejs_methods\' => "'.$le_formulaire['vuejs_methods'].'",
		\'surcharger_la_vue\' => "'.$le_formulaire['surcharger_la_vue'].'",
		';
        $texte = $texte.'],
		\'champs_libres\' => [
		';
        foreach ($les_champs as $liste_valeur) {
            if(isset($liste_valeur['profils']))
                continue;
            
            $texte = $texte.'[
			';
            foreach ($liste_valeur as $clef => $valeur) {
                $valeur = str_replace("'", "\'", $valeur);
                $texte = $texte.'\''.$clef.'\' => \''.$valeur.'\',
				';
            }
            $texte = $texte.'
			],
			';
        }
        $texte = $texte.'],
		\'valeurs_par_defaut\' => [
		';
        foreach ($valeur_par_defaut_formulaire as $liste_valeur) {
            $texte = $texte.'[
			';
            foreach ($liste_valeur as $clef => $valeur) {
                $valeur = str_replace("'", "\'", $valeur);
                $texte = $texte.'\''.$clef.'\' => \''.$valeur.'\',
				';
            }
            $texte = $texte.'
			],
			';
        }
        $texte = $texte.'],
	    ];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Formulaires_libres/'.$nom_formulaire.'.php';

        // Si le fichier existe, on le supprime
        if (file_exists($chemin_avec_nom_document) == true)
            unlink($chemin_avec_nom_document);

        // Enregistrement du fichier
        $fichier = fopen($chemin_avec_nom_document, "x+");
        fputs($fichier, $texte );
        fclose($fichier);

        return true;

    }


    /**
     *
     * Génère le nouveau fichier de migration pour les sous-formulaires
     *
     */
    public static function generer_fichier_migration_sous_formulaire($nom_sous_formulaire) {

        // On vérifie que le dossier migration existe bien en spécifique
        $chemin_dossier_migrations = app_path().'/Migrations/Sous_formulaire';

        if(!\File::isDirectory($chemin_dossier_migrations))
            \File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
            return false;

        $le_sous_formulaire = modele('eden_sous_formulaire')->where('nom_sous_formulaire',$nom_sous_formulaire)->first();

        // On crée a le contenu du nouveau fichier de migration
        $le_sous_formulaire = $le_sous_formulaire->toArray();


        $texte =
            '<?php
		        return [
            ';

        foreach ($le_sous_formulaire as $index => $valeur){

            $texte = $texte . '    \'' . $index . '\' => \'' . $valeur . '\',
            ';

        }

        $texte = $texte . '     ];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Sous_formulaire/'. $le_sous_formulaire['nom_sous_formulaire'] .'.php';

        // Si le fichier existe, on le supprime
        if (file_exists($chemin_avec_nom_document) == true)
            unlink($chemin_avec_nom_document);

        // Enregistrement du fichier
        $fichier = fopen($chemin_avec_nom_document, "x+");
        fputs($fichier, $texte );
        fclose($fichier);

        return true;

    }

    /**
     *
     * Met en place les clés étrangéres sur les tables non libres
     *
     */
    public static function mise_en_place_cle_etrangere($cles_etrangeres,&$erreurs_cle_etrangere){

        $cles_etrangeres_a_mettre_en_place = array(
            'element_log_detail' => array(
                'id_element_log' => array(
                    'table' => 'element_log',
                    'colonne' => 'id_element_log'
                )
            )
        );

        foreach($cles_etrangeres_a_mettre_en_place as $type_element => $champs_libres) {

            foreach($champs_libres as $nom_champ => $informations) {

                $nom_contrainte = 'CES_' .$nom_champ;

                if (!in_array($nom_contrainte, $cles_etrangeres)) {

                    try {
                        DB::select('ALTER TABLE `' . $type_element . '` ADD CONSTRAINT `' . $nom_contrainte . '` FOREIGN KEY (`'.$nom_champ.'`) REFERENCES `' . $informations['table'] . '`(`'.$informations['colonne'].'`);');
                    } catch (\Exception $e) {
                        $erreurs_cle_etrangere[] = 'Erreur lors de la création de la clé étrangére standard '.$nom_champ.' sur la table ' . $type_element;
                    }
                }
            }
        }
    }

    /**
     *
     * Permet de mettre à jour les utilisateurs de tout les projets
     *
     */
    public static function maj_utilisateurs_easydev_projets(){

        $url_index = env('EDEN_CONSOLE_API_URL').'api/projet/list';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_CONSOLE_API_KEY') . "\r\n",
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

        $utilisateurs_modele = modele('utilisateur')->avec_inactifs()->get()->toArray();

        $variables = http_build_query(
            array(
                'utilisateurs' => $utilisateurs_modele
            )
        );

        $infos_return = array();

        foreach($projets_easydev as $projet) {

            if($projet['code_projet'] == 'reference')
                continue;

            foreach ($environnements as $version => $environnement) {

                if (empty($projet[$environnement]) || $projet[$version] < 2022000051)
                    continue;

                try {
                    $url_index = $projet[$environnement] . '/api/maj_utilisateurs_projet';

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

    /**
     *
     * Mise à jour des VueSQL
     *
     */
    public static function maj_vue_sql() {

        $vue_sql = \App\Eden\Champs_libres::migration_vue_sql();

        //lister les vues existantes en BDD
        $liste_des_vues = collect(DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE "'.env('DB_DATABASE').'" AND TABLE_TYPE = "VIEW"'))->pluck('TABLE_NAME')->toArray();

        $infos_return = array();

        try {
            foreach ($vue_sql as $cle => $liste) {

                $vue_existante = modele('vue_sql')->where('nom_sql', $liste['nom_sql'])->first();
                if(empty($vue_existante)) {
                    $management = management('vue_sql');
                    $modification_effectuee = true;
                }else{
                    $management = management('vue_sql',$vue_existante->id,$vue_existante);
                    $modification_effectuee = false;

                    foreach ($liste as $cle => $valeur){
                        if($vue_existante->{$cle} != $valeur)
                            $modification_effectuee = true;
                    }
                }

                if($modification_effectuee) {
                    $management->enregistre($liste);
                }elseif(!in_array($liste['nom_sql'], $liste_des_vues)){
                    $retour = service('vue_sql')->generer_vues_sql(array($management->modele));

                    if($retour !== true)
                        $infos_return[] = $retour;

                }

            }
        }
        catch (\Exception $e){

            $infos_return[] = 'Erreur maj vue_sql ';
        }

        return $infos_return;
    }

	public static function chargement_erreur_struc_table($type_element)
	{
		try {
			$list_erreurs_data = [];

			$table_libre = \table_libre($type_element);
			$champ_libres = champs_libres($type_element);

			$data = [];

            foreach ($champ_libres as $champs_libre) {
                if ($champs_libre->type == 42) {
                    $query = "SELECT `{$type_element}`.id, `{$type_element}`.`{$champs_libre["nom_sql"]}` as 'valeur_ko' FROM `{$type_element}` LEFT JOIN `{$champs_libre["type_element_ajax"]}` as table_lie ON table_lie.id = `{$type_element}`.`{$champs_libre["nom_sql"]}` WHERE table_lie.id IS NULL AND `{$type_element}`.`{$champs_libre["nom_sql"]}` IS NOT NULL";
                    $data_ko = DB::select($query);
                    if (count($data_ko) > 0) {
                        $data[] = ["data" => $data_ko, "champ" => $champs_libre, "query" => $query];
                    }
                }
            }

			return $data;
		} catch (\Exception $exception) {
			throw $exception;
		}
	}

    public static function etats_des_lieux_routes_rattrapage(){

        $dossiers_a_verifier = array(
            app_path().'/Managements',
            app_path().'/Http/Controllers',
            resource_path().'/views/vendor/eden',
            storage_path().'/app/eden_menus.php',
            storage_path().'/app/eden_menus_extranet.php',
        );

        $elements_a_verifier = require(public_path('eden/licence/remplacements_a_effectuer_spe_licence.php'));

        $etats_des_lieux = array();

        foreach($dossiers_a_verifier as $dossier){
            $etats_des_lieux = array_merge_recursive($etats_des_lieux,self::grep_dossier_fichier($dossier, $elements_a_verifier));
        }

        return $etats_des_lieux;
    }

    protected static function grep_dossier_fichier($chemin, $elements_par_type) {

        $dossier = is_dir($chemin);

        if(is_file($chemin))
            $fichiers = [$chemin];
        elseif($dossier)
            $fichiers = scandir($chemin);
        else
            return [];

        $fichiers_trouves = [];

        foreach ($fichiers as $fichier) {
            if ($fichier != "." && $fichier != "..") {

                $chemin_fichier = $dossier ? $chemin . '/' . $fichier : $fichier;

                if(is_file($chemin_fichier)) {
                    $contenu_fichier = file_get_contents($chemin_fichier);

                    foreach($elements_par_type as $type => $elements){

                        foreach(array_keys($elements) as $valeur){

                            if($type == 'name')
                                $fichier_trouve = strpos($contenu_fichier, "route('".$valeur."'") !== false
                                    || strpos($contenu_fichier, 'route("'.$valeur.'"') !== false
                                    || strpos($contenu_fichier, "'route' => array('".$valeur."'") !== false
                                    || strpos($contenu_fichier, "'route' => '".$valeur."'") !== false;
                            elseif($type == 'url_manuel'){
                                $valeur_a_verifier = substr($valeur,0,strpos($valeur,'{')-1);

                                if($valeur_a_verifier == 'eden')
                                    continue;

                                $fichier_trouve = strpos($contenu_fichier,"'".$valeur_a_verifier."'") !== false ||
                                    strpos($contenu_fichier,'"'.$valeur_a_verifier.'"') !== false ||
                                    strpos($contenu_fichier,"'".$valeur_a_verifier."/'") !== false ||
                                    strpos($contenu_fichier,'"'.$valeur_a_verifier.'/"') !== false;
                            }
                            else {
                                $fichier_trouve = strpos($contenu_fichier, "'".$valeur."'") !== false
                                    || strpos($contenu_fichier, '"'.$valeur.'"') !== false;
                            }

                            if($fichier_trouve){

                                if(!isset($fichiers_trouves[$chemin_fichier]))
                                    $fichiers_trouves[$chemin_fichier] = [];

                                if(!isset($fichiers_trouves[$chemin_fichier][$type]))
                                    $fichiers_trouves[$chemin_fichier][$type] = [];

                                $fichiers_trouves[$chemin_fichier][$type][$valeur] = $elements[$valeur];
                            }
                        }
                    }
                }
                elseif (is_dir($chemin_fichier)) {
                    $fichiers_trouves_dossier = self::grep_dossier_fichier($chemin_fichier, $elements_par_type);
                    $fichiers_trouves = array_merge_recursive($fichiers_trouves, $fichiers_trouves_dossier);
                }
            }
        }

        return $fichiers_trouves;
    }

    /**
     *
     * Gestion du composer 
     * Fusion du composer commun et du composer spécifique
     *
     */


    public static function mise_a_jour_composer() {
        
        $composer_source = base_path('app/Eden/composer.json');
        $composer_specifique = base_path('composer_specifique.json'); 
        $commun = json_decode(file_get_contents($composer_source), true);
        
        //si le composer spécifique existe on fusionne les require
        if(file_exists($composer_specifique) ) {
            $specifique = json_decode(file_get_contents($composer_specifique), true);
            $commun = array_merge_recursive($commun, $specifique);
        }
        
        //création/maj du nouveau composer.json
        $composer_cible = base_path('composer.json');

        $nouveau_composer = json_encode($commun, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if(file_get_contents($composer_cible) == $nouveau_composer){

            $result = Process::path(base_path())
                ->timeout(600)
                ->run('composer dump-autoload');

            if(!$result->successful())
                throw new Eden_exception('Erreur exécution dump-autoload');

            Artisan::call('queue:restart');

            return;
        }

        $retour = file_put_contents($composer_cible,$nouveau_composer);
        
        //si retour true on lance le composer update
        if($retour !== false) {
            $result = Process::path(base_path())
                ->timeout(600)
                ->run('composer update');
            $result->output();

            if($result->successful()){
                Artisan::call('queue:restart');
                return;
            }

            throw new Eden_exception('Erreur exécution composer update
            Faire les commandes suivantes à la racine du projet puis relancer la mise à jour:
            // sudo chown -R www-data:www-data /var/www/eden/vendor
            // sudo chmod -R 775 /var/www/eden/vendor');

        }

        throw new Eden_exception('Erreur création composer.json');

    }

    public static function lancement_script_avant() {
        // Base vide (installation) : pas de données à reprendre, et eden_parametres,
        // qui trace les scripts déjà joués, n'est créée qu'à l'étape suivante
        if(!Schema::hasTable('eden_parametres'))
            return;

        Script_management::lancer_scripts('Avant_migrations');
    }

    public static function lancement_script_apres() {
        Script_management::lancer_scripts('Apres_migrations');
    }

    public static function generer_tables_champs_libres() {
        Maintenance_management::maj_tables();
        Maintenance_management::maj_tables_libres();
        Maintenance_management::maj_champs_libres();
        Maintenance_management::maj_champs_libres_listes();
        Maintenance_management::maj_champs_libres_listes_formatees();
        Maintenance_management::maj_champs_libres_vues();
    }

    public static function generer_listes_libres() {
        $service_traduction = service('traduction');
        $service_traduction->chargement_valeurs_categorie([5, 6, 10]);
        Maintenance_management::maj_listes_libres(false,$service_traduction);
        Maintenance_management::maj_liste_libre_autresvues();
        Maintenance_management::maj_listes_libres_fiches(false,$service_traduction);
        Maintenance_management::maj_listes_libres_export(false,$service_traduction);

    }
    public static function generer_licences() {
        service('licence')->synchronisation();
    }
    public static function generer_fichiers_composants() {

        if(!defined('regeneration_valeurs_listes'))
			define("regeneration_valeurs_listes", true);

        Cache_management::genere_fichiers_composants_modules();
    }
    public static function generer_fichiers_composants_listes() {
       Cache_management::genere_fichiers_composants_listes();

}
    public static function maj_crons(){

        //On récupère les crons déjà répertoriés en bdd
        $crons_existants = modele('cron')->get()->keyBy('nom');

        //On récupère les crons existants dans les fichiers
        $crons = management('cron')->recuperer_crons();

        foreach($crons as $nom_cron => $standard) {

            if (isset($crons_existants[$nom_cron])) {

                unset($crons_existants[$nom_cron]);
                continue;
            }

            $cron = [
                'nom' => $nom_cron,
                'en_cours' => parametre($nom_cron . '_en_cours'),
                'derniere_execution' => parametre('derniere_execution_' . $nom_cron),
                'temps_avant_relance' => 24,
                'standard' => $standard,
            ];

            management('cron')->enregistre($cron);
        }

        //On supprime les crons existants qui ont été supprimés dans les fichiers
        foreach($crons_existants as $cron_a_supprimer){

            management('cron', $cron_a_supprimer->id, $cron_a_supprimer)->supprime();
        }

    }
}