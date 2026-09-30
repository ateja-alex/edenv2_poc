<?php

namespace App\Eden\Middlewares;
class Api{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, \Closure $next){

        $verification_cle_api = $this->verifie_cle_api();

		if($verification_cle_api !== true)
			return $verification_cle_api;

        return $next($request);
    }

    /**
	 *
	 * Vérifie la clé API pour toutes les requêtes
	 *
	 */
	private function verifie_cle_api() {

        // Authentification par clé
        if(!empty(request()->header('x-client-id'))){

            $cle_api = request()->header('x-client-id');

            // Clé API non paramétrée
            if(empty(config('services.eden.cle_api_eden')))
                return response()->json(array('statut' => false, 'donnees' => traduction('messages.php.api.erreur_parametrage_projet'), 'code_erreur' => 405));

            // Clé API erronée
            if($cle_api != config('services.eden.cle_api_eden'))
                return response()->json(array('statut' => false, 'donnees' => traduction('messages.php.api.erreur_cle_api'), 'code_erreur' => 405));

            return true;
        }
        // Authentification par identifiants
        elseif(!empty(request()->header('php-auth-user')) && !empty(request()->header('php-auth-pw'))){

            $nom_utilisateur = request()->header('php-auth-user');
            $mot_de_passe = request()->header('php-auth-pw');

            // Identifiants non configurés
            if(empty(config('services.eden.utilisateur_api_eden')) || empty(config('services.eden.mdp_api_eden')))
                return response()->json(array('statut' => false, 'donnees' => traduction('messages.php.api.erreur_parametrage_identifiants'), 'code_erreur' => 405));

            // Identifiants erronés
            if($nom_utilisateur != config('services.eden.utilisateur_api_eden') || $mot_de_passe != config('services.eden.mdp_api_eden'))
                return response()->json(array('statut' => false, 'donnees' => traduction('messages.php.api.erreur_identifiants'), 'code_erreur' => 405));

            return true;
        }
        // On vérifie le token
    	elseif(!empty(request()->token)){

            if(request()->token != config('services.ticket_eden.cle'))
                return json_encode(['success' => false, 'erreur' => traduction('messages.php.synchro_suivi_recette.token_introuvable',null,[request()->token,config('services.ticket_eden.cle')])]);

            return true;
        }

        // Pas de headers d'authentification
        return response()->json(array('statut' => false, 'donnees' => traduction('messages.php.api.erreur_cle_manquante'), 'code_erreur' => 405));
	}

}