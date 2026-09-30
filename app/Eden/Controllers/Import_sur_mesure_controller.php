<?php

namespace App\Eden\Controllers;

use App\Eden\Champs\Champ;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Import_sur_mesure_management;
use Illuminate\Http\Request;


class Import_sur_mesure_controller extends Controller {

	/**
	 * 
	 * Permet l'affichage d'un nouvel import ou d'un import existant
	 * 
	 */
    public function afficher($id = null) {

		$tables_libres = Table_libre::select('nom_table','type_element','index_traduction')
            ->where(function($requete){
                $requete->where('table_systeme',null)
                    ->orWhere('table_systeme',0);
            })
            ->orderBy('nom_table')->get()->pluck('nom_table','type_element')->toArray();

        $champs_libres = [];

        $champs_libres_valeurs_par_defaut = array(
            'table_import' => [],
            'tables_jointes' => (object)[],
        );

        if($id !== null) {
            $import_en_cours = management('import_en_cours', $id)->modele_formate();

            $management_import_sur_mesure = management('import_sur_mesure', $import_en_cours->import_sur_mesure);

            $champs_libres = $management_import_sur_mesure->champs_libres();
            
            $import_sur_mesure = $management_import_sur_mesure->modele_formate();

            $champs_libres_valeurs_par_defaut = $management_import_sur_mesure->champs_libres_valeurs_par_defaut($champs_libres);
        }
        else{
            $import_en_cours = modele_par_defaut('import_en_cours');

            $import_sur_mesure = modele_par_defaut('import_sur_mesure');
        }

		return view('eden::import_sur_mesure.index',[
            'tables_libres' => $tables_libres,
            'import_en_cours' => $import_en_cours,
            'import_sur_mesure' => $import_sur_mesure,
            'champs_libres' => $champs_libres,
            'champs_libres_valeurs_par_defaut' => $champs_libres_valeurs_par_defaut,
        ]);
    }


	/**
	 * 
	 * Page de choix des champs libres
	 * 
	 */
    public function traitement_donnee(Request $formulaire) {

        $fichier_import = $formulaire->file('fichier_import');

        $formulaire = $formulaire->all();

        $import_sur_mesure = json_decode($formulaire['import_sur_mesure'],true);
        $import_en_cours = json_decode($formulaire['import_en_cours'],true);

        $nouvel_import_sur_mappage = false;

        $import_en_cours_management = management('import_en_cours');
        $import_sur_mesure_management = management('import_sur_mesure');

        // Si on a un import en cours on recupére le management
        if(isset($import_en_cours['id'])) {
            $import_en_cours_management = management('import_en_cours', $import_en_cours['id']);
        }

        // Si on a un import sur mesure on recupére le management
        if (isset($import_sur_mesure['id'])){
            $nouvel_import_sur_mappage = true;
            $import_sur_mesure_management = management('import_sur_mesure', $import_sur_mesure['id']);
            $import_sur_mesure = $import_sur_mesure_management->modele;
        }

        $type_element = $import_sur_mesure['type_element'];

        if(!isset($import_sur_mesure['titre']) || empty($import_sur_mesure['titre']))
            $import_sur_mesure['titre'] = 'Import'.date('dmY').'_'.$type_element;

        $import_sur_mesure['complet'] = 0;

        //On sauvegarde que l'import sur mesure est utilisé
        $import_sur_mesure_management->enregistre($import_sur_mesure);

        $import_sur_mesure = $import_sur_mesure_management->modele_formate();

        $champs_libres = $import_sur_mesure_management->champs_libres();

        $chemin_acces = 'fichier_a_importer_'.date('YmdHis').'.' . $fichier_import->getClientOriginalExtension();

        $informations = array(
            'import_sur_mesure' => $import_sur_mesure_management->modele->id,
            'date' => date('Y-m-d H:i:s'),
            'statut' => 0,
            'nom_fichier' => $fichier_import->getClientOriginalName(),
            'chemin_acces' => $chemin_acces,
        );

        //On stocke le fichier et on enregistre l'import en cours
        $fichier_import->storeAs('public/import_sur_mesure', $chemin_acces);

        $import_en_cours_management->enregistre($informations);

        // On récupére les valeurs des champs entêtes du fichier
        try {
            $champs_import = $import_en_cours_management->champs_entete_fichier();
        } catch(\Throwable $e) {
            return response()->json(array(
                'succes' => false,
                'message' => traduction('messages.php.import_sur_mesure.erreur_format_fichier'),
            ));
        }

        // Si on utilise un modéle d'import sur mesure, on vérifie que le fichier est comptatible
        if($nouvel_import_sur_mappage) {

            $champs = $import_sur_mesure_management->champs();

            foreach($champs as $champ){

                if(isset($champ['champ_import_parent']) || !empty($champ['cle_cree']))
                    continue;

                if(!in_array($champ['champ_import'],$champs_import)) {
                    return response()->json(array(
                        'succes' => false,
                        'message' => traduction('messages.php.import_sur_mesure.mappage_incompatible'),
                    ));
                }
                else{
                    unset($champs_import[array_search($champ['champ_import'],$champs_import)]);
                }
            }

            foreach ($champs_import as $champ_import) {

                $champs[] = array(
                    'champ_import' => $champ_import,
                    'correspondance' => 'aucune_correspondance',
                    'utilisation_valeur_par_defaut' => true,
                    'cle_mise_a_jour' => false,
                    'mettre_a_jour' => false,
                    'correspondances_valeurs' => false,
                );
            }

            $import_sur_mesure = $import_sur_mesure_management->modele_formate();

        }
        else {

            // On récupére les valeurs par défaut
            $import_sur_mesure->valeurs_par_defaut = $import_sur_mesure_management->modeles_par_defaut();

            $champs = [];

            foreach ($champs_import as $champ_import) {

                $champs[] = array(
                    'champ_import' => $champ_import,
                    'correspondance' => 'aucune_correspondance',
                    'utilisation_valeur_par_defaut' => true,
                    'cle_mise_a_jour' => false,
                    'mettre_a_jour' => false,
                    'correspondances_valeurs' => false,
                );
            }

            $champs[] = array(
                'champ_import' => $type_element.'_id',
                'correspondance' => 'aucune_correspondance',
                'table_cle' => $type_element,
                'cle_cree' => true
            );

            foreach($import_sur_mesure_management->tables_jointes() as $table_jointe){

                $champs[] = array(
                    'champ_import' => $table_jointe['type_element'].'_id',
                    'correspondance' => 'aucune_correspondance',
                    'table_cle' => $table_jointe['type_element'],
                    'table_id' => $table_jointe['id'],
                    'cle_cree' => true
                );
            }

        }

        // On récupére les champs libres qui contiennent des valeurs par défaut
        $champs_libres_valeurs_par_defaut = $import_sur_mesure_management->champs_libres_valeurs_par_defaut($champs_libres);

        $import_sur_mesure->champs = $champs;

        return response()->json(
            array(
                'succes' => true,
                'champs_libres' => $champs_libres,
                'import_sur_mesure' => $import_sur_mesure,
                'import_en_cours' => $import_en_cours_management->modele_formate(),
                'champs_libres_valeurs_par_defaut' => $champs_libres_valeurs_par_defaut,
            )
        );
	
    }

    /**
     * @param Request $formulaire
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de gérer les correspondances
     *
     */
    public function correspondance_valeurs_liste(Request $formulaire){

        $formulaire = $formulaire->all();

        $import_en_cours = $formulaire['import_en_cours'];
        $import_sur_mesure = $formulaire['import_sur_mesure'];

        $management_import_en_cours = management('import_en_cours',$import_en_cours['id']);

        $retour = $management_import_en_cours->correspondance_valeurs_liste($import_sur_mesure['champs']);

        return response()->json($retour);
    }


    /**
     * @param Request $formulaire
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de gérer les clés et les correspondances en même temps par soucis de performance
     *
     */
    public function verifications_cles(Request $formulaire){

        $formulaire = $formulaire->all();

        $import_en_cours = $formulaire['import_en_cours'];
        $import_sur_mesure = $formulaire['import_sur_mesure'];

        $management_import_en_cours = management('import_en_cours',$import_en_cours['id']);

        $retour = $management_import_en_cours->verifications_cles($import_sur_mesure['champs']);

        return response()->json($retour);
    }

    /**
     * @param Request $formulaire
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * On enregistre les champs du modéle d'import
     *
     */
    public function enregistrer_champs_import(Request $formulaire){

        $formulaire = $formulaire->all();

        $import_en_cours = $formulaire['import_en_cours'];
        $import_sur_mesure = $formulaire['import_sur_mesure'];

        if(empty($import_sur_mesure['titre']))
            unset($import_sur_mesure['titre']);

        $management_import_en_cours = management('import_en_cours',$import_en_cours['id']);
        $management_import_sur_mesure = $management_import_en_cours->import_sur_mesure();

        // On formate les valeurs pour ne garder que ce qui est nécessaire pour l'enregistrement
        $champs_erp_concernes = [];

        foreach($import_sur_mesure['champs'] as &$champ){

            if(isset($champ["champ_libre"]))
                unset($champ["champ_libre"]);

            if($champ['correspondance'] != 'aucune_correspondance' && $champ['correspondance'] != null && empty($champ['cle_cree'])){

                $correspondance = explode('.',$champ['correspondance']);

                $element = $correspondance[0];
                $nom_sql = $correspondance[1];

                if($champ['utilisation_valeur_par_defaut'] == 'true')
                    $champs_erp_concernes[$element][] = $nom_sql;
            }
        }

        $champs_libres_valeurs_par_defaut = [];

        if(isset($formulaire['champs_libres_valeurs_par_defaut']))
            $champs_libres_valeurs_par_defaut = $formulaire['champs_libres_valeurs_par_defaut'];

        // On récupére les valeurs par défaut référencés uniquement
        if(isset($champs_libres_valeurs_par_defaut['table_import'])) {
            foreach ($champs_libres_valeurs_par_defaut['table_import'] as &$champ_libre_valeur_par_defaut) {
                $champs_erp_concernes[$champ_libre_valeur_par_defaut['type_element']][] = $champ_libre_valeur_par_defaut['nom_sql'];
            }
        }

        if(isset($champs_libres_valeurs_par_defaut['tables_jointes'])) {
            foreach ($champs_libres_valeurs_par_defaut['tables_jointes'] as $element => &$champs_libres_valeur_par_defaut) {
                foreach($champs_libres_valeur_par_defaut as &$champ_libre_valeur_par_defaut) {
                    $champs_erp_concernes[$element][] = $champ_libre_valeur_par_defaut['nom_sql'];
                }
            }
        }


        $valeurs_par_defaut_par_type_element = $import_sur_mesure['valeurs_par_defaut'];

        $valeurs_par_defaut_par_type_element_formatees = [];

        foreach ($champs_erp_concernes as $element => $champs_concernes) {

            $type_element = $management_import_sur_mesure->element_type_element($element);
            $champs_libres_types = Champ_libre::where('type_element',$type_element)->whereIn('nom_sql',$champs_concernes)->get()->pluck('type','nom_sql')->toArray();

            foreach($champs_concernes as $champ_concerne) {
                if(isset($valeurs_par_defaut_par_type_element[$element][$champ_concerne])) {

                    $valeur = $valeurs_par_defaut_par_type_element[$element][$champ_concerne];

                    // On enléve les valeurs vides pour ne garder que les données nécessaires notamment pour les champs type 10,11,12
                    if(is_array($valeurs_par_defaut_par_type_element[$element][$champ_concerne]))
                        $valeurs_par_defaut_par_type_element_formatees[$element][$champ_concerne] = array_filter($valeur);
                    else {

                        if (in_array($champs_libres_types[$champ_concerne], array(4, 5))) {

                            $date = new \DateTime($valeur);

                            if ($champs_libres_types[$champ_concerne] == 4)
                                $valeur = $date->format('Y-m-d');
                            else
                                $valeur = $date->format('Y-m-d H:i:s');
                        }

                        $valeurs_par_defaut_par_type_element_formatees[$element][$champ_concerne] = $valeur;
                    }
                }
            }
        }

        $import_sur_mesure['valeurs_par_defaut'] = $valeurs_par_defaut_par_type_element_formatees;

        $management_import_en_cours->import_sur_mesure()->enregistre($import_sur_mesure);

        return response()->json($management_import_en_cours->import_sur_mesure()->modele_formate());
    }


	/**
	 * 
	 * On lance l'import pour la première fois
	 * 
	 */
    public function traitement_mise_en_bdd(Request $formulaire) {
        $formulaire = $formulaire->all();

        $import_en_cours = $formulaire['import_en_cours'];

        $management_import_en_cours = management('import_en_cours',$import_en_cours['id']);


        if(isset($import_en_cours['types_notifications'])) {

            $types_notifications = $import_en_cours['types_notifications'];

            $types_notifications_a_enregistrer = array();

            foreach ($types_notifications as $type_notification => $valeur) {

                if ($valeur == 'true')
                    $types_notifications_a_enregistrer[] = $type_notification;
            }

            $management_import_en_cours->enregistre(array('types_notifications' => $types_notifications_a_enregistrer));

        }

        // On sauvegarde que le modéle d'import sur mesure ne peut être utilisé le temps de l'import
        $management_import_en_cours->import_sur_mesure()->enregistre(array(
            'complet' => 0
        ));

        $management_import_en_cours->import();

        return response()->json(true);
    }

}
