<?php

namespace App\Eden\Controllers;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Http\Controllers\Controller;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Elements\Transformation_document;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Rapport_management;
use App\Eden\Managements\Parametrage_management;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Eden\Managements\Parametrage\Menus_management;

use App\Eden\Rapports_libres;


use App\Eden\Variables;
use File;

use Illuminate\Http\Request;

class Parametrage_controller extends Controller {


	/**
	 *
	 * Affiche les différentes options de paramétrage
	 *
	 */
	public function index() {
		return view('eden::parametrage.index');
	}

	/**
	 *
	 * Affiche le détail du versioning
	 *
	 */
	public function versioning() {

		$versions = json_decode(file_get_contents('../app/Eden/Versioning.json'), true);

		return view('eden::parametrage.versioning', array('versions' => $versions));
	}

	/**
	 *
	 * Personnalisation du CSS de l'ERP
	 *
	 */
	public function css($extranet = false) {

		if(\Storage::has('css_specifique_erp.css') && in_array($extranet, ['false', false])) {

			$css = \Storage::get('css_specifique_erp.css');
		}
        else if(\Storage::has('css_specifique_extranet.css') && in_array($extranet, ['true', true])){

            $css = \Storage::get('css_specifique_extranet.css');

        }
		else
			$css = '';

		return view('eden::parametrage.css',['css_specifique_erp' => $css, 'extranet' => $extranet]);
	}

	/**
	 *
	 * Enregistre le css
	 *
	 */
	public function css_enregistrer() {

		$css = request()->css;
		$extranet = request()->extranet;

        if($extranet == true)
            \Storage::put('css_specifique_extranet.css', $css);
        else
            \Storage::put('css_specifique_erp.css', $css);

		return json_encode(array('retour' => true));
	}

	/**
	 *
	 * Appelle la vue pour configurer les paramètres de fonctionnalités
	 *
	 */
	public function fonctionnalites() {

        $management_general = management_fonctionnalite('fonctionnalite');

        $informations_modules = $management_general->enfants_avec_informations_sans_fonctionnalites();
        $fonctionnalites_manquantes = $management_general->recuperer_difference_vue_config();
        $fonctionnalites = config('fonctionnalites');

		return view('eden::parametrage.fonctionnalites')->with([
            'parametres_fonctionnalites' => $fonctionnalites,
            'modules' => $informations_modules,
            'fonctionnalites_manquantes' => $fonctionnalites_manquantes,
        ]);
	}

    /**
	 *
	 * Enregistre la modification des paramètres de fonctionnalités
	 *
	 */
    public function fonctionnalites_enregistrer(Request $formulaire) {

        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

        $fonctionnalites_password = $management_fonctionnalites_generales->toutes_fonctionnalites_par_type('password');

        $formulaire = $formulaire->all();

        foreach($fonctionnalites_password as $fonctionnalite_password) {

            if(isset($formulaire['parametres_fonctionnalites'][$fonctionnalite_password]))
                $formulaire['parametres_fonctionnalites'][$fonctionnalite_password] = base64_encode($formulaire['parametres_fonctionnalites'][$fonctionnalite_password]);
        }

        $formulaire['parametres_fonctionnalites'] = $management_fonctionnalites_generales->methode_pre_enregistrement($formulaire['parametres_fonctionnalites']);

        $contenu_fichier = "<?php\n\nreturn ".var_export($formulaire['parametres_fonctionnalites'], true).";\n";

        $contenu_fichier = str_replace(["'true'","'false'"],['true','false'],$contenu_fichier);

        // On sauvegarde la config actuelle
        $chemin_base = storage_path().'/app/sauvegardes_fonctionnalites';
        $nom_fichier = date('YmdHis').'_eden_fonctionnalites.php';

        // Cas où le dossier "sauvegarde_fonctionnalites" n'existe pas, on le crée
        if(!File::isDirectory($chemin_base))
            $retour = File::makeDirectory($chemin_base, $mode = 0777, true, true);

        if(file_exists(storage_path('app/eden_fonctionnalites.php'))) {

            $contenu_fichier_sauvegarde = file_get_contents(storage_path('app/eden_fonctionnalites.php'));

            // on stocke dans un fichier la sauvegarde
            \Storage::put('sauvegardes_fonctionnalites/'.$nom_fichier, $contenu_fichier_sauvegarde);
        }

		// on stocke dans un fichier
    	\Storage::put('eden_fonctionnalites.php', $contenu_fichier);

        $management_fonctionnalites_generales->methode_post_enregistrement_fonctionnalites($formulaire['parametres_fonctionnalites']);

        opcache_invalidate(storage_path('app/eden_fonctionnalites.php'), true);

        return response()->json(true);
    }

	/**
	 *
	 * Appelle la vue pour configurer les paramètres de fonctionnalités pour un module donné
	 *
	 */
	public function fonctionnalites_module($module) {
        log_eden('Parametrage_controller::fonctionnalites_modules début');
        $management_general = management_fonctionnalite('fonctionnalite');
        $management_module = management_fonctionnalite($module);

        $informations = $management_module->informations();
        $fonctionnalites_modules = $management_module->liste_fonctionnalites();
        $informations_modules = $management_general->enfants_avec_informations_sans_fonctionnalites();
        $fonctionnalites_manquantes = $management_general->recuperer_difference_vue_config();
        $fonctionnalites_type_connexion = $management_general->toutes_fonctionnalites_par_type('connexion');

        if($module === 'fonctionnalites_specifiques' && empty($fonctionnalites_modules))
            $fonctionnalites_modules = $management_module->fonctionnalites_mise_en_page_par_defaut();

        $parametres_fonctionnalites = config('fonctionnalites');

        if($module === 'fonctionnalites_specifiques')
            $parametres_fonctionnalites = config('fonctionnalites_specifiques');

        return view('eden::parametrage.fonctionnalites_module')->with([
            'nom_module_lien' => $module,
            'parametres_fonctionnalites' => $parametres_fonctionnalites,
            'informations' => $informations,
            'modules' => $informations_modules,
            'fonctionnalites_modules' => $fonctionnalites_modules,
            'fonctionnalites_manquantes' => $fonctionnalites_manquantes,
            'fonctionnalites_type_connexion' => $fonctionnalites_type_connexion,
            'profils' => modele('profil')->get()
        ]);
	}

	/**
	 *
	 * Appelle la vue pour configurer les paramètres de fonctionnalités spécifiques
	 * La vue et le fichier de config sont à créer en prod
	 *
	 */
	public function fonctionnalites_specifiques() {

		return view('eden::parametrage.fonctionnalites_specifiques')->with('fonctionnalites_specifiques' , config('fonctionnalites_specifiques'));
	}

    /**
	 *
	 * Enregistre la modification des paramètres de fonctionnalités
	 *
	 */
    public function fonctionnalites_specifiques_enregistrer(Request $formulaire) {

    	$contenu_fichier = "<?php\n\nreturn [\n";

    	foreach($formulaire['parametres_fonctionnalites'] as $nom => $valeur) {

    		if($valeur == 'true')
    			$contenu_fichier .= "\t'".$nom."' => true,\n";
    		elseif($valeur == 'false')
    			$contenu_fichier .= "\t'".$nom."' => false,\n";
    		else {

    			$contenu_fichier .= "\t'".$nom."' => \"".$valeur."\",\n";
    		}
    	}

    	$contenu_fichier .= "];";

		// on stocke dans un fichier
    	\Storage::put('eden_fonctionnalites_specifiques.php', $contenu_fichier);

    	return response()->json(true);

    }


	/**
	 *
	 *
	 *
	 */
	public function gestion_pdf_par_defaut() {

		$pdfs_types_elements = [];

		$liste = Table_libre::whereIn('type_element',Variables::$documents_gescom)->pluck('id', 'type_element')->toArray();

		foreach(Variables::$documents_gescom as $type_element) {

			$pdfs_types_elements[$type_element]['pdf_par_defaut'] = config('fonctionnalites_pdf_par_defaut')[$type_element];

			$pdfs_types_elements[$type_element]['pdf'][0] = 'document_gescom';

			 if(view()->exists('eden::pdf.document_gescom_'.$type_element))
				$pdfs_types_elements[$type_element]['pdf'][-1] = 'document_gescom_'.$type_element;


			$modeles = modele('modele_de_document')
				->join('modele_de_document_type_element', 'modele_de_document.id', '=', 'modele_de_document_type_element.cle_locale')
				->where('valeur', $liste[$type_element])
				->pluck('modele_de_document.nom',  'modele_de_document.id')->toArray();

			foreach($modeles as $id_modele => $modele) {

				$pdfs_types_elements[$type_element]['pdf'][$id_modele] = $modele;
			}
		}

		return view('eden::parametrage.fonctionnalites_pdf_par_defaut', compact('pdfs_types_elements'));
	}

	/**
	 *
	 *
	 *
	 */
	public function enregistre_pdf_par_defaut(Request $formulaire) {

		$pdfs_types_elements = $formulaire->pdfs_types_elements;

		$contenu_fichier = "<?php\n\nreturn [\n";


		foreach($pdfs_types_elements as $type_element => $valeur) {

			$contenu_fichier .= "\t'".$type_element."' => \"".$valeur['pdf_par_defaut']."\",\n";
		}

		$contenu_fichier .= "];";


		// on stocke dans un fichier
		\Storage::put('eden_fonctionnalites_pdf_par_defaut.php', $contenu_fichier);

        return response()->json(true);
	}

	/**
	 *
	 * Affichage des logs
	 *
	 */
	public function logs($action = false) {

		// On retourne la liste des fichiers de logs
		if($action == 'liste') {

			$liste_temp = glob(storage_path('logs/*.log'));

			$liste = [];
			foreach($liste_temp as $fichier) {

                if(str_contains($fichier,'worker'))
                    continue;
                
				$liste[$fichier] = pathinfo($fichier);
				$liste[$fichier]['nom'] = 'Log Laravel du '.formate_date('d/m/Y', str_replace('laravel-', '', $liste[$fichier]['filename']));
			}

			rsort($liste);

			return response()->json(['liste' => $liste]);
		}

		// On retourne les lignes d'un fichier de log
		if($action == 'log') {

			$types_presents = [];
			$couleurs_types = [
					'emergency' => 'danger',
					'alert' => 'danger',
					'critical' => 'danger',
					'error' => 'danger',
					'warning' => 'warning',
					'notice' => 'secondary',
					'info' => 'info',
					'debug' => 'success',
				];

			$log_temp = explode("\n", file_get_contents(storage_path('logs/').request()->log));

			$logs = [];
			foreach($log_temp as $numero => $ligne) {

				if($ligne == '[stacktrace]')
					continue;

				// C'est une nouvelle ligne
				if(substr($ligne, 0,2) == '[2') {

					$titre = substr($ligne, 22);

					$type = explode(':', $titre);
					$type = $type[0];
					$titre = str_replace($type.': ', '', $titre);
					$type = explode('.', $type);
					$type = @$type[1];

                    if(!in_array($type,$types_presents))
					    $types_presents[] = $type;

					$logs[] = [
								'date' => substr($ligne, 1, 19),
								'type' => $type,
								'numero' => $numero,
								'type_couleur' => @$couleurs_types[strtolower($type)],
								'erreur' => $titre, 'stacktrace' => []
								];
				}

			}

			rsort($logs);

			return response()->json(
					[
						'lignes_log' => $logs,
						'types' => $types_presents,
					]);
		}

		// On retourne une ligne d'un fichier de log
		if($action == 'ligne') {

			$couleurs_types = [
					'emergency' => 'danger',
					'alert' => 'danger',
					'critical' => 'danger',
					'error' => 'danger',
					'warning' => 'warning',
					'notice' => 'secondary',
					'info' => 'info',
					'debug' => 'success',
				];

			$log_temp = explode("\n", file_get_contents(storage_path('logs/').request()->log));

			$log_en_cours = 0;
			$logs = [];
			foreach($log_temp as $numero => $ligne) {

				if($ligne == '[stacktrace]')
					continue;

				// C'est une nouvelle ligne
				if(substr($ligne, 0,2) == '[2') {

					$log_en_cours = $numero;
					$titre = substr($ligne, 22);

					$type = explode(':', $titre);
					$type = $type[0];
					$titre = str_replace($type.': ', '', $titre);
					$type = explode('.', $type);
					$type = @$type[1];

					$logs[$log_en_cours] = ['date' => substr($ligne, 1, 19), 'type' => $type, 'type_couleur' => @$couleurs_types[strtolower($type)], 'erreur' => $titre, 'stacktrace' => []];

				// On ajoute la stacktrace à la dernière ligne
				} else {

					$type_couleur = '';
					$type = '';

					if(strpos($ligne, '/vendor/laravel/') !== false || strpos($ligne, 'ClassLoader->loadClass') !== false || strpos($ligne, 'spl_autoload_call') !== false) {

						$type_couleur = 'secondary';
						$type = 'Laravel';

					} elseif(strpos($ligne, 'Eden') !== false) {

						$type_couleur = 'success';
						$type = 'Eden';

					} elseif(strpos($ligne, '/app/') !== false || strpos($ligne, 'App\\') !== false) {

						$type_couleur = 'info';
						$type = 'Spécifique';

					} elseif(strpos($ligne, '/vendor/') !== false) {

						$type_couleur = 'secondary';
						$type = 'Vendors';
					}

					$logs[$log_en_cours]['stacktrace'][] = ['ligne' => $ligne, 'type' => $type, 'type_couleur' => $type_couleur];
				}

			}

			return response()->json(['details' => $logs[request()->ligne]]);
		}

		$erreur_parametrage = $this->recupere_log_bdd();

    	return view('eden::parametrage.logs',[
			'erreur_parametrage' => $erreur_parametrage,
		]);
    }

	/**
	 * 
	 * Recupère les logs liés à la base de données
	 * 
	 */
	public function recupere_log_bdd(){
		$erreur_parametrage = array();

		// Vérification des droits d'écriture pour les migrations et les traductions (standard et spécifique)
		$chemin_dossier_migrations_specifique = app_path().'/Migrations';
		$chemin_dossier_migrations_standard = app_path().'/Eden/Migrations';

		// On vérifie les droits sur le dossier app/Migrations
		if(!is_writable($chemin_dossier_migrations_specifique) && \File::isDirectory($chemin_dossier_migrations_specifique))
			$erreur_parametrage[] = 'Droit écriture manquant migrations spécifique';

        // On vérifie les droits sur le dossier app/Eden/Migrations
		if(!is_writable($chemin_dossier_migrations_standard) && \File::isDirectory($chemin_dossier_migrations_standard))
			$erreur_parametrage[] = 'Droit écriture manquant migrations standard';

		// Vérification sur toutes les tables qui possèdent un champ libre client_id doivent avoir un champ libre entite_id
		$champs_libres_client_id = Champ_libre::where('nom_sql', 'client_id')->get();

		foreach ($champs_libres_client_id as $champ) {

			$type_element = $champ->type_element;
			$champ_entite_id = Champ_libre::where('nom_sql', 'entite_id')->where('type_element', $type_element)->get();
			if(empty($champ_entite_id->toArray()))
				$erreur_parametrage[] = 'entite_id manquant sur '.$type_element.'';

		}

        $erreurs_cle_etrangere = json_decode(parametre('erreurs_cle_etrangere'),true);

        if(!empty($erreurs_cle_etrangere)){

            $erreur_parametrage[] = "----------------------------------------------------";
            $erreur_parametrage[] = "Liste des clés étrangères qui n'ont pas fonctionné :";

            $erreur_parametrage = array_merge($erreur_parametrage,$erreurs_cle_etrangere);

            $erreur_parametrage[] = "----------------------------------------------------";
        }

        // Vérifie que les listes formatées soient indiquées dans le fichier Variable.php

        $listes_formatees_champs_libres = Champ_libre::select('liste_choix')->where('type', 20)->where('liste_choix','!=',0)->distinct()->get()->pluck('liste_choix')->toArray();

        $listes_formatees = array_keys(variable('liste_formatees'));

        $difference_liste_formatees = array_diff($listes_formatees_champs_libres,$listes_formatees);

        sort($difference_liste_formatees);

        $configurations_email_defaut = modele('configuration_email')->select('type')->where('valeur_par_defaut',1)->get()->pluck('type')->toArray();
        $types_configurations_email = Cache_management::valeurs_liste_formatee(592);
        $configurations_manquantes = array();

        foreach($types_configurations_email as $cle => $type){

            if(in_array($type['id_valeur'], [0,1]))
                continue;

            if(!in_array($type['id_valeur'], $configurations_email_defaut))
                $configurations_manquantes[] = $type['valeur'];
        }

        if(!empty($configurations_manquantes))
            $erreur_parametrage[] = "Une ou plusieurs configurations email par défaut sont manquantes : ".implode(', ',$configurations_manquantes);

        if(!empty($difference_liste_formatees))
            $erreur_parametrage[] = "Une ou plusieurs listes formatées sont utilisés mais ne sont pas référencés dans Variable.php \n ".implode(';',$difference_liste_formatees);

		if(empty(fonctionnalite('email_smtp_host')))
			$erreur_parametrage[] = 'Paramétrage SMTP non renseigné. <a href="'.route("parametrage.fonctionnalites.module",["module"=>"emails_v2"]).'"> Fonctionnalités des emails </a>';

		return $erreur_parametrage;
	}


	/**
	 *
	 * Appelle la vue pour configurer les indicateurs
	 *
	 */
	public function indicateurs() {

		return view('eden::parametrage.indicateurs')->with('parametres_indicateurs' , config('indicateurs'));
	}

    /**
	 *
	 * Enregistre la modification des paramètres de fonctionnalités
	 *
	 */
    public function indicateurs_enregistrer(Request $formulaire) {

    	$contenu_fichier = "<?php\n\nreturn [\n";

    	foreach($formulaire['parametres_indicateurs'] as $nom => $valeur) {

    		if($valeur == 'true')
    			$contenu_fichier .= "\t'".$nom."' => true,\n";
    		elseif($valeur == 'false')
    			$contenu_fichier .= "\t'".$nom."' => false,\n";
    		else {

    			$contenu_fichier .= "\t'".$nom."' => \"".$valeur."\",\n";
    		}

    		if(config('indicateurs.'.$nom) !== true && $valeur == 'true') {

    			$indicateur = indicateur($nom)->calcule_pour_tous();
    		}
    	}

    	$contenu_fichier .= "];";

		// on stocke dans un fichier
    	\Storage::put('eden_indicateurs.php', $contenu_fichier);

    	return response()->json(true);

    }

    /**
	 *
	 * Recalcule un indicateur
	 *
	 */
    public function indicateurs_recalcule(Request $formulaire) {

    	$indicateur = $formulaire->indicateur;

    	indicateur($indicateur)->calcule_pour_tous();

    	return response()->json(true);

    }

    /**
	 *
	 * Activer la synchro GDrive de la bibliothèque
	 *
	 */
    public function synchro_gdrive() {

		$client = new \Google_Client();
	    $client->setApplicationName('Synchronisation Eden');
	    $client->setScopes(\Google_Service_Drive::DRIVE);
	    $client->setAccessType('offline');
	    $client->setRedirectUri(route('parametrage.gdrive.index'));
	    $client->setPrompt('select_account consent');

	    if(file_exists(storage_path('app/credentials.json'))) {
		    $client->setAuthConfig(storage_path('app/credentials.json'));
		}

	    $chemin_token = storage_path('app/token-google-drive.json');

	    if (file_exists($chemin_token)) {

	        $accessToken = json_decode(file_get_contents($chemin_token), true);
	        $client->setAccessToken($accessToken);
	    }

	    // S'il n'y a pas de token ou s'il est expiré.
	    if ($client->isAccessTokenExpired()) {

	        // On rafraichit le token si possible sinon on en demande un nouveau
	        if ($client->getRefreshToken()) {
	            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	        } else {

	            if(isset(request()->code)) {

	            	$authCode = request()->code;

		            // Échange le code d'autorisation contre un token.
		            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
		            $client->setAccessToken($accessToken);

		            // Vérifie s'il y a une erreur.
		            if (isset($access_token['error']))
		                throw new Exception(join(', ', $accessToken));

			        // Sauvegarde du token dans un fichier.
			        if (!file_exists(dirname($chemin_token)))
			            mkdir(dirname($chemin_token), 0700, true);

			        file_put_contents($chemin_token, json_encode($client->getAccessToken()));
	            }
	        }
	    }

    	return view('eden::parametrage.synchro_gdrive', [
    						'client' => $client,
    						'parametre' => parametre('synchro_gdrive_dossier_racine')
    					]);
    }

    /**
	 *
	 * Enregistre un paramètre
	 *
	 */
    public function parametre_enregistrer($nom_parametre) {

    	parametre($nom_parametre, request()->valeur);

    	return json_encode(['success' => true]);
    }

    /**
	 *
	 * Paramétrage des rapports
	 *
	 */
    public function rapports() {

    	$categories = Rapports_libres::categories();

    	foreach($categories as $cle => $infos) {

    		$categories[$cle]['rapports'] = Rapport_libre::where('categorie', $cle)->orderBy('ordre')->get();

            foreach($categories[$cle]['rapports'] as &$rapport){
                $rapport['standard'] = Rapport_management::rapport_standard($rapport);
            }
    	}

    	return view('eden::parametrage.rapports')->with('categories' , $categories);
    }

	/**
	 *
	 * Enregistre la modification des paramètres de fonctionnalités
	 *
	 */
	public function rapports_enregistrer(Request $formulaire) {

		foreach($formulaire->all() as $info_rapport) {

            $rapport = Rapport_libre::where('id_rapport', $info_rapport['id_rapport'])->first();

            if(!empty($info_rapport['suppression']))
                $retour = Rapport_management::supprimer($rapport);
            else if($rapport->inactif != $info_rapport['inactif']) {

                $rapport->inactif = $info_rapport['inactif'];

                $rapport->save();

                // on met à jour les fichiers de migrations du spécifique
                $la_liste_libre = Liste_libre::where('id_rapport', $info_rapport['id_rapport'])->first();

                if ($la_liste_libre !== null)
                    $retour = Liste_libre_management::generer_fichier_migration_liste_libre($la_liste_libre->id);
                else
                    $retour = Rapport_management::generer_fichier_migration_rapport($rapport->id);
            }
		}

		return response()->json(true);
	}

	/**
	 *
	 * Paramétrage des fiches
	 *
	 */
	public function fiches() {

        $extranet = strpos(request()->route()->getName(),'parametrage.extranet.') !== false;

		$fiches = Table_libre::where('fiche', 1)->get();

		return view('eden::parametrage.fiche.liste')->with('fiches' , $fiches)->with('extranet',$extranet);
	}

	/**
	 *
	 * Paramétrage d'une fiche en particulier
	 *
	 */
	public function fiche($type_element, Request $request) {

        $fiche_management = fiche($type_element, 0);

        $extranet = strpos($request->route()->getName(),'parametrage.extranet.') !== false;

		// on va chercher la config
		if ($extranet && file_exists(storage_path('app/eden_fiche_'.$type_element.'_extranet.php')))
            $fiche = include(storage_path('app/eden_fiche_'.$type_element.'_extranet.php'));
		else if(!$extranet && file_exists(storage_path('app/eden_fiche_'.$type_element.'.php')))
            $fiche = include(storage_path('app/eden_fiche_'.$type_element.'.php'));
		else {

            $fiche_tmp = $fiche_management->structure_fiche_par_defaut();
            $fiche = array('options' => array(), 'modules' => array());

			foreach($fiche_tmp['modules'] as $clef => $osef) {
                $fiche['modules'][] = $osef;
			}

            if(isset($fiche_tmp['options'])) {
                foreach ($fiche_tmp['options'] as $clef => $osef) {
                    $fiche['options'][$clef] = $osef;
                }
            }
		}

        $independant = !empty($fiche_management->independant);

		// on va chercher la liste des modules disponibles
        $modules = $fiche_management->modules_disponibles();

		// on va chercher les listes libres liées à la fiche
		$listes_libres = Liste_libre::select('eden_listeslibres.*','eden_rapports.index_traduction')
            ->join('eden_rapports','eden_rapports.id_rapport','eden_listeslibres.id_rapport')
            ->where('fiche', $type_element)->get();

		// on va chercher les rapports libres liées à la fiche
		$rapports_libres = Rapport_libre::where('type_element_fiche', $type_element)->get();

		// on va chercher les formulaires sur mesure
		$formulaires_libres = Formulaire::where('type_element', $type_element)->get();

		// On récupère les champs de type liste lié au type d'élément
		$champ_type_liste = Champ_libre::where('type_element',$type_element)->whereIn('type',[1,20])->get();
        $cartes = $rapports_libres->where('type_rapport','carte');
        $rapports_libres = $rapports_libres->where('type_rapport', '!=', 'carte');

		if(!isset($fiche['colonne_droite']))
            $fiche['colonne_droite'] = array();

        if(!$independant) {

            if (!isset($fiche['options']))
                $fiche['options'] = array();

            $valeur_par_defaut_options = array('afficher_fil_ariane' => 1, 'options_desactives' => []);

            foreach ($valeur_par_defaut_options as $cle => $valeur) {

                if (!isset($fiche['options'][$cle]))
                    $fiche['options'][$cle] = $valeur;
            }

            $options_fil_ariane = fiche($type_element)->options_fil_ariane([]);
        }

		return view('eden::parametrage.fiche', [
			'fiche' => $fiche,
			'modules' => $modules,
			'element' => $independant ? $type_element : table_libre($type_element)->element,
			'independant' => $independant,
			'type_element' => $type_element,
			'listes_libres' => $listes_libres,
			'rapports_libres' => $rapports_libres,
			'formulaires_libres' => $formulaires_libres,
			'champ_type_liste' => $champ_type_liste,
			'document' => !empty($fiche_management->document),
            'cartes' => $cartes,
			'options_pour_fil_ariane' => $options_fil_ariane ?? [],
		]);
	}

	/**
	 *
	 * Enregistre le paramétrage d'une fiche
	 *
	 */
	public function fiche_enregistrer(Request $formulaire, $type_element) {

		$retour = fiche($type_element)->genere_fichier_fiche($formulaire, strpos($formulaire->route()->getName(),'parametrage.extranet.') !== false);

		return response()->json($retour);

	}

    public function fiche_dupliquer(Request $formulaire, $type_element, $extranet = false){

        $documents_a_modifier = $formulaire->all();

        $management_fiche = fiche($type_element);

        $structure_fiche = $management_fiche->structure_fiche(array(
            'sans_traitement' => true
        ));

        foreach($documents_a_modifier as $document => $valeur){

            if($valeur != 'true')
                continue;

            fiche($document)->dupliquer_structure($structure_fiche,$extranet);
        }

        return response()->json(true);
    }

	/**
	 *
	 * Paramétrage d'afficher les paramètres possible sur les tables éditables (editable_client = 1)
	 *
	 */
	public function parametres_global() {

		$tables_libres = Table_libre::where('editable_client',1)
            ->where(function($condition){
                $condition->where('table_systeme',0)
                    ->orWhereNull('table_systeme');
            })
            ->orderBy('categorie','DESC')->get();

		return view('eden::parametrage.parametrage_tables_libres', [
			'tables_libres' => $tables_libres,
		]);
	}

	/**
	 *
	 * Paramétrage d'afficher les paramètres possible sur une table libre
	 *
	 */
	public function parametres_table_zoom($type_element) {

        $listes = new Collection();

        $liste_libre_principale = Liste_libre::select('eden_listeslibres.*','eden_tableslibres.index_traduction as table_index_traduction',DB::raw('\'principale\' as type_liste'))
            ->join('eden_tableslibres','eden_listeslibres.type_element','eden_tableslibres.type_element')
            ->where('eden_listeslibres.type_element',$type_element)
            ->where(function($requete){
                $requete->where('id_rapport','');
                $requete->orWhereNull('id_rapport');
            })
            ->get();

        $listes = $listes->merge($liste_libre_principale);

        $liste_libre_principale_existante = !$liste_libre_principale->isEmpty();

        $liste_libre_rapport = Liste_libre::select('eden_listeslibres.*','eden_rapports.titre','eden_rapports.liste_sur_fiche','eden_rapports.inactif','eden_rapports.index_traduction as rapport_index_traduction',DB::raw('IF(liste_sur_fiche = 1 OR eden_listeslibres.export = 1,IF(liste_sur_fiche = 1,\'fiche\',\'export\'),\'rapport\') as type_liste'))
            ->join('eden_rapports','eden_listeslibres.id_rapport','eden_rapports.id_rapport')
            ->where('eden_listeslibres.type_element',$type_element)
            ->whereNotNull('eden_listeslibres.id_rapport')
            ->where('eden_listeslibres.id_rapport','!=','')
            ->orderBy('inactif')
            ->orderBy('type_liste')
            ->get();

        $listes = $listes->merge($liste_libre_rapport);

        foreach($listes as &$liste){
            $liste->standard = Rapport_management::rapport_standard($liste,$liste);
        }

        $informations_listes = array(
            'liste_libre_principale_existante' => $liste_libre_principale_existante,
            'listes' => $listes,
        );

		$formulaires_libres = Formulaire::where('type_element', $type_element)->get();

        $champs_libres_type_element_liste = champs_libres_elements([$type_element]);

        $champs_selection_type_element_liste = [];

        if(!empty($champs_libres_type_element_liste[$type_element])) {

            $champs_type_element_liste = $champs_libres_type_element_liste[$type_element];

            if(!empty($champs_type_element_liste['42']))
                $champs_selection_type_element_liste = $champs_type_element_liste['42']->keyBy('nom_sql')->toArray();

            if(!empty($champs_libres_type_element_liste[$type_element]['22']))
                $champs_selection_type_element_liste = array_merge($champs_selection_type_element_liste,$champs_type_element_liste['22']->keyBy('nom_sql')->toArray());
        }

        $types_elements = Table_libre::orderBy('type_element')->get();

        $types_elements_fiche = $types_elements->sortBy('element')->pluck('type_element')->toArray();

        $types_elements_fiche = array_merge(Variables::$documents_gescom,$types_elements_fiche);

        sort($types_elements_fiche);

        $champs_type_element_fiche = champs_libres_elements($types_elements_fiche);

        $champs_selection_type_element_fiche = [];

        foreach($champs_type_element_fiche as $type_element_fiche => $champs_par_type) {

            if (!empty($champs_par_type['42']))
                $champs_selection_type_element_fiche[$type_element_fiche] = $champs_par_type['42']->keyBy('nom_sql')->toArray();

            if (!empty($champs_par_type['22']))
                $champs_selection_type_element_fiche[$type_element_fiche] = array_merge($champs_selection_type_element_fiche[$type_element_fiche] ?? [],$champs_par_type['22']->keyBy('nom_sql')->toArray());
        }

        $types_elements_fiche_formates = [];

        foreach ($types_elements_fiche as $type_element_fiche){

            $types_elements_fiche_formates[$type_element_fiche] = ucfirst(table_libre($type_element_fiche)->element);

        }

		$table_libre = Table_libre::where('type_element',$type_element)->first();

        $id_rapports = Rapport_libre::get()->pluck('id_rapport')->toArray();

		//Vérification existence formulaire générique et formulaire de fiche
		$formulaire_generique = Formulaire::where('nom_formulaire',$type_element)->first();

		if (!$formulaire_generique)
			$formulaire_generique_vide = true;
		else
			$formulaire_generique_vide = false;

		$formulaire_fiche = Formulaire::where('nom_formulaire','fiche_'.$type_element)->first();

		if (!$formulaire_fiche)
			$formulaire_fiche_vide = true;
		else
			$formulaire_fiche_vide = false;

        $formulaire_creation_volee = Formulaire::where('nom_formulaire','creation_volee_'.$type_element)->first();

        if (!$formulaire_creation_volee)
            $formulaire_creation_volee_vide = true;
        else
            $formulaire_creation_volee_vide = false;

        $categories = Rapports_libres::categories();
        $categories_rapports = Rapports_management::rapports_disponibles();

        $rapports_fiche = Rapport_libre::where('type_element_fiche', $type_element)->get();

        $champs_libres = (new Table_libre_management())->chargement_valeurs_forcees_extranet($table_libre);

		$types_vues_carte = Table_libre::where('type_element', 'adresse')
			->orWhereIn('type_element', 
				modele('vue_sql')
					->where('table_par_defaut', 'adresse')
					->select('nom_sql')
			)
			->get();

		return view('eden::parametrage.parametrage_tables_libres_zoom', [
		 	'informations_listes' => $informations_listes,
		 	'type_element' => $type_element,
		 	'types_elements' => $types_elements,
		 	'liste_libre_choix' => collect(variable('liste_libre_choix_ajout')),
		 	'table_libre' => $table_libre,
		 	'formulaire_fiche_vide' => $formulaire_fiche_vide,
		 	'formulaire_creation_volee_vide' => $formulaire_creation_volee_vide,
		 	'formulaire_generique_vide' => $formulaire_generique_vide,
		 	'formulaires_libres' => $formulaires_libres,
			'categories' => $categories,
            'categories_rapports' => $categories_rapports,
            'champs_type_element_liste' => $champs_type_element_liste ?? array(),
            'champs_selection_type_element_liste' => $champs_selection_type_element_liste,
            'champs_type_element_fiche' => $champs_type_element_fiche,
            'champs_selection_type_element_fiche' => $champs_selection_type_element_fiche,
            'champs_libres' => $champs_libres,
            'types_elements_fiche' => $types_elements_fiche_formates,
            'id_rapports' => $id_rapports,
            'rapports_fiche' => $rapports_fiche,
			'types_vues_carte' => $types_vues_carte,
		]);
	}

	/**
	 *
	 *
	 * Fonction qui active/désactive l'aide contextuelle
	 *
	 */
	public function gestion_aide_contextuelle() {

		// on modifie l'utilisateur en session
		$utilisateur = moi();

		$aide_contextuelle = empty($utilisateur->aide_contextuelle) ? 1 : 0;

		$utilisateur->aide_contextuelle = $aide_contextuelle;

		// on enregistre l'information en bdd

		management('utilisateur', $utilisateur->id)->enregistre(['aide_contextuelle' => $aide_contextuelle]);

		return redirect()->back();
	}

    public function fonctionnalites_recherche(Request $formulaire) {

        $chaine_de_charactere_recherche = $formulaire->recherche_texte;

        $resultat_recherche = management_fonctionnalite('fonctionnalite')->recherche_fonctionnalites($chaine_de_charactere_recherche);

        return response()->json($resultat_recherche);
    }

    /**
     *
     * Fonction permettant de récupérer la valeur des fonctionnalités via ajax
     *
     */
    public function recuperer_valeur_fonctionnalites(){

        return response()->json(config('fonctionnalites'));

	}

    public function regenere_composants_lie_fonctionnalite(Request $request){

        foreach($request['parametres_fonctionnalites'] as $nom => $valeur) {

            $composants = service('association_modules')->recupere('fonctionnalites', $nom);

            if(!empty($composants['modules']) || !empty($composants['type_element']))
                service('association_modules')->generation_composant('fonctionnalites', $nom, $composants);

        }
        $retour = management_fonctionnalite($request['nom_module'])->fonctionnalites_avec_valeurs();

        if($request['nom_module'] === 'fonctionnalites_specifiques' && empty($retour))
            $retour = management_fonctionnalite($request['nom_module'])->fonctionnalites_mise_en_page_par_defaut();

        return $retour;
    }

    public function fonctionnalites_exporter_excel(){

        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

        $les_fonctionnalites['les_fonctionnalites'] = $management_fonctionnalites_generales->toutes_les_fonctionnalites_avec_categories();

		$donnees = [
			'colonnes' => collect([
				(object)['id' => 0,'nom' => traduction('interface.export.fonctionnalites.module')],
				(object)['id' => 1,'nom' => traduction('interface.export.fonctionnalites.categorie')],
				(object)['id' => 2,'nom' => traduction('interface.export.fonctionnalites.nom_fonctionnalite')],
				(object)['id' => 3,'nom' => traduction('interface.export.fonctionnalites.fonctionnalite')],
			]),
			'lignes' => []
		];

		foreach($les_fonctionnalites['les_fonctionnalites'] as $nom_module => $categories)
			foreach($categories as $nom_categorie => $fonctionnalites)
				foreach($fonctionnalites as $fonctionnalites)
					$donnees['lignes'][] = [
						traduction('interface.export.fonctionnalites.module').' : '.$nom_module,
						$nom_categorie,
						$fonctionnalites['nom'],
						$fonctionnalites['fonctionnalite'],
					];

		if(!file_exists(storage_path('app/public/exports/export_fonctionnalites.xlsx')))
			\Storage::delete('public/exports/export_fonctionnalites.xlsx');

        service('export')->exporter_xlsx_csv($donnees,'export_fonctionnalites.xlsx');

		return response()->download(storage_path('app/public/exports/export_fonctionnalites.xlsx'), 'export_fonctionnalites.xlsx');
    }

    /*
     *
     * Permet de retourner toutes les aides contextuelles signalées comme OK par l'utilisateur connecté
     *
     */
    public function retourne_aide_contextuelle_utilisateur(){

        if(empty(moi()))
            return collect(array());

        return modele('aide_utilisateur')->where('utilisateur_id', moi()->id)->get();
    }

    /*
     *
     * Permet de vider les fonctionnalités liées à l'authentification d'une api.
     * Tom, 21/10/22 : Actuellement on ne vide qu'un paramètre, car il y a un ticket à venir pour finir le sujet
     *
     */
    public function deconnexion_api(){

        parametre(request('fonctionnalite'), '');

        return response()->json(['succes' => true]);
    }
}
