<?php

namespace App\Eden\Managements;

use App\Eden\Champs\Champ;
use App\Eden\Managements\Parametrage\Menus_management;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des utilisateurs de l'ERP
 */
class Cache_management {

	protected static $cache_temporaire;

	protected static $memo_partage = [];

    public static function partage($cle, $callback) {

		if(!self::partage_actif())
			return $callback();

		if(array_key_exists($cle, self::$memo_partage))
			return self::$memo_partage[$cle];

		if(!self::partage_store_disponible())
			return self::$memo_partage[$cle] = $callback();

		return self::$memo_partage[$cle] = Cache::rememberForever('eden.'.$cle, $callback);
    }

    public static function partage_lit($cle) {

		if(!self::partage_actif() || !self::partage_store_disponible())
			return null;

		if(array_key_exists($cle, self::$memo_partage))
			return self::$memo_partage[$cle];

		$valeur = Cache::get('eden.'.$cle);

		if($valeur !== null)
			self::$memo_partage[$cle] = $valeur;

		return $valeur;
    }

    public static function partage_ecrit($cle, $valeur) {

		if(!self::partage_actif())
			return $valeur;

		self::$memo_partage[$cle] = $valeur;

		if(self::partage_store_disponible())
			Cache::forever('eden.'.$cle, $valeur);

		return $valeur;
    }

    public static function partage_oublie($cle) {

		unset(self::$memo_partage[$cle]);

		if(self::partage_store_disponible())
			Cache::forget('eden.'.$cle);

		return true;
    }

    public static function partage_oublie_table($type_element) {

		self::partage_oublie('table_libre.'.$type_element);
		self::partage_oublie('colonnes.'.$type_element);
		self::partage_oublie('type_elements');

		return true;
    }

    public static function partage_oublie_champs_libres($type_element, $nom_sql = false) {

		$espaces = array(
			'champs_libres_objet',
			'champs_libres_uniques',
			'champs_libres_pj',
			'champs_libres_sous_formulaire',
			'champs_libres_textarea',
			'champs_libres_recherche',
			'champs_libres_obligatoires',
			'champs_libres_multiselection',
			'champs_libres_numerotation_automatique',
			'champs_libres_liste_utilisateurs',
			'champs_libres_elements',
		);

		foreach($espaces as $espace)
			self::partage_oublie($espace.'.'.$type_element);

		$noms_sql = DB::table('eden_champslibres')->where('type_element', $type_element)->pluck('nom_sql')->toArray();

		if(!empty($nom_sql))
			$noms_sql[] = $nom_sql;

		foreach(array_unique($noms_sql) as $nom) {

			self::partage_oublie('champ_libre.'.$type_element.'.'.$nom);
		}

		return self::partage_oublie_table($type_element);
    }

    public static function partage_oublie_traductions() {

		self::partage_oublie('langues');
		self::partage_oublie('langues_codes');

		foreach(DB::table('traduction_langue')->pluck('code') as $code)
			self::partage_oublie('traductions.'.$code);

		return true;
    }

    public static function partage_oublie_memo() {

		self::$memo_partage = [];

		return true;
    }

    public static function partage_vide_tout() {

		self::$memo_partage = [];

		if(self::partage_store_disponible())
			Cache::flush();

		return true;
    }

    protected static function partage_actif() {

		return env('EDENPME_CACHE', true);
    }

    protected static function partage_store_disponible() {

		return Container::getInstance() !== null && Container::getInstance()->bound('cache');
    }

    /**
     *
     * Permet de vider le cache Eden qui se trouve en session
     *
     * @return true
     *
     */
    public static function vider_tout($utilisateur_connecte_seulement = false) {

		self::partage_vide_tout();

		return self::vider($utilisateur_connecte_seulement);
    }

    public static function vider($utilisateur_connecte_seulement = false) {

		if($utilisateur_connecte_seulement === false)
			self::invalide();

		// on vide le cache en session
		session()->forget('cache');
		session()->put('cache_version', self::version());

		// on met à jour le fichier des filtres vuejs
		\App\Eden\Vuejs::supprime_fichier_filtres();

		// on met à jour le fichier cache des familles
		\App\Eden\Managements\Familles_management::supprime_cache();

		\App\Eden\Managements\Familles_management::genere_cache();


		return true;
    }

    public static function version() {

		return Cache::get('eden.version', 0);
    }

    public static function invalide() {

		Cache::forever('eden.version', microtime(true));

		return true;
    }

    public static function verifie_si_cache_obsolete() {

		$version = self::version();

		if(session()->get('cache_version') !== $version) {

			session()->forget('cache');
			session()->put('cache_version', $version);
		}

		return true;
    }

    public static function inspection_partage() {

		$resultat = array();

		foreach(self::inspection_cles() as $espace => $discriminants) {

			$nb = 0;
			$poids = 0;

			foreach($discriminants as $discriminant) {

				$valeur = Cache::get('eden.'.$espace.($discriminant === null ? '' : '.'.$discriminant));

				if($valeur === null)
					continue;

				$nb++;
				$poids += strlen(serialize($valeur));
			}

			if($nb > 0)
				$resultat[] = array('espace' => $espace, 'nb' => $nb, 'poids' => $poids);
		}

		usort($resultat, fn($a, $b) => $b['poids'] <=> $a['poids']);

		return $resultat;
    }

    public static function inspection_sessions() {

		$version = self::version();

		$resultat = array();

		foreach(glob(config('session.files').'/*') as $fichier) {

			if(!is_file($fichier) || basename($fichier) === '.gitignore')
				continue;

			$contenu = @unserialize(file_get_contents($fichier));

			if(!is_array($contenu))
				continue;

			$interne = $contenu['utilisateur_eden'] ?? null;
			$extranet = $contenu['utilisateur_eden_extranet'] ?? null;
			$cache = $contenu['cache'] ?? array();

			$espaces = array();

			foreach($cache as $espace => $valeur)
				$espaces[] = array('espace' => $espace, 'poids' => strlen(serialize($valeur)));

			usort($espaces, fn($a, $b) => $b['poids'] <=> $a['poids']);

			$resultat[] = array(
				'id'             => substr(basename($fichier), 0, 10),
				'utilisateur'    => self::inspection_utilisateur($interne ?: $extranet),
				'id_utilisateur' => self::inspection_valeur($interne ?: $extranet, 'id'),
				'type'           => $extranet !== null ? 'Extranet' : ($interne !== null ? 'Interne' : 'Anonyme'),
				'langue'         => $contenu['locale'] ?? (self::inspection_valeur($interne, 'langue') ?: '-'),
				'poids'          => filesize($fichier),
				'poids_cache'    => empty($cache) ? 0 : strlen(serialize($cache)),
				'nb_espaces'     => count($espaces),
				'a_jour'         => ($contenu['cache_version'] ?? null) === $version,
				'activite'       => date('d/m/Y H:i', filemtime($fichier)),
				'inactif_depuis' => self::inspection_duree(time() - filemtime($fichier)),
				'espaces'        => $espaces,
			);
		}

		usort($resultat, fn($a, $b) => $b['poids'] <=> $a['poids']);

		return $resultat;
    }

    protected static function inspection_valeur($objet, $cle) {

		if(is_object($objet))
			return $objet->$cle ?? null;

		if(is_array($objet))
			return $objet[$cle] ?? null;

		return null;
    }

    protected static function inspection_duree($secondes) {

		if($secondes < 60)
			return 'moins d\'une minute';

		if($secondes < 3600)
			return floor($secondes / 60).' min';

		if($secondes < 86400)
			return floor($secondes / 3600).' h';

		return floor($secondes / 86400).' j';
    }

    public static function inspection_disque() {

		$nb = 0;
		$poids = 0;

		foreach(rglob(storage_path('framework/cache/data').'/*') as $fichier)
			if(is_file($fichier)) {

				$nb++;
				$poids += filesize($fichier);
			}

		return array('nb' => $nb, 'poids' => $poids);
    }

    protected static function inspection_utilisateur($utilisateur) {

		if(is_object($utilisateur))
			return trim(($utilisateur->prenom ?? '').' '.($utilisateur->nom ?? '')) ?: 'Utilisateur '.($utilisateur->id ?? '?');

		if(is_array($utilisateur))
			return trim(($utilisateur['prenom'] ?? '').' '.($utilisateur['nom'] ?? '')) ?: 'Utilisateur '.($utilisateur['id'] ?? '?');

		return 'Session anonyme';
    }

    public static function inspection_etat() {

		$version = self::version();

		return array(
			'actif'       => env('EDENPME_CACHE', true) ? true : false,
			'driver'      => config('cache.default'),
			'session'     => config('session.driver'),
			'invalide_le' => $version > 0 ? date('d/m/Y H:i:s', (int) $version) : null,
		);
    }

    public static function inspection_espace($espace) {

		$cles = self::inspection_cles();

		if(!isset($cles[$espace]))
			return array();

		$resultat = array();

		foreach($cles[$espace] as $discriminant) {

			$valeur = Cache::get('eden.'.$espace.($discriminant === null ? '' : '.'.$discriminant));

			if($valeur === null)
				continue;

			$resultat[] = array(
				'cle' => $discriminant === null ? $espace : $discriminant,
				'poids' => strlen(serialize($valeur)),
				'entrees' => is_countable($valeur) ? count($valeur) : null,
			);
		}

		usort($resultat, fn($a, $b) => $b['poids'] <=> $a['poids']);

		return $resultat;
    }

    public static function partage_oublie_espace($espace) {

		$cles = self::inspection_cles();

		if(!isset($cles[$espace]))
			return false;

		foreach($cles[$espace] as $discriminant)
			self::partage_oublie($espace.($discriminant === null ? '' : '.'.$discriminant));

		return true;
    }

    public static function inspection_nb_sondes() {

		$nb = 0;

		foreach(self::inspection_cles() as $discriminants)
			$nb += count($discriminants);

		return $nb;
    }

    protected static function inspection_cles() {

		$types_elements = DB::table('eden_tableslibres')->pluck('type_element')->toArray();

		$par_type_element = array(
			'champs_libres_objet',
			'champs_libres_uniques',
			'champs_libres_pj',
			'champs_libres_sous_formulaire',
			'champs_libres_textarea',
			'champs_libres_recherche',
			'champs_libres_obligatoires',
			'champs_libres_multiselection',
			'champs_libres_numerotation_automatique',
			'champs_libres_liste_utilisateurs',
			'champs_libres_elements',
			'table_libre',
			'colonnes',
			'triggers',
			'workflows',
			'calendrier_synchro',
			'synchronisations',
			'modeles_de_documents',
		);

		$cles = array();

		foreach($par_type_element as $espace)
			$cles[$espace] = $types_elements;

		$cles['champ_libre'] = DB::table('eden_champslibres')->selectRaw("CONCAT(type_element, '.', nom_sql) as cle")->pluck('cle')->toArray();
		$cles['traductions'] = DB::table('traduction_langue')->pluck('code')->toArray();
		$cles['listes_libres'] = DB::table('eden_champslibres_listes')->distinct()->pluck('id_cl')->toArray();

		foreach(array('langues', 'langues_codes', 'type_elements', 'conditionnement_present', 'utilisateur_systeme') as $espace)
			$cles[$espace] = array(null);

		return $cles;
    }
    /**
     *
     * Cache qui ne dure qu'une page (pour éviter les multi requetes par exemple)
     *
     */
    public static function temporaire($nom, $valeur = null) {

		if($valeur === null) {

			if(!isset(self::$cache_temporaire))
				return null;

			if(!isset(self::$cache_temporaire[$nom]))
				return null;

			return self::$cache_temporaire[$nom];
		}

		if(!isset(self::$cache_temporaire))
			self::$cache_temporaire = array();

		self::$cache_temporaire[$nom] = $valeur;

		return $valeur;
    }

    /**
     *
     * Génère les fichiers JS des composants de l'ERP
     *
     */
    public static function genere_fichiers_composants() {

        Cache_management::genere_valeurs_champs_listes();
        Cache_management::genere_fichier_css();
        Cache_management::genere_menus();
        Cache_management::genere_traductions();
        Cache_management::generation_module();
        Cache_management::generation_liste_libre();

    }

    public static function genere_fichiers_composants_modules() {

        Cache_management::genere_valeurs_champs_listes();
        Cache_management::genere_fichier_css();
        Cache_management::genere_menus();
        Cache_management::genere_traductions();
        Cache_management::generation_module();

    }

    public static function genere_fichiers_composants_listes() {

        self::vider();


        Cache_management::generation_liste_libre();

    }

    /**
     *
     * Permet de regénérer les listes libres
     *
     */
    public static function generation_liste_libre($id_liste = false){

        $version_composants = intval(parametre('version_composants'));

        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }
        
        $rapports_a_eviter = Rapport_libre::where(function($requete){
            $requete->where('kanban','!=','');
            $requete->whereNotNull('kanban');
        })->orWhere(function($requete){
            $requete->where('visualisation_carte','!=','');
            $requete->whereNotNull('visualisation_carte');
        })->get()->pluck('id_rapport')->toArray();

        if($id_liste != null)
            $listes_libres = Liste_libre::where('id', $id_liste)
                ->where(function($sous_requete) use($rapports_a_eviter){
                    $sous_requete->whereNotIn('id_rapport',$rapports_a_eviter)
                        ->orWhereNull('id_rapport');
                })
                ->get();
        else
            $listes_libres = Liste_libre::where(function($sous_requete) use($rapports_a_eviter){
                    $sous_requete->whereNotIn('id_rapport',$rapports_a_eviter)
                        ->orWhereNull('id_rapport');
                })->get();

        $categories = Rapports_management::rapports_disponibles();

        foreach($listes_libres as $liste_libre) {

            $nom_du_fichier_js = 'liste_libre_' . $liste_libre->id . '.js';

            $id_rapport = $liste_libre->id_rapport;

            $type_element = $liste_libre->type_element;

            if(!empty($id_rapport)) {

                $management = liste_rapport($id_rapport);
            }
            else {

                $management = liste($type_element);
            }

            if ($id_rapport == '' || $id_rapport == null)
                $donnees = $management->recuperation_donnees_pour_liste_libre($liste_libre);

            else {
                $donnees = $management->recuperation_donnees_pour_liste_libre_rapport($liste_libre);
                $donnees['categories'] = $categories;
            }

            if(!empty($donnees)) {

                $donnees['type_element'] = $type_element;

                if (!empty($id_rapport) && view()->exists('eden::listes.liste_' . $id_rapport))
                    $vue = 'eden::listes.liste_' . $id_rapport;
                elseif (view()->exists('eden::listes.liste_' . $donnees['type_element']))
                    $vue = 'eden::listes.liste_' . $donnees['type_element'];
                else
                    $vue = 'eden::composants_vue.js.liste_libre';

                $contenu_de_la_vue = view($vue, $donnees)->render();

                $contenu_de_la_vue = str_replace(array('<script>', '</script>', '<script type="text/javascript">'), '', $contenu_de_la_vue);

                \Storage::put('public/composants/'. $nom_du_fichier_js, $contenu_de_la_vue);
            }
        }

    }

    /**
     *
     * Génère les fichiers Css de l'ERP
     *
     */
    public static function genere_fichier_css() {

        $version_composants = intval(parametre('version_composants'));

        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }

        $contenu_de_la_vue = view('eden::composants_vue.css')->render();

        $contenu_de_la_vue = str_replace(array('<style>', '</style>'), '', $contenu_de_la_vue);

        \Storage::put('public/css/css_generer.css', $contenu_de_la_vue);

    }

    /**
     *
     * Génère le menu de l'ERP
     *
     */
    public static function genere_menus($id_profil_menu = false) {

		$utilisation_extranet = fonctionnalite('utiliser_extranet');

        $version_composants_menu = intval(parametre('version_composants_menus'));

        if($version_composants_menu == null)
            parametre('version_composants_menus',1);
        else{
            $version_composants_menu++;
            parametre('version_composants_menus',$version_composants_menu);
        }

        $calcul_route = function(&$menus){

            foreach($menus as &$infos_menus){

                if(!empty($infos_menus['desactive']))
                    continue;

                if(isset($infos_menus['route'])){

                    if(isset($infos_menus['type_lien']) && $infos_menus['type_lien'] == 4){

                        $infos_menus['route'] = $infos_menus['route'];
                        $infos_menus['target'] = '_blank';
                    }
                    else
                        $infos_menus['route'] = route($infos_menus['route'], $infos_menus['parametres'] ?? [], false);
                }
                else if(!empty($infos_sous_menus)) {

                    foreach ($infos_menus['sous_menus'] as &$infos_sous_menus) {

                        if(isset($infos_sous_menus['type_lien']) && $infos_sous_menus['type_lien'] == 4){

                            $infos_sous_menus['route'] = $infos_sous_menus['route'];
                            $infos_sous_menus['target'] = '_blank';
                        }
                        else
                            $infos_sous_menus['route'] = route($infos_sous_menus['route'], $infos_sous_menus['parametres'] ?? [], false);
                    }
                }

            }
        };

        $management_menus = new Menus_management();

        $menus_eden = $management_menus->recuperation_menus();
        $calcul_route($menus_eden);
        
        if($utilisation_extranet) {

            $menus_eden_extranet = $management_menus->recuperation_menus(true);
            $calcul_route($menus_eden_extranet);

            $contenu_de_la_vue = view('eden::composants_vue.menus', ['menus_eden' => $menus_eden_extranet])->render();

            $contenu_de_la_vue = str_replace(array('<script>', '</script>'), '', $contenu_de_la_vue);

            \Storage::put('public/composants/extranet/menus.js', $contenu_de_la_vue);
        }

        $contenu_de_la_vue = view('eden::composants_vue.menus',['menus_eden'=>$menus_eden])->render();

        $contenu_de_la_vue = str_replace(array('<script>', '</script>'), '', $contenu_de_la_vue);

        \Storage::put('public/composants/menus.js', $contenu_de_la_vue);


    }

     /**
     *
     * Génère les modules de l'ERP
     *
     */
    public static function generation_module($nom_module = false) {

        $version_composants = intval(parametre('version_composants'));

        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }

        $modules = [];

        $dossiers = [
            app_path('Eden/Views/composants_vue/js'),
            resource_path('views/vendor/eden/composants_vue/js')
        ];

        foreach($dossiers as $dossier) {
            if (!is_dir($dossier))
                continue;

            $repertoire = scandir($dossier);

            self::recupere_modules_repertoire($repertoire, $modules, $dossier, $nom_module);
        }

        if(empty($modules))
            throw new \Exception('Aucun module trouvé pour la génération des composants');

        foreach($modules as $module){

            $nom_du_fichier_js = str_replace(['composants_vue.js.', '.'], ['', '/'], $module) . '.js';

            $contenu_de_la_vue = view('eden::' . $module)->render();

            $contenu_de_la_vue = str_replace(array('<script>', '</script>'), '', $contenu_de_la_vue);

            \Storage::put('public/composants/' . $nom_du_fichier_js, $contenu_de_la_vue);

        }
    }

    /**
     *
     * Permet de générer les valeurs des listes libres et des listes formatées dans un fichier
     *
     * @param $utilisateur si fourni, ne régénère le fichier que pour cet utilisateur au lieu
     * de reboucler sur tous les utilisateurs (utilisé à la création d'un utilisateur / affectation
     * d'un profil, pour éviter un recalcul complet à chaque enregistrement)
     *
     */
    public static function genere_valeurs_champs_listes($utilisateur = null){

        self::vider();

        // Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if(defined('migration_en_cours') && !defined('regeneration_valeurs_listes'))
            return true;

		$version_composants = intval(parametre('version_composants'));

        //Gestion de la version
        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }

        //On récupére les valeurs éditées des listes formatées
        $champs_listes_formatees = Champs_liste_formatee::where('desactivee','!=',1)->orderBy('ordre')->get()->toArray();

        $champs_listes_formatees_trie = array();

        $compteur_ordre = array();

        foreach($champs_listes_formatees as $cle => $champ){

            if(!isset($compteur_ordre[$champ['id_liste_choix']]))
                $compteur_ordre[$champ['id_liste_choix']] = 0;

            $champs_listes_formatees_trie[$champ['id_liste_choix']][$compteur_ordre[$champ['id_liste_choix']]] = $champ;

            $compteur_ordre[$champ['id_liste_choix']]++;
        }

        //On gére les champs listes
        $champs_libre_liste = Champ_libre_liste::orderBy('ordre')->get()->keyBy('id_valeur')->toArray();

        $champs_libre_liste_trie = array();

        foreach ($champs_libre_liste as $cle => $champ) {

            if(empty($champ['id_cl']))
                continue;

            $champs_libre_liste_trie[$champ['id_cl']][$cle] = $champ;

        }

        $liste_libres = Champ_libre::whereIn('type', array(1,12))->where(function($r) { $r->where('liste_choix', 0)->orWhereNull('liste_choix'); })
            ->get()->pluck('id_cl')->toArray();

        $cle_listes_libres = array_unique(array_merge($liste_libres,array_keys($champs_libre_liste_trie)));

        $listes_formatees = collect(variable('liste_formatees'));

        //on gére les traductions sans passer par le helper pour un soucis de performance
        $traductions = modele('traduction_valeur')
                ->join('traduction_index','traduction_index.index','traduction_valeur.index')
                ->select('traduction_valeur.index','traduction_valeur.langue', DB::raw('IFNULL(COALESCE(traduction_specifique,traduction_standard),"") AS valeur'))
                ->whereIn('categorie', [8,9])
                ->get();

        $traductions_organises_par_langue = array();

        foreach($traductions as $traduction){

            if(!isset($traductions_organises_par_langue[$traduction->langue]))
                $traductions_organises_par_langue[$traduction->langue] = [];

            $traductions_organises_par_langue[$traduction->langue][$traduction->index] = $traduction->valeur;
        }

        foreach($traductions_organises_par_langue as $langue => &$traductions_organises){

            if($langue != 'fr')
                $traductions_organises = array_merge($traductions_organises_par_langue['fr'],$traductions_organises);
        }

        $traductions = $traductions_organises_par_langue;

        $liaisons = Champ_libre_management::liaisons_valeurs_listes_libres();

        //On récupére les utilisateurs pour lesquels nous devons générer un fichier
        if(!empty($utilisateur))
            $utilisateurs_a_parcourir = collect([$utilisateur->id => $utilisateur]);
        else
            $utilisateurs_a_parcourir = modele('utilisateur')
                ->where('autorise_a_se_connecter',1)
                ->get()->keyBy('id');

        $nombre_entite = modele('entite')->count();

        if($nombre_entite > 1) {
            $entites_par_utilisateur = collect(DB::select('WITH RECURSIVE cte AS (
                        SELECT valeur as entite_id, cle_locale as utilisateur_id
                        FROM utilisateur_entites
                        WHERE cle_locale IN (' . implode(',',array_keys($utilisateurs_a_parcourir->toArray())) . ')
                        UNION ALL
                        SELECT id as entite_id, cte.utilisateur_id
                        FROM entite
                        JOIN cte ON entite.entite_parent = cte.entite_id
                    )
                    SELECT entite_id,utilisateur_id
                    FROM cte'))->groupBy('utilisateur_id')
                ->map(function($groupe) {
                    return $groupe->pluck('entite_id');
                })
                ->toArray();

            $utilisateurs_a_parcourir->map(function($utilisateur) use ($entites_par_utilisateur){
                $utilisateur->entites = $entites_par_utilisateur[$utilisateur->id] ?? [];
            });
        }
        else
            $utilisateurs_a_parcourir->map(function($utilisateur){
                $utilisateur->acces_toutes_entites = 1;
            });

        if(empty($utilisateur) && fonctionnalite('utiliser_extranet')) {
            $utilisateur_extranet = modele_par_defaut('utilisateur');
            $utilisateur_extranet->langue = 'fr';
            $utilisateurs_a_parcourir['extranet'] = $utilisateur_extranet;
            $utilisateurs_a_parcourir['extranet_login'] = $utilisateur_extranet;
        }

		$utilisateur_initiale = moi();

        $service_extranet = service('extranet');

        $champs_liste_choix = Champ_libre::whereNotNull('liste_choix')->where('liste_choix','!=','')->get()->groupBy('liste_choix');

        $valeurs_fixe[71] = Table_libre::leftJoin('traduction_valeur',function($join) {
                    $join->on('index',DB::raw("CONCAT(index_traduction,'.nom_table')"))
                        ->where(function($condition){
                            $condition->where('traduction_valeur.inactif',0)
                                ->orWhereNull('traduction_valeur.inactif');
                        })
                        ->where('langue','fr');
                })
                ->where(function($condition){
                    $condition->where('table_systeme',0)
                        ->orWhereNull('table_systeme');
                })
                ->select(DB::raw('IF(COALESCE(traduction_specifique,traduction_standard) IS NULL,eden_tableslibres.type_element,CONCAT(COALESCE(traduction_specifique,traduction_standard), " (",eden_tableslibres.type_element,")")) as nom_element'),'eden_tableslibres.id')
                ->orderBy('nom_element')->get()->pluck('nom_element', 'id')->toArray();

        try {

            foreach ($utilisateurs_a_parcourir as $utilisateur_id => $utilisateur_courant) {

                $champs_listes_formatees_utilisateur = $champs_listes_formatees_trie;
                $champs_libre_liste_utilisateur = $champs_libre_liste_trie;

                session()->put('utilisateur_eden', $utilisateur_courant);

                $langue = langue_utilisateur();

                $langue = $langue === null || empty($traductions[$langue]) ? 'fr' : $langue;

                // Cas des listes formatées
                foreach ($listes_formatees as $cle => $valeur) {
                    self::valeurs_listes_formatees($cle,$champs_listes_formatees_utilisateur, $traductions[$langue], $champs_liste_choix,$valeurs_fixe);
                }

                // Cas des listes libres
                foreach ($cle_listes_libres as $cle) {

                    $liaisons_cle = [];

                    if(isset($liaisons[$cle]))
                        $liaisons_cle = $liaisons[$cle];

                    self::valeurs_listes_libres($cle,$champs_libre_liste_utilisateur, $traductions[$langue], $liaisons_cle);
                }

                // Les valeurs des listes libres
                $valeurs_listes_libres = array();

                foreach ($champs_libre_liste_utilisateur as $id_cl => $liste) {

                    if($utilisateur_id == 'extranet_login' && !$service_extranet->champs_disponible_pour_extranet('liste_libre', $id_cl))
                        continue;

                    if (empty($id_cl))
                        continue;

                    $valeurs_listes_libres[$id_cl] = $liste;
                }

                // Les valeurs des listes formatees
                $valeurs_listes_formatees = array();
                foreach ($champs_listes_formatees_utilisateur as $id_cl => $liste) {

                    if($utilisateur_id == 'extranet_login' && !$service_extranet->champs_disponible_pour_extranet('liste_formatee', $id_cl))
                        continue;

                    $valeurs_listes_formatees[$id_cl] = $liste;
                }

                $valeurs_json = array(
                    'valeurs_listes_libres' => $valeurs_listes_libres,
                    'valeurs_listes_formatees' => $valeurs_listes_formatees
                );

                $valeurs_json = json_encode($valeurs_json);

                $valeurs_js = 'var valeurs_listes_libres = '.json_encode($valeurs_listes_libres).';var valeurs_listes_formatees = '.json_encode($valeurs_listes_formatees).';';

                \Storage::put('public/valeurs_champs_listes/utilisateur_' .$utilisateur_id . '.json', $valeurs_json);
                \Storage::put('public/valeurs_champs_listes/utilisateur_' . $utilisateur_id . '.js', $valeurs_js);
            }

        }
        catch(\Exception $e){
            session()->put('utilisateur_eden', $utilisateur_initiale);

            throw $e;
        }

        session()->put('utilisateur_eden', $utilisateur_initiale);

	    return true;
    }

    /**
     *
     * Permet de générer les champs listes d'une liste formatée ou libre
     *
     */
    public static function genere_valeurs_liste($type,$liste_choix) {

        $version_composants = intval(parametre('version_composants'));

        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }

        $utilisateurs_a_parcourir = modele('utilisateur')
            ->where('autorise_a_se_connecter',1)
            ->get()->keyBy('id');

        if(fonctionnalite('utiliser_extranet')) {
            $utilisateur_extranet = modele_par_defaut('utilisateur');
            $utilisateur_extranet->langue = 'fr';
            $utilisateurs_a_parcourir['extranet'] = $utilisateur_extranet;
            $utilisateurs_a_parcourir['extranet_login'] = $utilisateur_extranet;
        }

		$utilisateur_initiale = moi();

        try {

            foreach ($utilisateurs_a_parcourir as $utilisateur_id => $utilisateur) {

                if(!\Storage::has('public/valeurs_champs_listes/utilisateur_' . $utilisateur_id . '.json'))
                    continue;

                $valeurs_origine = \Storage::get('public/valeurs_champs_listes/utilisateur_' . $utilisateur_id . '.json');

                $valeurs_origine = json_decode($valeurs_origine,true);

                $valeurs = $valeurs_origine[$type];

                if($type == 'valeurs_listes_formatees'){
                    if($utilisateur_id == 'extranet_login' && !service('extranet')->champs_disponible_pour_extranet('liste_formatee', $liste_choix))
                        continue;

                    $valeurs[$liste_choix] = Champs_liste_formatee::where('desactivee','!=',1)
                        ->where('id_liste_choix',$liste_choix)
                        ->orderBy('ordre')->get()->toArray();
                }
                else{
                    if($utilisateur_id == 'extranet_login' && !service('extranet')->champs_disponible_pour_extranet('liste_libre', $liste_choix))
                        continue;

                    $valeurs[$liste_choix] = Champ_libre_liste::where('id_cl',$liste_choix)
                        ->orderBy('ordre')->get()->keyBy('id_valeur')->toArray();

                }

                self::$type($liste_choix,$valeurs);

                $valeurs_origine[$type] = $valeurs;

                $valeurs_json = json_encode($valeurs_origine);

                $valeurs_js = 'var valeurs_listes_libres = '.json_encode($valeurs_origine['valeurs_listes_libres']).';var valeurs_listes_formatees = '.json_encode($valeurs_origine['valeurs_listes_formatees']).';';

                \Storage::put('public/valeurs_champs_listes/utilisateur_' .$utilisateur_id . '.json', $valeurs_json);
                \Storage::put('public/valeurs_champs_listes/utilisateur_' . $utilisateur_id . '.js', $valeurs_js);
            }

        }
        catch(\Exception $e){
            session()->put('utilisateur_eden', $utilisateur_initiale);

            throw $e;
        }

        session()->put('utilisateur_eden', $utilisateur_initiale);

	    return true;
    }

    /**
     *
     * Permet de générer les champs listes d'une liste formatée
     *
     */
    public static function genere_valeurs_liste_formatees($liste_choix) {

        return self::genere_valeurs_liste('valeurs_listes_formatees',$liste_choix);
    }

    /**
     *
     * Permet de générer les champs listes d'une liste libre
     *
     */
    public static function genere_valeurs_liste_libres($liste_choix) {

        return self::genere_valeurs_liste('valeurs_listes_libres',$liste_choix);
    }

    public static function valeurs_listes_formatees($cle, &$champs_listes_formatees, $traductions = array(),$champs_liste_choix = null, $valeurs_fixe = array()){

        // Cas spécifiques des champs Oui/Non et Oui/Non/Sans valeur
        if(in_array($cle,array(3,14))){

            $tableau_par_defaut = Champ::recuperer_valeur_listes_preenregistrees($cle,null,false,$traductions)['liste'];

            // On initialise un tableau par defaut
            $tableau_par_defaut_organises = [];

            foreach ($tableau_par_defaut as $index => $valeur) {

                $tableau_par_defaut_organises[$index] = array();
                $tableau_par_defaut_organises[$index]['id_valeur'] = $index;
                $tableau_par_defaut_organises[$index]['valeur'] = $valeur;
                $tableau_par_defaut_organises[$index]['id_liste_choix'] = $cle;

            }

            $champs_listes_formatees[$cle]['standard'] = $tableau_par_defaut_organises;

            if($champs_liste_choix == null)
                $champs = Champ_libre::where('liste_choix',$cle)->get();
            else
                $champs = $champs_liste_choix[$cle] ?? [];

            // On parcout chaque champ pour affecré la bonne valeur
            foreach ($champs as $champ) {

                $liste = Champ::recuperer_valeur_listes_preenregistrees($cle, $champ,false,$traductions)['liste'];

                $liste_organise = [];

                foreach ($liste as $index => $valeur_liste) {

                    $liste_organise[$index] = array();
                    $liste_organise[$index]['id_valeur'] = $index;
                    $liste_organise[$index]['valeur'] = $valeur_liste;
                    $liste_organise[$index]['id_liste_choix'] = $cle;

                }

                if ($liste !== $tableau_par_defaut) {

                    $nom = $champ->type_element . '.' . $champ->nom_sql;

                    $champs_listes_formatees[$cle][$nom] = $liste_organise;
                }

            }

        }

        else {

            $listes_formatees_valeurs_organises = array();
            $listes_formatees_valeurs =
                $valeurs_fixe[$cle] ??
                Champ::recuperer_valeur_listes_preenregistrees($cle,null,false,$traductions)['liste'];

            $compteur = 1;

            foreach ($listes_formatees_valeurs as $index => $valeur_liste) {

                $informations = array(
                    'id_valeur' => $index,
                    'valeur' => $valeur_liste,
                    'id_liste_choix' => $cle,
                );

                $listes_formatees_valeurs_organises[$compteur] = $informations;

                $compteur++;
            }

            // Si on a des valeurs éditées via l'interface
            if (!empty($champs_listes_formatees[$cle])) {

                // Cette boucle sert à vérifier que les valeurs qui ont été éditées via l'interface
                // existent toujours dans les valeurs standards
                // sinon, elles ne doivent plus être prises en compte
                foreach ($champs_listes_formatees[$cle] as $index => $liste) {

                    $equivalence = false;

                    foreach ($listes_formatees_valeurs_organises as $liste_valeurs) {

                        // la valeur qui est en BDD (éditée) existe toujours dans les valeurs standards
                        if ($liste_valeurs['id_valeur'] == $liste['id_valeur']) {

                            $equivalence = true;

                            $champs_listes_formatees[$cle][$index]['valeur'] = $liste_valeurs['valeur'];
                        }
                    }

                    // a priori ça veut dire que la valeur n'existe plus dans le standard
                    if ($equivalence === false)
                        unset($champs_listes_formatees[$cle][$index]);

                }
            } else {

                //Sinon on prend les valeurs initiales
                $champs_listes_formatees[$cle] = $listes_formatees_valeurs_organises;
            }

        }
    }

    public static function valeurs_listes_libres($cle,&$champs_libre_liste,$traductions = array(),$liaisons = false){

        if($liaisons === false)
            $liaisons = Champ_libre_management::liaisons_valeurs_listes_libres($cle);

        if (!isset($champs_libre_liste[$cle][0])) {
            $champs_libre_liste[$cle][0] = array(
                'id_valeur' => 0,
                'valeur' => 'Sans Valeur',
                'id_cl' => $cle,
                'categorie' => null,
                'ordre' => -1
            );
        }

        //On organise par catégorie si nécessaire
        $champs_libre_liste_organisee = array();

        $categorie = '0';

        usort($champs_libre_liste[$cle], function ($champ_liste_1, $champ_liste_2) {
            return $champ_liste_1['ordre'] <=> $champ_liste_2['ordre'];
        });

        foreach ($champs_libre_liste[$cle] as $valeur) {

            if(!empty($valeur['index_traduction'])) {

                $index_traduction = $valeur['index_traduction'] . '.nom';

                $index_traduction_categorie = $valeur['index_traduction'] . '.categorie';

                if(!empty($traductions)) {

                    $valeur['valeur'] = $index_traduction;
                    $valeur['categorie'] = $index_traduction_categorie;

                    if(isset($traductions[$index_traduction]))
                        $valeur['valeur'] = $traductions[$index_traduction];

                    if(isset($traductions[$index_traduction_categorie]))
                        $valeur['categorie'] = $traductions[$index_traduction_categorie];
                }
                else {

                    $valeur['valeur'] = traduction($index_traduction);
                    $valeur['categorie'] = traduction($index_traduction_categorie);

                }
            }

            if (!empty($valeur['categorie'])) {
                $champs_libre_liste_organisee[$valeur['categorie']][] = $valeur;
                $categorie = '1';
            }
            else
                $champs_libre_liste_organisee['sans_categorie'][] = $valeur;

        }

        $champs_libre_liste[$cle] = $champs_libre_liste_organisee;

        $champs_libre_liste[$cle]['liaisons'] = $liaisons;
        $champs_libre_liste[$cle]['categorie'] = $categorie;
    }


    /**
     * @param $cle
     * @return array|mixed
     *
     * Permet de récupérer les valeurs d'une liste formatée
     *
     */
    public static function valeurs_liste_formatee($cle) {

        $listes_formatees_valeurs_organises = array();
        $listes_formatees_valeurs = Champ::recuperer_valeur_listes_preenregistrees($cle)['liste'];

        $champs_listes_formatees = Champs_liste_formatee::where('desactivee','!=',1)->where('id_liste_choix',$cle)->orderBy('ordre')->get()->keyBy('id_valeur')->toArray();

        $champs_listes_formatees_trie = array();

        foreach($champs_listes_formatees as $cle_formatees => $champ){

            $champs_listes_formatees_trie[$champ['id_liste_choix']][$cle_formatees] = $champ;

        }

        $champs_listes_formatees = $champs_listes_formatees_trie;

        foreach($listes_formatees_valeurs as $index => $valeur_liste){

            $listes_formatees_valeurs_organises[$index] = array();
            $listes_formatees_valeurs_organises[$index]['id_valeur'] = $index;
            $listes_formatees_valeurs_organises[$index]['valeur'] = $valeur_liste;
            $listes_formatees_valeurs_organises[$index]['id_liste_choix'] = $cle;
        }

        if(isset($champs_listes_formatees[$cle])){

            foreach($champs_listes_formatees[$cle] as $index => $liste){

                if ($liste['valeur'] == null || $liste['valeur'] == '') {
                    $champs_listes_formatees[$cle][$index]['valeur'] = $listes_formatees_valeurs_organises[$liste['id_valeur']]['valeur'];
                }
            }
        }

        else
            $champs_listes_formatees[$cle] = $listes_formatees_valeurs_organises;

        return $champs_listes_formatees[$cle];
    }

    /**
     *
     * Permet de génerer le fichier de traduction pour l'utilisateur
     *
     */
    public static function genere_traductions() {

        $version_composants = intval(parametre('version_composants'));

        if($version_composants == null)
            parametre('version_composants',1);
        else{
            $version_composants++;
            parametre('version_composants',$version_composants);
        }

        $langues = modele('traduction_langue')->get();

        self::partage_oublie_traductions();

        $traductions_valeurs_fr = modele('traduction_valeur')
            ->select('index', DB::raw('COALESCE(traduction_specifique,traduction_standard) AS valeur'))
            ->where('langue', 'fr')
            ->get()->toArray();

        $traductions_valeurs_par_defaut = [];

        foreach($traductions_valeurs_fr as $traduction_valeur_fr){
            $traductions_valeurs_par_defaut[$traduction_valeur_fr['index']] = $traduction_valeur_fr['valeur'];
        }

        $traductions_valeurs = modele('traduction_valeur')
            ->select('index','langue', DB::raw('COALESCE(traduction_specifique,traduction_standard) AS valeur'))
            ->get()->toArray();

        $traductions_valeurs_par_langue = [];

        foreach($traductions_valeurs as $traduction_valeur){
            $traductions_valeurs_par_langue[$traduction_valeur['langue']][$traduction_valeur['index']] = $traduction_valeur['valeur'];
        }

        foreach($langues as $langue) {

            $traductions_valeurs = $traductions_valeurs_par_langue[$langue->code] ?? [];

            $valeurs = json_encode(array_merge($traductions_valeurs_par_defaut,$traductions_valeurs));

            \Storage::put('public/traductions/langue_' . $langue->code . '.json', $valeurs);

            \Storage::put('public/traductions/langue_' . $langue->code . '.js', 'var traductions_valeurs = '.$valeurs.';');

        }

        return true;
    }

    public static function recupere_modules_repertoire($fichiers, &$modules, $nom_dossier = '', $nom_module = false, $dossier_parent = null)
    {
        foreach ($fichiers as $fichier) {

            if (in_array($fichier, array('.', '..', 'include', 'html', 'liste_libre.blade.php')))
                continue;

            if (strpos($fichier, '.old') !== false)
                continue;

            $emplacement_fichier = $nom_dossier . '/' . $fichier;
            $nom_du_fichier = str_replace('.blade.php', '', $fichier);

            if (is_dir($emplacement_fichier) && $nom_du_fichier != $nom_module) {

                $dossier_parent = $dossier_parent != null ? $dossier_parent . '.' . $nom_du_fichier : $nom_du_fichier;
                self::recupere_modules_repertoire(scandir($emplacement_fichier), $modules, $emplacement_fichier, $nom_module, $dossier_parent);
                $dossier_parent = null;
                continue;
            }

            $chemin_base = base_path();
            $nom_dossier_pour_vue = str_replace([$chemin_base . '/app/Eden/Views/', $chemin_base . '/resources/views/vendor/eden/', '/'], ['', '', '.'], $nom_dossier);

            $emplacement_fichier = $nom_dossier_pour_vue . '.' . $nom_du_fichier;

            if ($nom_module != false) {

                $nom_du_fichier = $dossier_parent != null ? $dossier_parent . '.' . $nom_du_fichier : $nom_du_fichier;

                if ($nom_du_fichier == $nom_module)
                    $modules[] = $emplacement_fichier;
            } else
                $modules[] = $emplacement_fichier;
        }
    }
}
