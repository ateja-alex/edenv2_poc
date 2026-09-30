<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Vuejs;
use App\Eden\Managements\Maintenance_management;

use App\Eden\Models\Utilisateur;
use App\Eden\Models\Table_libre;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;

class Utilisateur_management extends Element_management {

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * on vérifie email et mot de passe et on enregistre les modifications
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        if(!empty($modifications['envoyer_identifiants'])) {
            $this->envoyer_identifiants = true;
            unset($modifications['envoyer_identifiants']);
        }

		$retour = $this->verifie_informations_pour_utilisateur($modifications);

		if($retour !== true) {

			return $retour;
		}

        if(!empty($modifications['licence_id'])){
            $type_utilisateur = (!empty($modifications['type_utilisateur']) ? $modifications['type_utilisateur'] : (isset($this->modele->type_utilisateur) ? $this->modele->type_utilisateur : null));
            if(($type_utilisateur == 1 && moi()->type_utilisateur == 1) || ($type_utilisateur == 2 && moi()->type_utilisateur != 2)){
                return traduction('messages.php.licence.droits_licence_incorrects');
            }
        }

        if(!empty($modifications['licence_id']) && (empty($this->modele) || $modifications['licence_id'] != $this->modele->licence_id)){

            $licence = modele('licence',$modifications['licence_id']);

            if($licence->nombre_utilises >= $licence->nombre)
                return traduction('messages.php.licence.affectation_licence_impossible');
        }

		return parent::enregistre($modifications);
	}

	/**
	 *
	 * Vérifie les champs obligatoires
	 *
	 * @param $modele le modèle ou on doit vérifier les champs obligatoires
	 * (si le modèle existe, les champs obligatoires ne sont pas obligatoires !)
	 * @param $modifications array, la liste des modifications
	 *
	 * @return true si pas de problème, une erreur (string) sinon
	 *
	 */
	protected function verifie_champs_obligatoires($modele, $modifications) {

		if(isset($modifications['type_utilisateur']) && $modifications['type_utilisateur'] != 3)
			return parent::verifie_champs_obligatoires($modele, $modifications);

		// c'est une ressource, on doit gérer un cas particulier
		$table_libre = table_libre($this->_type_element);
		$champs_libres = $table_libre->champs_libres()->where('obligatoire', 1)->get();

		foreach($champs_libres as $key => $champ_libre) {

			if($champ_libre->nom_sql == 'email')
				$champs_libres->forget($key);

			if($champ_libre->nom_sql == 'prenom')
				$champs_libres->forget($key);

			if($champ_libre->nom_sql == 'mot_de_passe')
				$champs_libres->forget($key);
		}

		return $this->verifie_champs_obligatoires_avec_champs($modele, $modifications, $champs_libres);
	}

	/**
	 *
	 * Vérifie informations à propos d'un utilisateur pour l'enregistrement (autre qu'une ressource)
	 *
	 */
	public function verifie_informations_pour_utilisateur(&$modifications) {

		if(isset($modifications['type_utilisateur']) && $modifications['type_utilisateur'] == 3)
			return true;
		if(isset($modifications["mot_de_passe"]) && !isset($modifications["mot_de_passe_verification"]) ) {
			return traduction('messages.php.connexion.mdp_differents');
		}

		// vérification mot de passe
		if(isset($modifications["mot_de_passe"]) && $modifications["mot_de_passe"] != "" && $modifications["mot_de_passe"] != $modifications["mot_de_passe_verification"] ) {

			return traduction('messages.php.connexion.mdp_differents');
		}

		// vérification email
		if(isset($modifications["email"])) {

			$verification_email = modele('utilisateur')->where('email', $modifications["email"]);

			if($this->modele !== null) {

				$verification_email->where('id', '!=', $this->modele->id);
			}

			if($verification_email->first() !== null) {

				return traduction('messages.php.utilisateur.email_deja_utilise');
			}
		}

		if(isset($modifications["mot_de_passe"]) && !empty($modifications["mot_de_passe"]))
			$modifications["mot_de_passe"] = $this->ed_crypt($modifications['mot_de_passe']);
		elseif(empty($modifications["mot_de_passe"]))
			unset($modifications["mot_de_passe"]);

		// création des clés d'api
		if(empty($this->modele->api_cle_publique)) {

			$modifications['api_cle_publique'] = $this->cle_api();
			$modifications['api_cle_privee'] = $this->cle_api();
		}

		if(isset($modifications['mot_de_passe_verification']) || request()->has('mot_de_passe_verification'))
			unset($modifications['mot_de_passe_verification']);


		// Si l'utilisateur est un Éditeur, on lui accorde le droit d'Usurpation
		if(isset($modifications['type_utilisateur']) && $modifications['type_utilisateur'] == '2') {

			$modifications['droit_usurpation'] = '1';
		}

        $entite_acces = [];

        if (!empty($modifications['entite_acces'])) {
            foreach($modifications['entite_acces'] as $id_entite) {
                $entite_acces[$id_entite] = true;
            }
        }

		if(!empty($modifications['entite_id_defaut']) && $modifications['acces_toutes_entites'] != 1) {	

			$entites_utilisateur = !empty($modifications['entites']) ? $modifications['entites'] : $this->modele->entites;
			
			if(!in_array($modifications['entite_id_defaut'], $entites_utilisateur)) {

				return traduction('messages.php.utilisateur.entite_defaut_invalide');
			}
		}

        $entite_defaut = [];

        if (!empty($modifications['entite_defaut'])) {
            foreach($modifications['entite_defaut'] as $id_entite) {
                $entite_defaut[$id_entite] = true;
            }
        }

        $modifications['entite_defaut'] = base64_encode(json_encode($entite_defaut));
        $modifications['entite_acces'] = base64_encode(json_encode($entite_acces));

		return true;
	}

	/**
	 *
	 * @todo à décrire
	 *
	 */
	public function enregistrer_modification_utilisateur_connecte($modifications = array(), $modele = false) {

		if(!empty($modifications["mot_de_passe"])){

			$mot_de_passe = $modifications["mot_de_passe"];
			$confirmation_mot_de_passe = $modifications["mot_de_passe_verification"] ?? '';

			if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $mot_de_passe))
				return traduction('messages.php.connexion.mdp_invalide');

			if($mot_de_passe != $confirmation_mot_de_passe)
				return traduction('messages.php.connexion.mdp_differents');

			$nouveau_mdp = $this->ed_crypt($mot_de_passe);
			
			if($this->modele->mot_de_passe == $nouveau_mdp)
				return traduction('messages.php.connexion.mdp_pareils_precedent');

			$modifications["mot_de_passe"] = $nouveau_mdp;

			if(array_key_exists("mot_de_passe_verification", $modifications))
				unset($modifications["mot_de_passe_verification"]);

			$modifications['renouveler_mot_de_passe'] = 0;
			$modifications['date_derniere_modification_mot_de_passe'] = date('Y-m-d H:i:s');
		}
		else if(array_key_exists("mot_de_passe", $modifications))
			unset($modifications["mot_de_passe"]);

		return parent::enregistre($modifications);
	}

	/**
	 *
	 * Crypte le mot de passe
	 *
	 * @return string le mot de passe crypté
	 *
	 */
	public function ed_crypt($mot_de_passe) {

		return md5('easy' . $mot_de_passe . 'dev');
	}

	/**
	*
	* @cf description sur Element_management
	*
	* On vide le cache
	*
	*/
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        //Si l'utilisateur est de type ressource, il n'est pas autorisé à se connecter
        if(isset($modifications['type_utilisateur']) && $modifications['type_utilisateur'] == 3)
            $this->enregistre_modele(['autorise_a_se_connecter' => false,'licence_id' => null]);

        if(($this->modele->type_utilisateur == 2 || $this->modele->autorise_a_se_connecter != 1) && !empty($this->modele->licence_id))
            $this->enregistre_modele(['licence_id' => null]);

        if(!empty($this->envoyer_identifiants))
            $this->envoyer_logs_par_mail();

		// on vide le cache
		Cache_management::invalide();
		Vuejs::supprime_fichier_filtres();

        // uniquement à la création ou à un changement effectif de profil, pas à chaque enregistrement
        if(empty($modele_avant) || empty($modele_avant->id) || $modele_avant->profil_id != $modele->profil_id)
            Cache_management::genere_valeurs_champs_listes($modele);


		$this->mettre_a_jour_droits_toutes_entites($modele);


		parent::methodes_post_modification($modele, $modele_avant, $modifications);

	}

	/**
	 *
	 * Met à jour l'utilisateur si il a le droit d'accéder à toutes les entités
	 *
	 */
	static public function mettre_a_jour_droits_toutes_entites($modele) {

		// Si l'utilisateur a accès à toutes les entités
		if($modele->acces_toutes_entites == '1') {

			// On recrée son champ entite_defaut
			$element = Utilisateur::find($modele->id);

			$entite_defaut = array();

			foreach (modele('entite')->get() as $entite) {

				$entite_defaut[$entite->id] = true;
			}

			$entite_defaut = json_encode($entite_defaut);
			$entite_defaut = base64_encode($entite_defaut);

			// On met à jour l'utilisateur
			$element->entite_defaut = $entite_defaut;
			$element->save();
		}
	}

	/**
 	 *
	 * @cf description sur Element_management
	 *
	 */
	protected function methodes_post_suppression($modele) {

		// on vide le cache
		Cache_management::invalide();
		Vuejs::supprime_fichier_filtres();

        if(!empty($modele->licence_id)) {

            $licence_id = $modele->licence_id;

            $this->enregistre_modele(['licence_id' => null]);

            DB::select('UPDATE licence SET nombre_utilises = (SELECT count(*) from utilisateur where utilisateur.licence_id = licence.id and type_utilisateur != 2 and coalesce(inactif,0) = 0) WHERE id = '.$licence_id);
        }

		parent::methodes_post_suppression($modele);
	}

	/**
	*
	* On modifie le mode de paramètrage de l'utilisateur
	*
	*/
	public function changer_mode_parametrage($mode_parametrage) {


		// on enregistre le mode de paramètrage de l'utilisateur en bdd
		$this->enregistre_modele(array('mode_parametrage' => $mode_parametrage));

		// on met à jour la session
		$utilisateur = Utilisateur::where('id',moi()->id)->first();
		session()->put('utilisateur_eden',$utilisateur);

		// on vide le cache
		Cache_management::vider(true);
	}

	/**
	 *
     * Fonction créant une clé pour l'api en s'assurant qu'elle est unique
     *
     * @return la clé d'api
	 *
     */
	private function cle_api() {

        $cle_api = md5(random_bytes(32));

        while(modele('utilisateur')->where('api_cle_publique', $cle_api)->orWhere('api_cle_privee', $cle_api)->get()->count() > 0) {

            $cle_api = md5(random_bytes(32));
        }

        return $cle_api;
    }

    /**
	 *
     * Fonction créant une array contenant la liste des raccourcis de l'utilisateur
     *
     * @return la liste des raccourcis de l'utilisateur connecté
	 *
     */
    public function raccourcis() {

    	// On récupère l'user actuel
    	$utilisateur = session()->get('utilisateur_eden');
    	$id_utilisateur = $utilisateur->id;
    	$les_raccourcis = moi()->raccourcis;

    	$raccourcis = array();
    	$raccourcis['array_raccourcis_liste'] = array();
    	$raccourcis['array_raccourcis_saisie'] = array();
		/*
    	$les_raccourcis = json_decode($les_raccourcis[0]->raccourcis);
    	$les_raccourcis_liste = json_decode($les_raccourcis->raccourcis_liste);
    	$raccourcis_saisie = json_decode($les_raccourcis->raccourcis_saisie);
    	// on met dans l'array $raccourcis les raccourcis de l'user
    	foreach ($les_raccourcis_liste as $id => $osef) {
    		$table_libre = Table_libre::select('type_element','element')->where('id',$id)->first();
    		$raccourcis['array_raccourcis_liste'][] = array(
    			'id' => $id,
    			'nom' => 'Liste '.$table_libre->element,
    			'route' => route('base_eden.liste.index',['type_element'=>$table_libre->type_element]),
    		);
    	}
    	foreach ($raccourcis_saisie as $id => $osef) {

			$table_libre = Table_libre::select('type_element','element')->where('id',$id)->first();
    		$raccourcis['array_raccourcis_saisie'][] = array(
    			'id' => $id,
    			'nom' => 'Saisie '.$table_libre->element,
    			'route' => route('formulaire_nouvel_element_generique',['type_element'=>$table_libre->type_element]),
    		);
    	}
		*/

    	return $raccourcis;
    }

	/**
	 *
	 *
	 * Vérifie si on peut se connecter sur un autre compte
	 *
	 */
	public function droit_usurpation() {

        if(moi() === null)
			exit;


		if(moi()->profil_id !== null && moi()->profil_id !== 0 && moi()->droit_usurpation != 1)
			exit;
    }


    public function conditions_specifiques_recherche($element, $filtrage = false) {

    	// Si l'utilisateur n'est pas un admin, on ne retourne pas les admins
    	if(!editeur())
			$element = $element->where(function($condition){
				$condition->where('type_utilisateur', '!=', '2')
					->orWhereNull('type_utilisateur');
			});
        
		return parent::conditions_specifiques_recherche($element, $filtrage);
    }

    /**
     *
     * On envoie les logs de l'utilisateur par mail
     *
     */
    public function envoyer_logs_par_mail(){

        $token = Str::random(16);

        // On enregistre le token de l'utilisateur
        $this->enregistre_modele(array('token_initialisation_mot_de_passe' => $token));

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$this->modele->email],
            'sujet' => 'Bienvenue / Welcome',
        ];

        $variables_email = [
            'url' => route('initialisation_mot_de_passe', ['token' => $token])
        ];

        return $service_email->envoyer('eden::mails.envoie_identifiants_erp', $variables_email, $parametres_email);
    }

    /**
     *
     * Permet de charger les valeurs des entités accessibles au moment de la connexion
     *
     */
    public function chargement_entites(){

        $nombre_entite = modele('entite')->count();

        if($nombre_entite > 1)
            $this->modele->entites = collect(DB::select('WITH RECURSIVE cte AS (
                        SELECT valeur as entite_id
                        FROM utilisateur_entites
                        WHERE cle_locale = ' . $this->modele->id . '
                        UNION ALL
                        SELECT id as entite_id
                        FROM entite
                        JOIN cte ON entite.entite_parent = cte.entite_id
                    )
                    SELECT entite_id
                    FROM cte'))->pluck('entite_id')->toArray();
        else
            $this->modele->acces_toutes_entites = 1;
    }

	public function mot_de_passe_a_renouveler(){

		if($this->modele->type_utilisateur === 2)
			return false;
		
		if($this->modele->renouveler_mot_de_passe == 1)
			return true;

		if(fonctionnalite('renouvellement_mot_de_passe_utilisateur') === false)
			return false;

		$jours = fonctionnalite('duree_validite_mot_de_passe_utilisateur');

		if($this->modele->date_derniere_modification_mot_de_passe >= date('Y-m-d H:i:s', strtotime('-' . $jours . ' days')))
			return false;

		$this->enregistre_modele(['renouveler_mot_de_passe' => 1]);

		return true;
	}

}
