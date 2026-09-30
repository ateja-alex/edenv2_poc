<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\XLSX\Options;

class Import_en_cours_management extends Element_management{

    private $nombre_a_traiter_par_execution = 100000;
    private $champs = false;

    /**
     * @return mixed
     *
     * Permet de récupérer les champs d'entetes constituant un fichier
     *
     */
    public function champs_entete_fichier(){

        if($this->champs !== false)
            return $this->champs;

        $extension = explode('.',$this->modele->chemin_acces)[1];

        $reader = $extension == 'csv' ? new CSVReader() : new XLSXReader();
        $reader->open(storage_path('app/public/import_sur_mesure/' . $this->modele->chemin_acces));

        $sheet = $reader->getSheetIterator()->current();

        $champs = [];

        foreach ($sheet->getRowIterator() as $index_row => $row) {

            if($index_row == 1){

                foreach ($row->getCells() as $cell) {
                    $champs[] = retraite_caracteres_speciaux($cell->getValue(),'_');
                }

                break;
            }
        }

        $reader->close();

        return $this->champs = $champs;
    }

    /**
     * @return mixed
     *
     * On surchage modéle par défaut pour avoir des array à la place de text
     *
     */
    public function modele_par_defaut(){
        $modele_par_defaut = parent::modele_par_defaut();

        $modele_par_defaut['combinaisons_cles'] = [];
        $modele_par_defaut['erreurs'] = [];
        $modele_par_defaut['types_notifications'] = [];

        return $modele_par_defaut;
    }

    /**
     * @return mixed
     *
     * Permet de récupérer le modéle d'un import avec les champs textes décodés en array
     *
     */
    public function modele_formate(){

        $modele = clone $this->modele;
        $modele->combinaisons_cles = $this->combinaisons_cles();
        $modele->erreurs = $this->erreurs();

        if(empty($modele->types_notifications))
            $modele->types_notifications = [];

        return $modele;
    }

    /**
     * @param array $modifications
     * @param false $modele
     * @return mixed
     *
     * Gére l'enregistrement des arrays au format json
     *
     */
    public function enregistre($modifications = array(), $modele = false){
        if(isset($modifications['combinaisons_cles']) && is_array($modifications['combinaisons_cles']))
            $modifications['combinaisons_cles'] = json_encode($modifications['combinaisons_cles'],JSON_INVALID_UTF8_IGNORE);

        if(isset($modifications['erreurs']) && is_array($modifications['erreurs']))
            $modifications['erreurs'] = json_encode($modifications['erreurs'],JSON_INVALID_UTF8_IGNORE);

        if(isset($modifications['statut']) && $modifications['statut'] == 4) {
            $this->import_sur_mesure()->enregistre(array('complet' => 1));
        }

        return parent::enregistre($modifications, $modele);
    }

    /**
     * @return mixed
     *
     * Permet de récupérer les combinaisons de clés du fichier
     *
     */
    public function combinaisons_cles(){

        if(empty($this->modele->combinaisons_cles))
            return [];

        return json_decode($this->modele->combinaisons_cles,true);
    }

    /**
     * @return mixed
     *
     * Permet de récupérer les erreurs durant l'import
     *
     */
    public function erreurs(){

        if(empty($this->modele->erreurs))
            return [];

        return json_decode($this->modele->erreurs,true);
    }

    /**
     * @return mixed
     *
     * Permet de récupérer l'import sur mesure
     *
     */
    public function import_sur_mesure(){

        return management('import_sur_mesure',$this->modele->import_sur_mesure);
    }

    /**
     * @param $champs
     * @return array
     *
     * Cette fonction récupére les valeurs des listes libres et des listes formatées afin d'effectuer
     * la correspondance avec les valeurs du fichier
     *
     */
    public function correspondance_valeurs_liste($champs){

        $correspondances = array();

        $collection = $this->fichier_to_array();

        $import_sur_mesure = $this->import_sur_mesure();

        foreach ($champs as $champ) {

            if(!empty($champ['cle_cree']))
                continue;

            if (isset($champ['champ_import_parent']))
                $champ_import = $champ['champ_import_parent'];
            else
                $champ_import = $champ['champ_import'];

            // Si le champ a besoin d'une correspondance avec les valeurs ERP, on récupére le champ de l'erp et les valeurs correspondantes déjà référencé
            if ($champ['correspondances_valeurs'] == 'true' ) {

                 $champ_correspondance = explode('.', $champ['correspondance']);

                $element = $champ_correspondance[0];
                $nom_sql = $champ_correspondance[1];
                $type_element = $import_sur_mesure->element_type_element($element);

                $valeurs_import = [];

                if(isset($champ['correspondances_valeurs_liste']))
                    $valeurs_import = array_column($champ['correspondances_valeurs_liste'],'valeur');

                $correspondances[] = array(
                    'element' => $element,
                    'champ_import' => $champ_import,
                    'champ_erp' => management($type_element)->champ($nom_sql)->vmodel(false)->cree(),
                    'valeurs_import' => $valeurs_import,
                );
            }
        }

        // On parcourt les valeurs du fichier
        foreach ($collection as $index_ligne => $ligne) {

            //On récupére les valeurs possibles du fichier
            foreach ($correspondances as &$correspondance) {

                if (!empty($ligne[$correspondance['champ_import']]))
                    $correspondance['valeurs_import'][] = trim($ligne[$correspondance['champ_import']]);
            }
        }

        //Pour chaque correspondance on va traiter l'array pour n'avoir aucun doublon
        foreach ($correspondances as &$correspondance) {
            $correspondance['valeurs_import'] = array_unique($correspondance['valeurs_import']);
        }

        sort($correspondance['valeurs_import']);

        return array(
            'succes' => true,
            'correspondances' => $correspondances,
            'import_en_cours_id' => $this->modele->id,
        );

    }

    /**
     * @param $champs
     * @return array
     *
     * Permet de gérer la vérification des clés d'import dans le fichier et en bdd
     * afin de savoir s'il n'y a pas de ligne avec les mêmes clés
     *
     */
    public function verifications_cles($champs){

        $correspondances = array();

        $import_sur_mesure = $this->import_sur_mesure();

        $cles_mise_a_jour_par_type_element = array(
            $import_sur_mesure->modele->type_element => array(),
        );

        $verification_des_cles = false;

        foreach ($champs as $champ) {

            if(!empty($champ['cle_cree']))
                continue;

            if (isset($champ['champ_import_parent']))
                $champ_import = $champ['champ_import_parent'];
            else
                $champ_import = $champ['champ_import'];

            //Si le champ est une clé de mise à jour on l'ajoute dans l'array des cles de mises à jour
            if ($champ['cle_mise_a_jour'] == 'true') {

                $verification_des_cles = true;

                $champ_info = explode('.', $champ['correspondance']);

                $element = $champ_info[0];

                $nom_sql = $champ_info[1];

                $champ['champ_erp'] = $nom_sql;
                $champ['type_element'] = $import_sur_mesure->element_type_element($element);
                $champ['champ_libre'] = champ_libre_modele($element,$nom_sql);;

                $cles_mise_a_jour_par_type_element[$element][] = $champ;
            }
        }

        $collection = $this->fichier_to_array();

        $etat_post_import = $this->calcul_etat_post_import($champs,$collection);

        if($verification_des_cles == false)
           return array(
                'succes' => true,
                'import_en_cours_id' => $this->modele->id,
                'etat_post_import' => $etat_post_import
           );

        $extension = explode('.',$this->modele->chemin_acces)[1];

        $combinaisons_cles = [];

        // On parcourt les valeurs du fichier
        foreach ($collection as $index_ligne => $ligne) {

            //On parcourt les clés et on crée une combinaison afin de vérifier si la combinaison est unique
            foreach ($cles_mise_a_jour_par_type_element as $type_element => $champs_cles_mise_a_jour) {

                if(empty($champs_cles_mise_a_jour))
                    continue;

                $combinaison = '';

                foreach ($champs_cles_mise_a_jour as $champ_cle_mise_a_jour) {

                    $champ_import = $champ_cle_mise_a_jour['champ_import'];

                    if($type_element == $import_sur_mesure->modele->type_element && empty($ligne[$champ_import]))
                        return array('succes' => false, 'message' => "La colonne ".$champ_import." ne peut avoir une donnée vide car c'est une clé de mise à jour !");

                    $valeur = $ligne[$champ_import];

                    $champ_libre = $champ_cle_mise_a_jour['champ_libre'];

                    if(!empty($champ_libre['type']) && in_array($champ_libre['type'],array(4,5)))
                        $valeur = $this->convertisseur_date($valeur,$champ_libre,$extension);
                    //On traite les correspondances pour les listes formatées, les listes libres et les champs de recherche ajax
                    else if ($champ_cle_mise_a_jour['correspondances_valeurs'] == 'true' && isset($champ_cle_mise_a_jour['correspondances_valeurs_liste'])) {

                        foreach ($champ_cle_mise_a_jour['correspondances_valeurs_liste'] as $correspondance_valeur_liste) {

                            if ($correspondance_valeur_liste['valeur'] != $valeur ||
                                !isset($correspondance_valeur_liste[$type_element]) ||
                                empty($correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']])
                            )
                                continue;

                            $valeur = $correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']];

                            if (is_array($valeur))
                                $valeur = array_keys($valeur);
                        }

                    } else if (strpos($valeur,',') != false && isset($champ_libre['type']) && in_array($champ_libre['type'], array(1, 20, 10, 11, 12, 42)))
                        $valeur = implode(',', $valeur);

                    $combinaison .= $valeur;
                }

                if ($type_element != $import_sur_mesure->modele->type_element)
                    $combinaison = $combinaisons_cles[$import_sur_mesure->modele->type_element][($index_ligne + 2)] . $combinaison;

                $combinaisons_cles[$type_element][($index_ligne + 2)] = $combinaison;
            }
        }

        // Si on a besoin de vérification on va vérifier les combinaisons de clés
        $this->comparaison_cles_fichier_bdd($combinaisons_cles, $cles_mise_a_jour_par_type_element, $etat_post_import);

        $this->enregistre([
            'combinaisons_cles' => $combinaisons_cles,
        ]);

        return array(
            'succes' => true,
            'import_en_cours_id' => $this->modele->id,
            'etat_post_import' => $etat_post_import,
        );

    }

    /**
     * @param $combinaisons_cles
     * @param $cles_mise_a_jour_par_type_element
     * @return bool|string
     *
     * Permet de calculer les états post import et notamment les ajouts et les mises à jours
     *
     */
    public function comparaison_cles_fichier_bdd($combinaisons_cles,$cles_mise_a_jour_par_type_element, &$etat_post_import){

        $import_sur_mesure = $this->import_sur_mesure();

        $concat_type_element_principal = '';

        $champs_libres_jointure = Champ_libre::where('type_element_ajax',$import_sur_mesure->modele->type_element)
            ->whereIn('type_element',$import_sur_mesure->tables_jointes_types_element())
            ->get()->pluck('nom_sql','type_element')->toArray();

        //Pour chaque element on vérifie qu'il n'y a pas de doublon
        foreach($cles_mise_a_jour_par_type_element as $type_element => $cles_mise_a_jour) {

            $champs_erp = [];

            foreach($cles_mise_a_jour as $champ){
                $champs_erp[] = $champ['champ_erp'];
            }

            //On construit un concat des combinaisons de champ qui constitue les clés
            $concat = 'COALESCE('.$type_element.'.'.implode(',""),COALESCE('.$type_element.'.',$champs_erp).', "")';

            if($import_sur_mesure->modele->type_element == $type_element)
                $concat_type_element_principal = $concat;
            else
                $concat = $concat_type_element_principal.','.$concat;

            $elements_mise_a_jour = modele($type_element)
                ->select(DB::raw('CONCAT('.$concat.') as valeur_cle'))
                ->whereIn(DB::raw('CONCAT('.$concat.')'), array_unique($combinaisons_cles[$type_element]))
                ->groupBy(DB::raw('CONCAT('.$concat.')'));

            if($type_element != $import_sur_mesure->modele->type_element)
                $elements_mise_a_jour->join($import_sur_mesure->modele->type_element, $import_sur_mesure->modele->type_element . '.id', $type_element . '.' . $champs_libres_jointure[$type_element]);

            $elements_mise_a_jour = $elements_mise_a_jour->get()->pluck('valeur_cle')->toArray();

            $combinaisons_sans_caracteres_speciaux = array_map('retraite_caracteres_speciaux',$combinaisons_cles[$type_element]);

            $compte_cles = array_count_values($combinaisons_sans_caracteres_speciaux);

            $etat_post_import['par_type_element'][$type_element]['nombres_mises_a_jour'] = 0;

            foreach($elements_mise_a_jour as $element_mise_a_jour) {

                $element_mise_a_jour = retraite_caracteres_speciaux($element_mise_a_jour);

                if(!isset($compte_cles[$element_mise_a_jour]))
                    continue;

                $nombre_mise_a_jour = $compte_cles[$element_mise_a_jour];

                $etat_post_import['par_type_element'][$type_element]['nombres_mises_a_jour'] += $nombre_mise_a_jour;
                $etat_post_import['par_type_element'][$type_element]['nombres_ajoutes'] -= $nombre_mise_a_jour;

                unset($compte_cles[$element_mise_a_jour]);

            }

            foreach($compte_cles as $cle => $valeur){

                if($valeur > 1)
                    $etat_post_import['par_type_element'][$type_element]['nombres_ajoutes'] -= ($valeur-1);
            }
        }

        return true;

    }

    /**
     * @param $champs
     * @param $collection
     *
     * Permet de calculer le nombres d'éléments dans le fichier et le nombre qui va être ajouté par type élement
     *
     */
    public function calcul_etat_post_import($champs,$collection){

        $etat_post_import = [
            'nombre_lignes' => sizeof($collection),
            'par_type_element'
        ];

        //On parcourt les éléments de la collection et on regarde si il y a des données qui vont être importés
        foreach($collection as $ligne){

            $type_element_verification_ajout = [];

            foreach($champs as $champ){

                if (isset($champ['champ_import_parent']))
                    $champ_import = $champ['champ_import_parent'];
                else
                    $champ_import = $champ['champ_import'];

                $champ_info = explode('.', $champ['correspondance']);

                $type_element = $champ_info[0];

                if($type_element == 'aucune_correspondance')
                    continue;

                if(!in_array($type_element,$type_element_verification_ajout) && !empty($ligne[$champ_import])){

                    if(!isset($etat_post_import['par_type_element'][$type_element]['nombres_ajoutes']))
                        $etat_post_import['par_type_element'][$type_element]['nombres_ajoutes'] = 0;

                    $etat_post_import['par_type_element'][$type_element]['nombres_ajoutes']++;

                    $type_element_verification_ajout[] = $type_element;
                }
            }
        }

        return $etat_post_import;
    }

    /**
     *
     * Permet de récupérer les éléments en bdd concernés par l'import
     *
     */
    public function recuperation_elements_concernes(){

        $elements = [];

        $import_sur_mesure = $this->import_sur_mesure();

        $cles_mises_a_jour = $import_sur_mesure->cles_mises_a_jour();

        $champs_libres_jointure = Champ_libre::where('type_element_ajax',$import_sur_mesure->modele->type_element)
            ->whereIn('type_element',$import_sur_mesure->tables_jointes_types_element())
            ->get();

        $champs_libres_jointure_uniques = $champs_libres_jointure->where('unique',1)
            ->pluck('nom_sql','type_element')
            ->toArray();

        $champs_libres_jointure = $champs_libres_jointure->pluck('nom_sql','type_element')->toArray();

        $requetes_par_element = [];

        //Pour chaque element, on récupére les éléments correspondant
        foreach($cles_mises_a_jour as $element => $cle_mise_a_jour) {

            if(empty($cle_mise_a_jour))
                continue;

            $type_element = $import_sur_mesure->element_type_element($element);

            //On construit un concat des combinaisons de champ qui constitue les clés
            $concat = 'COALESCE('.$type_element.'.'.implode(',""),COALESCE('.$type_element.'.',$cle_mise_a_jour).', "")';

            if($import_sur_mesure->modele->type_element == $type_element)
                $concat_type_element_principal = $concat;
            else
                $concat = $concat_type_element_principal.','.$concat;

            $requete = modele($type_element)
                ->select(
                    DB::raw('CONCAT('.$concat.') AS concat_import_' . $type_element),
                    $type_element.'.*'
                )
                ->whereIn(DB::raw('CONCAT('.$concat.')'), array_unique($this->combinaisons_cles()[$element]))
                ->groupBy('concat_import_' . $type_element);

            if($type_element != $import_sur_mesure->modele->type_element)
                $requete->join($import_sur_mesure->modele->type_element, $import_sur_mesure->modele->type_element . '.id', $type_element . '.' . $champs_libres_jointure[$type_element]);

            $requetes_par_element[$type_element] = $requete;

            //On récupére les id des éléménts en fonction des combinaisons
            $elements[$element] = $requete->get()->keyBy('concat_import_' . $type_element)->map(function($element) use ($type_element){
                unset($element->{'concat_import_' . $type_element});
                return $element;
            });

        }

        // Gestion d'un cas particulier si une table jointe a son champ de liaison avec la table principale en unique
        if(!empty($champs_libres_jointure_uniques) && !empty($cles_mises_a_jour[$import_sur_mesure->modele->type_element])){

            foreach($champs_libres_jointure_uniques as $type_element => $champ){

                if(!empty($cles_mises_a_jour[$type_element]))
                    continue;

                $requete = clone $requetes_par_element[$import_sur_mesure->modele->type_element];

                unset($requete->getQuery()->columns[1]);

                $requete->addSelect($type_element.'.*')
                    ->join($type_element,$import_sur_mesure->modele->type_element.'.id',$type_element.'.'.$champ);

                $elements[$type_element] = $requete->get()->keyBy('concat_import_' . $import_sur_mesure->modele->type_element)->map(function($element) use ($import_sur_mesure){
                    unset($element->{'concat_import_' . $import_sur_mesure->modele->type_element});
                    return $element;
                });
            }
        }

        return $elements;
    }

    public function fichier_to_array(){

        $extension = explode('.',$this->modele->chemin_acces)[1];

        if($extension == 'csv'){
            $reader = new CSVReader();
        }
        else{
            $options = new Options();
            $options->SHOULD_FORMAT_DATES = true; 

            $reader = new XLSXReader($options);
        }

        $reader->open(storage_path('app/public/import_sur_mesure/' . $this->modele->chemin_acces));

        $sheet = $reader->getSheetIterator()->current();

        $entetes = [];

        foreach ($sheet->getRowIterator() as $index_row => $row) {

            if($index_row == 1){

                foreach ($row->getCells() as $cell) {
                    $entetes[] = retraite_caracteres_speciaux($cell->getValue(),'_');
                }

                continue;
            }
            
            $ligne = [];

            foreach ($row->getCells() as $index => $cell) {

                if(!isset($entetes[$index]))
                    continue;
                
                $ligne[$entetes[$index]] = $cell->getValue();
            }

            $results[] = $ligne;
        }

        $reader->close();

        if($this->champs === false)
            $this->champs = $entetes;

        return $results;
    }

    /**
     *
     * Permet de jouer l'import
     *
     * Cette fonction est joué en tâche cron
     *
     */
    public function import(){

        define('import_en_cours',true);

        if($this->modele->statut == 1)
            return 'Import déjà en cours';

        $this->retire_donnees_preparees($this->modele);

        //On récupére les éléments nécessaires pour l'import
        $import_sur_mesure = $this->import_sur_mesure();

        $elements = $import_sur_mesure->elements();

        $champs_par_element = $import_sur_mesure->champs_par_element();

        $valeurs_par_defaut_par_type_element = $import_sur_mesure->valeurs_par_defaut();

        $erreurs = $this->erreurs();

        $elements_concernes = $this->recuperation_elements_concernes();

        $champs_libres_jointure = Champ_libre::where('type_element_ajax',$import_sur_mesure->modele->type_element)
            ->whereIn('type_element',$import_sur_mesure->tables_jointes_types_element())
            ->get();

        $champs_libres_jointure = $champs_libres_jointure->pluck('nom_sql','type_element')->toArray();

        $cles_mises_a_jour = $import_sur_mesure->cles_mises_a_jour_import($champs_par_element);

        //On récupére les valeurs du fichier
        $lignes = $this->fichier_to_array();

        $extension = explode('.',$this->modele->chemin_acces)[1];

        //On indique que l'import est en cours
        $this->modele->statut = 1;
        $this->modele->nombres_a_importer = sizeof($lignes);
        $this->modele->save();

        // On gére un fichier de compte rendu où on y ajoute les lignes en erreurs
        $fichier_compte_rendu = storage_path('app/public/import_sur_mesure/compte_rendu_import_'.$this->modele->id .'.csv');

        // On met en place un compte rendu qui va permettre de lister les lignes qui n'ont pas réussi à être importées
        $compte_rendu_existe = file_exists($fichier_compte_rendu);

        $compte_rendu = fopen($fichier_compte_rendu, "a");

        if(!$compte_rendu_existe)
            fputcsv($compte_rendu, $this->champs_entete_fichier());

        // On import 500 lignes par 500 lignes
        $nombres_deja_importes = empty($this->modele->nombres_importes) ? 0 : $this->modele->nombres_importes;
        $nombres_fin_import =  $this->nombre_a_traiter_par_execution;

        if($nombres_deja_importes + $this->nombre_a_traiter_par_execution > $this->modele->nombres_a_importer)
            $nombres_fin_import = $this->modele->nombres_a_importer - $nombres_deja_importes;

        $lignes = array_slice($lignes,$nombres_deja_importes,$nombres_fin_import);

        $nombre_importes = 0;

        // Pour les champs multiselections on ne récupére que les ids des valeurs
        foreach($valeurs_par_defaut_par_type_element as &$valeurs_par_defaut) {

            foreach($valeurs_par_defaut as &$valeur_par_defaut){

                if (is_array($valeur_par_defaut))
                    $valeur_par_defaut = array_keys($valeur_par_defaut);
            }
        }

        //On parcourt chaque lignes
        foreach ($lignes as $index_ligne => $ligne) {

            $ids_elements = [];

            $parent_valide = true;

            $combinaison_principale = '';

            //On parcourt chaque éléments
            foreach ($elements as $element => $type_element) {

                if($parent_valide == false)
                    continue;

                $management_element = null;

                //On vérifie que la combinaison est comprise dans les elements concernes
                if (isset($cles_mises_a_jour[$element]) && !empty($cles_mises_a_jour[$element])) {

                    $combinaison = '';

                    foreach ($cles_mises_a_jour[$element] as $champ) {

                        $champ_import = $champ['champ_import'];

                        $champ_libre = $champ['champ_libre'];

                        $valeur = $ligne[$champ_import];

                        if(!empty($champ_libre['type']) && in_array($champ_libre['type'],array(4,5)))
                            $valeur = $this->convertisseur_date($valeur,$champ['champ_libre'],$extension);
                        //On traite les correspondances pour les listes formatées, les listes libres et les champs de recherche ajax
                        else if ($champ['correspondances_valeurs'] === true && isset($champ['correspondances_valeurs_liste'])) {

                            foreach ($champ['correspondances_valeurs_liste'] as $correspondance_valeur_liste) {

                                if ($correspondance_valeur_liste['valeur'] != $valeur ||
                                    !isset($correspondance_valeur_liste[$type_element]) ||
                                    empty($correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']])
                                )
                                    continue;

                                $valeur = $correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']];

                                if (is_array($valeur))
                                    $valeur = array_keys($valeur);
                            }

                        } else if (strpos($valeur,',') != false && isset($champ_libre['type']) && in_array($champ_libre['type'], array(1, 20, 10, 11, 12, 42))) {
                            $valeur = explode(',', $valeur);
                        }

                        $combinaison .= $valeur;
                    }

                    if ($element == $import_sur_mesure->modele->type_element)
                        $combinaison_principale = $combinaison;
                    else
                        $combinaison = $combinaison_principale . $combinaison;

                    //Si on trouve une correspondance on récupére le management de l'élément
                    if (isset($elements_concernes[$element][$combinaison])) {
                        $management_element = management($type_element, $elements_concernes[$element][$combinaison]->id, $elements_concernes[$element][$combinaison]);
                        if($import_sur_mesure->modele->type_element == $element)
                            $ids_elements[$import_sur_mesure->modele->type_element] = $management_element->modele->id;

                    }
                }
                else if(isset($elements_concernes[$element][$combinaison_principale]))
                    $management_element = management($type_element, $elements_concernes[$element][$combinaison_principale]->id, $elements_concernes[$element][$combinaison_principale]);

                if (empty($management_element))
                    $management_element = management($type_element);

                $informations = [];

                // Les informations comprennent les valeurs par défaut initialement
                if(!isset($management_element->modele) && isset($valeurs_par_defaut_par_type_element[$element]))
                    $informations = $valeurs_par_defaut_par_type_element[$element];

                if(isset($champs_par_element[$element])) {
                    foreach ($champs_par_element[$element] as $champ) {

                        $champ_libre = $champ['champ_libre'];

                        if(!empty($champ['cle_cree'])){

                            if(!empty($ids_elements[$champ['table_id']]))
                                $informations[$champ_libre['nom_sql']] = $ids_elements[$champ['table_id']];

                            continue;
                        }

                        //Si le modéle est défini et que l'on ne doit pas mettre à jour la valeur on continue
                        if ((isset($management_element->modele) && $champ['mettre_a_jour'] === false))
                            continue;

                        // On récupére le champ import dans le fichier
                        if (isset($champ['champ_import_parent']))
                            $champ_import = $champ['champ_import_parent'];
                        else
                            $champ_import = $champ['champ_import'];

                        $valeur = $ligne[$champ_import];

                        if (!empty($valeur)) {

                            //On traite le formatage pour les dates
                            if(!empty($champ_libre['type']) && in_array($champ_libre['type'],array(4,5)))
                                $valeur = $this->convertisseur_date($valeur,$champ['champ_libre'],$extension);
                            //On traite les correspondances pour les listes formatées, les listes libres et les champs de recherche ajax
                            else if ($champ['correspondances_valeurs'] === true && isset($champ['correspondances_valeurs_liste'])) {

                                foreach ($champ['correspondances_valeurs_liste'] as $correspondance_valeur_liste) {

                                    if ($correspondance_valeur_liste['valeur'] != $valeur ||
                                        !isset($correspondance_valeur_liste[$type_element]) ||
                                        empty($correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']])
                                    )
                                        continue;

                                    $valeur = $correspondance_valeur_liste[$type_element][$champ_libre['nom_sql']];

                                    if (is_array($valeur))
                                        $valeur = array_keys($valeur);
                                }

                            } elseif (strpos($valeur,',') != false && isset($champ_libre['type']) && in_array($champ_libre['type'], array(1, 20, 10, 11, 12, 42))) {
                                $valeur = explode(',', $valeur);
                            }

                            $informations[$champ_libre['nom_sql']] = $valeur;
                        }
                    }
                }

                if(empty($informations))
                    continue;

                // Si c'est une table jointe, on applique la valeur du parent
                if($import_sur_mesure->modele->type_element != $element && !empty($ids_elements[$import_sur_mesure->modele->type_element]) && !empty($champs_libres_jointure[$type_element]))
                    $informations[$champs_libres_jointure[$type_element]] = $ids_elements[$import_sur_mesure->modele->type_element];

                try {
                    $retour = $management_element->enregistre($informations);
                }
                catch(\Exception $e){
                    $retour = 'Erreur inconnu';
                    Log::info("Erreur inconnu lors de l'import ".$this->modele->id." sur le type élément ".$type_element.'
                     avec les informations suivantes : '. json_encode($informations).' '.$e);
                }

                // Si il y a une erreur lors de l'enregistrement on l'enregistre en bdd et dans un fichier
                if($retour !== true) {
                    if($import_sur_mesure->modele->type_element == $element)
                        $parent_valide = false;

                    $erreurs[$retour][] = $index_ligne+$nombres_deja_importes;
                    fputcsv($compte_rendu, $ligne);
                }

                if(isset($management_element->modele->id)) {
                    $ids_elements[$element] = $management_element->modele->id;

                    if(isset($combinaison))
                        $elements_concernes[$element][$combinaison] = $management_element->modele;
                }

            }

            $nombre_importes++;

            // Tout les 50 lignes, on sauvegarde le nombre importés pour simplifier le suivi de l'import
            if($nombre_importes % 50 == 0 || ($nombre_importes + $nombres_deja_importes) == $this->modele->nombres_a_importer) {
                $this->modele->nombres_importes = $nombre_importes + $nombres_deja_importes;
                $this->modele->save();
            }

        }

        fclose($compte_rendu);

        $modele = modele('import_en_cours', $this->modele->id);

        // Si l'import n'a pas été arrêté on enregistre que l'import doit continuer ou si il est fini
        if($modele->statut != 4) {
            if ($this->modele->nombres_importes >= $this->modele->nombres_a_importer) {

                $this->modele->statut = 3;

                // On sauvegarde que le modèle d'import sur mesure peut être utilisé
                $import_sur_mesure->enregistre(array('complet' => 1));
                $retour = $this->envoie_notifications();
            }
            else
                $this->modele->statut = 2;
        }

        $this->modele->erreurs = json_encode($erreurs);
        $this->modele->save();

        if(isset($retour))
            return $retour;
        
        return true;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        if(in_array('dupliquer',$liste_options))
            unset($liste_options[array_search('dupliquer',$liste_options)]);

        return $liste_options;
    }

    /**
     * @param $modele
     * @return mixed
     *
     * Permet de rendre disponible l'import sur mesure
     *
     */
    public function methodes_post_suppression($modele){

        $import_sur_mesure = $this->import_sur_mesure();

        $import_sur_mesure->enregistre(array('complet' => 1));

        return parent::methodes_post_suppression($modele);
    }

    /**
     *
     * Permet d'envoyer les notifications de fin d'import
     *
     */
    public function envoie_notifications(){

        $champ_libre = $this->champ('types_notifications')->modele;
        $valeurs =  \DB::table($champ_libre->table_pivot)->where('cle_locale', $this->modele->id)->get();

        $import_sur_mesure = $this->import_sur_mesure();

        $types_notifications = [];

        foreach($valeurs as $valeur) {

            $types_notifications[] = $valeur->valeur;
        }

        // Notification par notification ERP
        if(in_array(1,$types_notifications)){

            $message_notification = "L'import ".$import_sur_mesure->champ('titre')->affiche()." est terminé, cliquez ici pour avoir le résultat de l'import : <a href='".URL::to('eden/import_sur_mesure/')."/".$this->modele->id."'> Résultat import </a>";

            $notification = management('notification');

            $infos = array(

                'date' => date('Y-m-d H:i:s'),
                'utilisateur_id' => $this->modele->modifie_par,
                'zone' => 'navbar_notifications',
                'contenu_html' => $message_notification,
            );

            $notification->enregistre($infos);

        }

        // Notification par email
        if(in_array(2,$types_notifications)){

            $utilisateur = modele('utilisateur',$this->modele->modifie_par);

            if($utilisateur !== null) {

                $sujet = "Fin de l'import " . $import_sur_mesure->champ('titre')->affiche();
                $contenu_email = "L'import " . $import_sur_mesure->champ('titre')->affiche() . " est terminé, cliquez ici pour avoir le résultat de l'import : <a href='" . URL::to('eden/import_sur_mesure/') . "/" . $this->modele->id . "'> Résultat import </a>";

                // on prépare les données pour envoyer le mail
                $service_email = service('email');

                $parametres_email = [
                    'type_configuration' => 1,
                    'destinataire' => [$utilisateur->email],
                    'sujet' => $sujet,
                ];

                $variables_email = [
                    'contenu_email' => $contenu_email,
                    'titre' => $sujet
                ];

                return $service_email->envoyer('eden::mails.template_standard', $variables_email, $parametres_email);
            }
        }
    }

    public function convertisseur_date($valeur,$champ_libre,$extension){

        try{
            $format_date = extraite_format_date($valeur);

            if(empty($format_date))
                $valeur = null;
            else{
                $date = \DateTime::createFromFormat($format_date, $valeur);

                if ($champ_libre['type'] == 4)
                    $valeur = $date->format('Y-m-d');
                else
                    $valeur = $date->format('Y-m-d H:i:s');
            }
        }catch(\Exception | \Throwable $e){
            $valeur = null;
        }

        return $valeur;
    }
}