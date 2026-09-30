<?php

namespace App\Eden\Managements\Elements;

use Mail;

class Compte_email_management extends Element_management {
	
	/**
	 * @cf description sur Element_management
	 * 
	 * On ajoute automatiquement un token s'il n'existe pas
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		$envoyer_email = false;
		
		if(empty($this->modele) || empty($this->modele->id)) {
			
			$modifications['token'] = md5(time());
			$modifications['valide'] = 0;
			$envoyer_email = true;
		}
		else {
			
			// modification d'une adresse email
			if(isset($modifications['adresse_email']) && $modifications['adresse_email'] != $this->modele->adresse_email) {
				
				$modifications['token'] = md5(time());
				$modifications['valide'] = 0;
				$envoyer_email = true;
			}
			
			// on renvoie juste l'email
			if($this->modele->valide == 0 && (!isset($modifications['valide']) || $modifications['valide'] != 1)) {
				
				$envoyer_email = true;
			}
		}

        $utilisateur = modele('utilisateur')
            ->select('email','langue', 'prenom')
            ->where('id', empty($modifications['utilisateur_id']) ? $this->modele->utilisateur_id : $modifications['utilisateur_id'])
            ->first()
            ->toArray();

        if(isset($modifications['type_de_compte']) && $modifications['type_de_compte'] == 1){

            $email_utilisateur = $utilisateur['email'];
            $modifications['adresse_email'] = $email_utilisateur;

            //On réinitialise les valeurs des champs indisponibles pour ce type de compte
            $modifications['nom_expediteur'] = null;
            $modifications['configuration_email'] = null;
        }

		$retour = parent::enregistre($modifications, $modele);
		
		if($retour === true && $envoyer_email === true) {

            if($modifications['type_de_compte'] == 1)
                $destinataire = $email_utilisateur;
            else
			    $destinataire = $this->modele->adresse_email;

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 1,
                'destinataire' => [$destinataire],
                'sujet' => traduction('mails.validation_compte_email.objet', $utilisateur['langue']),
            ];

            $variables_email = [
                'lien_validation_compte_email' => route('email.valider_compte', ['id' => $this->modele->id, 'token' => $this->modele->token]),
                'utilisateur' => $utilisateur,
            ];

            $retour = $service_email->envoyer('eden::mails.validation_compte_email', $variables_email, $parametres_email);
		}
		
		return $retour;		
	}

	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		$comptes_emails = modele('compte_email')
			->where('id', '!=', $this->modele->id)
			->where('utilisateur_id', $this->modele->utilisateur_id)
			->get();

		// on met à jour la marge sur le projet
		if(!empty($modifications['par_defaut'])) {

			foreach($comptes_emails as $compte_email) {

				management('compte_email',$compte_email->id,$compte_email)->enregistre_modele(['par_defaut' => 0]);
			}
		}
		elseif(empty($this->modele->par_defaut)){

			if($comptes_emails->where('par_defaut',1)->count() ==  0)
				$this->enregistre_modele(['par_defaut' => 1]);
		}
		
	}
	
}