<?php

namespace App\Eden\Middlewares;

/*
use App\Eden\Models\Elements\Utilisateur;

use Illuminate\Support\Facades\Cookie; 
*/

use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;
use Closure;
use App;
use Cookie;
use App\Eden\Vuejs;
use App\Eden\Managements\Familles_management;
use Illuminate\Support\Facades\Cache;

use App\Eden\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Eden_utilisateur_connecte{

    public $type = 1;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next){

        $route = $request->route();

        if(!empty($route)){

            $params_a_lowercase = ['type_element', 'nom_sql'];

            foreach ($params_a_lowercase as $param) {
                $valeur = $route->parameter($param);
                if ($valeur && $valeur !== strtolower($valeur)) {
                    $route->setParameter($param, strtolower($valeur));
                }
            }
        }
		
		$remember_token = Cookie::get('remember_token');
		
		if($remember_token !== null && !session()->has('utilisateur_eden')) {

			// on ne peut pas passer par modele() ici
			// car si la gestion des droits est appliquée sur la table utilisateur,
			// nous avons un bug
			$utilisateur = Utilisateur::where('remember_token', $remember_token)->first();

			if($utilisateur !== null && $utilisateur->service != 1) {

				// on met l'utilisateur en session
				session()->put('utilisateur_eden', $utilisateur);
			}
            elseif ($utilisateur !== null && $utilisateur->service == 1)
                return redirect()->route('acces_restreint');
        }


		// on regarde si on est sur une connexion auto (type cron)
		if(request()->has('eden_cron_id_utilisateur') && request()->has('eden_cron_mdp')) {
			
			$utilisateur = Utilisateur::find(request()->get('eden_cron_id_utilisateur'));
			session()->put('utilisateur_eden', $utilisateur);
		}
		
		// l'utilisateur se connecte via l'extranet
		$connexion_extranet = false;
		
		if(session()->has('utilisateur_eden_extranet') && !empty(session()->get('utilisateur_eden_extranet'))) {
			
			$connexion_extranet = true;
		}
		else {
			
			if(!session()->has('utilisateur_eden')) {
				
				// Si l'utilisateur vient d'un GET, pas d'un POST, et ne demandait pas l'accès à la page d'accueil, on le redirige
				if($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['REQUEST_URI'] != '/eden/accueil' &&  $_SERVER['REQUEST_URI'] != '/eden/login')
					session(['redirection_post_login' => $_SERVER['REQUEST_URI']]);
                else
                    session()->forget('redirection_post_login');

				// l'utilisateur n'est pas connecté, on redirige
				session(['requete_client' => $request->path()]);

				if(fonctionnalite('utiliser_extranet') == true && fonctionnalite('url_extranet') != "" && $_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet'))
					return redirect()->route('extranet.login');
				else
					return redirect()->route('login');
			} 
		}

		if(session()->has('utilisateur_eden')) {

            if(session('utilisateur_eden')->service == 1)
                return redirect()->route('acces_restreint');

			// on définit son id_utilisateur dans une constante
			define('id_utilisateur', session()->get('utilisateur_eden')->id);
		}

        $cache = app('App\Eden\Managements\Cache_management');

		$cache->verifie_si_cache_obsolete();

        if($connexion_extranet === false) {

            if (!session()->has('cache.droits_licences'))
                $this->droits_licences();

            $this->verification_droits_licences();
        }

        if (!session()->has('cache.droits_profils'))
            $this->droits_profils();
		
		// on définit les filtres vuejs
		Vuejs::genere_filtres_vuejs();
		
		// on met en cache les informations des familles
		Familles_management::genere_cache();

		// Si l'utilisateur n'a pas de langue en session, on la met à Français 
		if (!session()->has('locale')) 
			session()->put('locale','fr');

        if(fonctionnalite('intranet') && !empty(moi()) && $this->type == 1) {
            if (moi()->autorisation_intranet == 3 && strpos($request->route()->getName(),'intranet.') !== false)
                return redirect()->route('base_eden.accueil.index');
            else if (moi()->autorisation_intranet == 2 && strpos($request->route()->getName(), 'intranet.') === false) {

                $routes_autorisees = array(
                    'eden/element',
                    'eden/formulaire/affichage',
                    'eden/parametrage/champs_libres',
                    'eden/champs/valeurs',
                    'eden/champs/filtrage',
                    'eden/recherche_avancee',
                    'eden/note_de_frais/informations',
                    'eden/planning',
                    'eden/calendrier',
                    'eden/bibliotheque',
                    'eden/saisie_des_temps',
                );

                foreach($routes_autorisees as $route){

                    if(strpos($request->route()->uri(),$route) !== false)
                        return $next($request);
                }

                if(strpos($request->route()->uri(),'eden/liste') !== false){

                    $alias_route = $request->route()->action['as'];

                    if(!in_array($alias_route,array('liste.index','liste.avec_filtre', 'liste.kanban',
                        'liste.kanban_avec_somme','liste.kanban_avec_nombre')))
                        return $next($request);
                }

                return redirect()->route('intranet.index');
            }
        }
        elseif($this->type == 1 && strpos($request->route()->getName(),'intranet.') !== false)
            abort(404);

		return $next($request);
    }

    /**
     *
     * Permet de gérer les licences
     *
     */
    public function droits_licences(){

        $utilisateur = session()->get('utilisateur_eden');

        if(empty($utilisateur->licence_id) && !editeur())
            abort(403);

        if(!empty($utilisateur->licence_id)){

            $elements = modele('licence_element')
                ->where('licence_id',$utilisateur->licence_id)
                ->get()
                ->groupBy('type')->map(function($type){
                    return $type->pluck('nom');
                })->toArray();

            if(isset($elements[1])){

                foreach($elements[1] as &$route){
                    $route .='.';
                }
            }

            if(isset($elements[3])){

                $modules = [];

                foreach($elements[3] as $element){

                    if(strpos($element,'.') === false) {

                        $modules[] = $element;
                        continue;
                    }

                    $module = explode('.',$element);

                    $modules[$module[0]][] = $module[1];
                }

                $elements[3] = $modules;
            }

            session()->put('cache.droits_licences',$elements);
        }
    }

    /**
     *
     * Permet de gérer les profils
     *
     */
    public function droits_profils(){

        $profil_id = !empty(moi()) ? moi()->profil_id : moi_extranet()->profil;

        if(!empty($profil_id)){

            $droits_profils = modele('profil_droits_element')
                ->where('profil_id',$profil_id)
                ->select('profil_droits_element.*',DB::raw('COALESCE(entite_id,0) as entite_id'))
                ->get()->groupBy(['type_element','entite_id']);

            session()->put('cache.droits_profils.element',$droits_profils);

            $droits_profils_divers = modele('profil_droits_divers')
                ->join('profil_droits_divers_profils as pddp2','pddp2.cle_locale','profil_droits_divers.id')
                ->leftJoin('profil_droits_divers_profils as pddp',function($join) use ($profil_id) {
                    $join->on('pddp.cle_locale','profil_droits_divers.id')
                        ->where('pddp.valeur',$profil_id);
                })
                ->whereNull('pddp.id')
                ->select('type','index')
                ->groupBy('type','index')
                ->get()->groupBy('type')->map(function($array){
                    return $array->pluck('index');
                })->toArray();

            session()->put('cache.droits_profils.divers_non_acces',$droits_profils_divers);
        }
        else
            session()->put('cache.droits_profils',false);
    }

    /**
     *
     * Vérification des droits de la licence
     *
     */
    public function verification_droits_licences(){

        if(editeur())
            return;

        $route = \Route::getCurrentRoute();

        if(session()->has('cache.droits_licences.2')) {

            $types_elements = session()->get('cache.droits_licences.2');

            $parametres = request()->route()->parameters();

            if(isset($parametres['type_element']))
                $type_element = $parametres['type_element'];

            else if(isset($parametres['id_liste'])){

                $liste = Liste_libre::find($parametres['id_liste']);

                $type_element = $liste->type_element;
            }

            else if(isset($parametres['id_rapport'])){

                $rapport_modele = Rapport_libre::where('id_rapport',$parametres['id_rapport'])->first();

                if(empty($rapport_modele))
                    abort(404);

                $type_element = $rapport_modele->type_element;
            }

            if(!empty($type_element)) {

                $table_libre = table_libre($type_element);

                if (!in_array($type_element, $types_elements) && !in_array('tous',$types_elements) && empty($table_libre->table_systeme))
                    abort(403);
            }
        }

        if(session()->has('cache.droits_licences.1')) {

            $droits_routes = session()->get('cache.droits_licences.1');

            $droits_routes[] = 'base_eden.';

            if(in_array('tous.',$droits_routes))
                return;

            foreach($droits_routes as $droit_route){

                if (strpos($route->getName(),$droit_route) !== false)
                    return;
            }

            abort(403);
        }
    }
}
