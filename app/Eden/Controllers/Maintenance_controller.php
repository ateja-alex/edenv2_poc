<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cache_management;
use App\Http\Controllers\Controller;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Rapport_parametre;
use App\Eden\Models\Utilisateur;
use App\Eden\Models\Element_piece_jointe;

use App\Eden\Variables;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Script_management;

use DB;
use Mail;
use Log;

class Maintenance_controller extends Controller {

	/**
	 *
	 * Affiche les différentes options de maintenance
	 *
	 */
    public function index() {

		return view('eden::maintenance.index');
    }

	/**
	 *
	 * Affiche une page vide pour mesurer le temps / la perf
	 *
	 */
	public function temps() {
		return view('eden::test_temps');
	}

	public function cache() {

		return view('eden::parametrage.cache');
	}

	public function cache_donnees() {

		$debut = microtime(true);

		$donnees = array(
			'partage'  => Cache_management::inspection_partage(),
			'sessions' => Cache_management::inspection_sessions(),
			'disque'   => Cache_management::inspection_disque(),
			'etat'     => Cache_management::inspection_etat(),
			'sondes'   => Cache_management::inspection_nb_sondes(),
		);

		$donnees['duree'] = round((microtime(true) - $debut) * 1000);

		return response()->json($donnees);
	}

	public function cache_espace() {

		return response()->json(Cache_management::inspection_espace(request()->espace));
	}

	public function cache_vider() {

		$portee = request()->portee;

		if($portee === 'espace')
			Cache_management::partage_oublie_espace(request()->espace);

		elseif($portee === 'partage')
			Cache_management::partage_vide_tout();

		elseif($portee === 'sessions')
			Cache_management::invalide();

		else
			Cache_management::vider_tout();

		return response()->json(array('success' => true, 'portee' => $portee));
	}

	/**
	 *
	 * Crée les cookies pour activer le debug
	 *
	 */
    public function debug() {

		setcookie('laravel_app_debug_easydev', 1, strtotime('now +1 month'),'/eden/');

		$contenu = file_get_contents('../config/app.php');

		if(strpos($contenu, 'laravel_app_debug_easydev') === false)
			dd('Attention, il semblerait que le config/app ne soit pas configuré pour activer le débug selon les cookies ! Voir le fichier config/app.php');

		dd("ok !");
    }

	/**
	 *
	 * Mise à jour des champs libres et tables libres
	 *
	 * Concrètement cette fonction reprend tous les champs libres et tables libres standards, et vérifie qu'ils sont créés dans le projet
	 * S'ils ne sont pas créés, on les crée via les paramètres standards,
	 * s'ils sont créés (on vérifie nom_sql et type_element pour champs libres, type_element pour tables libres, on ne touche à rien)
	 *
	 */
	public function migrations() {

		return view('eden::parametrage.migrations');
	}
    
    //gestion des migrations
    public function migrations_post() {
        
        $fonction = request()->fonction ?? null;
        
        define("migration_en_cours", true);
		temps_execution('debut migration');

		try {
			Cache_management::vider_tout();

			Maintenance_management::$fonction();
		}
		catch(\Exception | \Throwable $erreur){
			return response()->json(['success' => false, 
				'erreur' => [
					'message' => $erreur->getMessage() ?: class_basename($erreur), 
					'ligne' => $erreur->getLine(), 
					'fichier' => $erreur->getFile(),
					'stacktrace' => $erreur->getTraceAsString()
				]
			]); 
		}

        return response()->json(['success' => true]);

    }

	/**
	 *
	 * Défini toutes les colonnes de toutes les tables en nullable
	 *
	 */
    public function colonnes_null() {
		$variable = 'Tables_in_'.env('DB_DATABASE');

		foreach(DB::select('SHOW TABLES') as $table) {
			$colonnes = DB::select('SHOW COLUMNS FROM '.$table->$variable);

			$colonne_precedente = false;

			foreach($colonnes as $colonne) {

				if($colonne->Key == 'PRI') {

					$colonne_precedente = $colonne->Field;
					continue;
				}

				if($colonne_precedente === false)
					DB::select('ALTER TABLE `'.$table->$variable.'` CHANGE `'.$colonne->Field.'` `'.$colonne->Field.'` '.$colonne->Type.'  COLLATE utf8_unicode_ci NULL;');
				else
					DB::select('ALTER TABLE `'.$table->$variable.'` CHANGE `'.$colonne->Field.'` `'.$colonne->Field.'` '.$colonne->Type.'  COLLATE utf8_unicode_ci NULL AFTER `'.$colonne_precedente.'`;');

				$colonne_precedente = $colonne->Field;
			}

			// on change les collation de toutes les tables
			DB::select('ALTER TABLE `'.$table->$variable.'` COLLATE utf8_unicode_ci');
		}

        return response()->json(true);
	}

	/**
	 *
	 * Rempli les valeurs de chaine_tags_ajax_recherche (après un import par exemple, ou une mise à jour)
	 *
	 */
    public function maj_chaine_tags_ajax($type_element) {

        $retour = management($type_element)->maj_index_recherche();

		return response()->json(['retour' => $retour]);
	}

	/**
	 *
	 * Synchronise les tickets de suivi_recette
	 *
	 */
    public function synchro_suivi_recette() {

		$suivi_recette_easydev = modele('suivi_recette_easydev')->get();

		foreach($suivi_recette_easydev as $sred) {
			management('suivi_recette_easydev', $sred->id, $sred)->enregistre([]);
		}

		$suivi_recette_easydev = modele('suivi_recette_easydev_echange')->get();

		foreach($suivi_recette_easydev as $sred) {
			management('suivi_recette_easydev_echange', $sred->id, $sred)->enregistre([]);
		}
	}

     /**
	 *
	 * On transforme une array en string pour importer les data sur un fichier
	 *
	 */
    public function array_to_string($array,$retour,$type,$indentation = 1) {

    	foreach ($array as $clef => $valeur) {

    		for ($i=0; $i < $indentation; $i++) {
    			$retour .= "\t";
    		}

    		$retour .= "'".$clef."' => ";

    		if (is_array($valeur)){

    			$retour .= "[\n";
    			$retour = $this->array_to_string($valeur,$retour,$type,$indentation+1);
	    		for ($i=0; $i < $indentation; $i++) {
	    			$retour .= "\t";
	    		}
    			$retour .= "],\n";
    		}

    		else {
    			if ($valeur !== true && $valeur !== false)
    				$valeur = str_replace('"', '\"', $valeur);

    			if ($type == "parametrage") {



    					if (is_numeric($valeur) && $valeur !== false && $valeur !== true) {
    						$retour .= $valeur.",\n";
    					}
    					elseif ($valeur === false && $valeur !== true && !is_numeric($valeur)) {
    						$retour .= "false,\n";

    					}
    					elseif ($valeur !== false && $valeur === true && !is_numeric($valeur)){
    						$retour .= "true,\n";

    					}
    					else
    						$retour .= '"'.$valeur.'",'."\n";

    			}
    			else{
    				$retour .= "\"".$valeur."\",\n";
    			}
    		}

    	}

    	return $retour;
    }

	/**
	 *
	 * Tests erreurs 500
	 *
	 */
	public function test_erreur_500($type_test = 'page') {

		$urls 		= service('url_test_erreur')->retourne_url_a_tester();
		$elements 	= service('url_test_erreur')->retourne_elements_a_tester();
		$features 	= service('url_test_erreur')->retourne_feature_a_tester();

		$tous 		= $urls->concat($elements)->concat($features);

		if($type_test == 'page')
			$tous = $urls;

		if($type_test == 'element')
			$tous = $elements;

		if($type_test == 'feature')
			$tous = $features;

		// $tous 	= service('url_test_erreur')->retourne_elements_a_tester();
		// $tous = collect(['http://aana-preprod.easydev.run/eden/test/test_element/bl_achat']);

		return view('eden::maintenance.test_erreur_500', [ 'urls_a_tester' => $tous ]);

		//Maintenance_management::test_urls();
	}

	/**
	 *
	 * Route appelée en ajax pour vérifier si une url renvoit une erreur 500
	 *
	 */
	public function test_url_erreur_500() {

		return Maintenance_management::test_url(request()->url);
	}

	/**
	 *
	 * Affiche les paramètres de la config
	 *
	 */
	public function affiche_config() {

		dd(config());
	}

	/**
	 *
	 * L'objectif de cette fonction est d'essayer de récupérer automatiquement
	 * les id chez microsoft des utilisateurs existants dans Eden,
	 * elle doit être appelée notamment lorsqu'on vient d'installer microsoft
	 * et qu'il y a déjà une liste d'utilisateurs
	 *
	 */
	public function recupere_id_utilisateur_microsoft_pour_utilisateurs_existants(){

		// Récupère le token grâce à Graph
		$graph = service('microsoft_authentification')->instancie_graph_application();

		// Récupération des infos voulues
		$utilisateurs_microsoft = $graph->createRequest('GET', '/users')
				->setReturnType(Model\User::class)
				->execute();

		foreach($utilisateurs_microsoft as $utilisateur_microsoft){

			// on vérifie si l'utilisateur est déjà existant en base ?
			$utilisateur_en_base = modele('utilisateur')->where('email', $utilisateur_microsoft->getMail())->first();

			// il n'y a pas d'utilisateur avec cet email
			if(empty($utilisateur_en_base))
				continue;

			// il y a bien un uitlisateur mais il a déjà un id microsoft
			if(!empty(modele('utilisateur',$utilisateur_en_base['id'])->id_microsoft))
				continue;

			// on enregistre son ID microsoft
			$management_utilisateur = management('utilisateur', $utilisateur_en_base['id']);
			$management_utilisateur->enregistre_modele(array('id_microsoft' => $utilisateur_microsoft->getId()));
		}
	}

    /*
	 *
	 * Transfère tous les dossiers et fichiers Eden existants vers Sharepoint
	 *
	 */
    public function transfert_bibliotheque_eden_a_sharepoint($dossier_choisi = false) {

        $verification_graph = service('microsoft_authentification')->instancie_graph_application();

        if(!config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation'))
            return 'La synchronisation avec Sharepoint n\'a pas été activée';

        if(empty($verification_graph))
            return 'La connexion Microsoft a été mal paramétrée';

        //S'il n'y a pas de dossier choisi on retourne le dossier racine sinon on retourne le dossier choisi
        if(empty($dossier_choisi)){

            $dossiers = modele('dossier_bibliotheque')->whereNull('dossier_parent')->whereNull('id_microsoft')->get();
            $fichiers = modele('fichier_bibliothèque')->whereNull('dossier_parent')->whereNull('id_microsoft')->get();
        }
        else{

            $dossiers = modele('dossier_bibliotheque')->where('dossier_parent', $dossier_choisi->id)->whereNull('id_microsoft')->get();
            $fichiers = modele('fichier_bibliothèque')->where('dossier_parent', $dossier_choisi->id)->whereNull('id_microsoft')->get();
        }

        //On crée chaque fichier dans le dossier cible
        foreach($fichiers as $fichier){

            $fichier_a_copier = file_get_contents(storage_path('app/public/'. $fichier->chemin));

            if(empty($dossier_choisi))
                $retour_creation_fichier = service('microsoft_sharepoint')->creer_fichier($fichier->nom_original, $fichier_a_copier);
            else {
                $dossier_parent = modele('dossier_bibliotheque', $dossier_choisi->id);
                $retour_creation_fichier = service('microsoft_sharepoint')->creer_fichier($fichier->nom_original, $fichier_a_copier, $dossier_parent->id_microsoft);
            }
            if(empty($retour_creation_fichier))
                return 'Erreur pour le fichier' . $fichier->id . ' ' .$retour_creation_fichier;

            $fichier_management = management('fichier_bibliotheque', $fichier->id);
            $fichier_management->enregistre(['id_microsoft' => $retour_creation_fichier->id]);
        }

        foreach($dossiers as $dossier_enfant) {

            //On crée les dossiers dans le dossier choisi dans Sharepoint
            if (empty($dossier_choisi))
                $retour_creation_dossier = service('microsoft_sharepoint')->creer_dossier($dossier_enfant->nom);
            else {

                //On récupère le dossier parent avec les infos actualisées (sinon l'id microsoft n'est pas actualisé
                $dossier_parent = modele('dossier_bibliotheque', $dossier_choisi->id);
                $retour_creation_dossier = service('microsoft_sharepoint')->creer_dossier($dossier_enfant->nom, $dossier_parent->id_microsoft);
            }

            if (empty($retour_creation_dossier))
                return 'Erreur pour le dossier' . $dossier_enfant->id . ' ' . $retour_creation_dossier;

            $dossier_management = management('dossier_bibliotheque', $dossier_enfant->id);
            $dossier_management->enregistre(['id_microsoft' => $retour_creation_dossier->getId()]);

            //On récupère les dossiers et fichiers enfants
            $dossiers_dans_dossier_en_cours = modele('dossier_bibliotheque')
                ->where('dossier_parent', $dossier_enfant->id)
                ->whereNull('id_microsoft')
                ->get();

            $fichiers_dans_dossier_en_cours = modele('fichier_bibliotheque')
                ->where('dossier_parent', $dossier_enfant->id)
                ->whereNull('id_microsoft')
                ->get();

            //S'il y a des dossiers ou des fichiers enfants on rappelle la fonction avec le dossier choisi
            if ($dossiers_dans_dossier_en_cours->isNotEmpty())
                $liste_dossiers = $this->transfert_bibliotheque_eden_a_sharepoint($dossier_enfant);
            else if ($fichiers_dans_dossier_en_cours->isNotEmpty())
                $liste_dossiers = $this->transfert_bibliotheque_eden_a_sharepoint($dossier_enfant);
        }

        return 'Tous les dossiers et fichiers ont été traités';
    }

    /*
    *
    * Permet de créer les dossiers de tous les éléments dans Sharepoint
    *
    */
    public function creation_dossiers_elements_sharepoint($type_element_choisi = false) {
        ini_set('memory_limit', '4096M');

        if(empty(moi()->id_microsoft))
            return 'Vous n\'avez pas le droit d\'utiliser Sharepoint sans compte Microsoft';

        //récupération des types_element
        if(empty($type_element_choisi))
            $types_element_existant = modele('parametrage_mappage_sharepoint')->get();
        else
            $types_element_existant = modele('parametrage_mappage_sharepoint')->where('type_element', $type_element_choisi)->get();

        if(empty($types_element_existant))
            return false;

        $sharepoint = service('microsoft_sharepoint');

        foreach($types_element_existant as $cle_type => $type_element){

            $verification_type_element_faite = false;

            //Récupération des éléments du type_element
            $elements_a_creer = modele($type_element->type_element)->get();

            //On vérifie si le type_element est un type de document, ils sont gérés différemment
            $type_document = false;

            $tableau_fournisseurs_traites = [];
            $tableau_clients_traites = [];

            if(strpos($type_element, '_vente'))
                $type_document = 'vente';
            else if(strpos($type_element, '_achat'))
                $type_document = 'achat';

            foreach($elements_a_creer as $cle => $element){

                //Pour le premier dossier on vérifie l'existence du dossier du type_element en plus de le créer
                if($verification_type_element_faite === false || $type_document == 'vente' && !in_array($element->client_id, $tableau_clients_traites) || $type_document == 'achat' && !in_array($element->fournisseur_id, $tableau_fournisseurs_traites)){

                    $retour_element_cree = $sharepoint->verifier_existence_dossiers_elements_fiche($type_element->type_element, $element->id, $type_document);
                    $verification_type_element_faite = true;

                    if($type_document == 'vente')
                        $tableau_clients_traites[] = $element->client_id;
                    else if($type_document == 'achat')
                        $tableau_fournisseurs_traites[] = $element->fournisseur_id;
                }
                else{
                    $retour_element_cree = $sharepoint->verifier_existence_dossiers_elements_fiche($type_element->type_element, $element->id, $type_document, false);
                }

                echo 'Le dossier pour l\'élément ' . $element->chaine_affichage . ' (' . $retour_element_cree . ') a été créé dans Sharepoint <br>';

                //On oublie l'élément pour libérer de la mémoire
                $elements_a_creer->forget($cle);
                unset($element);
            }
            //On oublie le type d'élément pour libérer de la mémoire
            $types_element_existant->forget($cle_type);
        }

        return 'Tous les éléments ont été traités.';
    }

	  /*
	  *
	  *	Permet de récuperer les géolocalisations de toutes les adresses
	  *
	  */
	  public function mise_a_jour_geolocalisations_adresses(){
	  	$adresses = modele('adresse')->where('latitude',null)->where('longitude',null)->get();

	  	foreach ($adresses as $adresse) {

	  		$management_adresse = management('adresse',$adresse->id);

	  		$management_adresse->maj_latitude_longitude();
	  	}

	  	dd('OK !');
	  }
	
    /**
     *
     * Permet de supprimer les filtres des utilisateurs
     *
     */
    public function suppresion_filtres_utilisateurs_rapports(){

        DB::select('TRUNCATE TABLE eden_rapports_parametres');
        DB::select('DELETE FROM eden_parametres WHERE nom = "parametres_planning" OR nom LIKE "parametres_calendrier_%"');

        return response()->json( true);
    }

    /*
     *
     * Permet de récupérer les Crons existants dans les fichiers standard et spécifique,
     * et de les enregistrer en bdd dans la table cron s'ils n'existent pas encore
     *
     */

	public function erreur_struc_table($type_element){
		$data = Maintenance_management::chargement_erreur_struc_table($type_element);

		return view('eden::maintenance.erreur_struc_table')->with("data", $data);
	}

    public function rattrapage_routes(){

		$etats_des_lieux = Maintenance_management::etats_des_lieux_routes_rattrapage();

        return view('eden::maintenance.rattrapage_routes')->with("etats_des_lieux", $etats_des_lieux);
	}

    public function rattrapage_routes_post(){

        $etats_des_lieux = request()->etats_des_lieux;

        foreach($etats_des_lieux as $nom_fichier => $remplacements){

            $remplacements_avant = [];
            $remplacements_apres = [];

            foreach($remplacements as $type => $elements){

                if($type == "url_manuel")
                    continue;

                foreach ($elements as $valeur_avant => $valeur_apres) {

                     if($type == 'name'){

                         $remplacements_avant[] = "route('".$valeur_avant."'";
                         $remplacements_avant[] = 'route("'.$valeur_avant.'"';
                         $remplacements_avant[] = "'route' => array('".$valeur_avant."'";
                         $remplacements_avant[] = "'route' => '".$valeur_avant."'";
                         $remplacements_apres[] = "route('".$valeur_apres."'";
                         $remplacements_apres[] = 'route("'.$valeur_apres.'"';
                         $remplacements_apres[] = "'route' => array('".$valeur_apres."'";
                         $remplacements_apres[] = "'route' => '".$valeur_apres."'";

                     }
                     else{

                         $remplacements_avant[] = "'".$valeur_avant."'";
                         $remplacements_avant[] = '"'.$valeur_avant.'"';
                         $remplacements_apres[] = "'".$valeur_apres."'";
                         $remplacements_apres[] = '"'.$valeur_apres.'"';
                    }
                }
            }

            $fichier = file_get_contents($nom_fichier);

            $fichier = str_replace($remplacements_avant, $remplacements_apres,$fichier);

            //write the entire string
            file_put_contents($nom_fichier, $fichier);
        }

        return response()->json(true);
    }
}
