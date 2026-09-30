<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use Mail;

class Employe_demande_conge_management extends Element_management {

    private $utilisateurs = null;

    /**
     *
     *  On gére l'ajout de la date de demande et la vérification date de début et date de fin
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        if(isset($this->modele->statut)) {

            if($this->modele->statut == 2 && moi()->id === $this->modele->employe_id)
                    $modifications = array_merge($modifications, [
                        'statut' => 0,
                        'valide_n1' => 0,
                        'valide_n2' => 0,
                    ]);
            else if($this->modele->statut >= 1)
                return traduction('messages.php.element_non_modifiable');
        }

        if(isset($modifications['date_de_debut']) && isset($modifications['date_de_fin'])) {

            $date_debut = formate_date('Y-m-d', $modifications['date_de_debut']);
            $date_fin = formate_date('Y-m-d', $modifications['date_de_fin']);

            $periode_debut = $modifications['periode_de_debut'];
            $periode_fin   = $modifications['periode_de_fin'];

            $verification = modele('employe_demande_conge')
                ->select('id')
                ->where(function ($r) {
                    $r->whereIn('statut', [0,1])
                        ->orWhereNull('statut');
                })
                ->where(function ($r) use ($date_fin, $periode_fin) {
                    $r->where('date_de_debut', '<', $date_fin)
                        ->orWhere(function ($r) use ($date_fin, $periode_fin) {
                            $r->where('date_de_debut', '=', $date_fin)
                                ->where('periode_de_debut', '<=', $periode_fin);
                        });
                })
                ->where(function ($r) use ($date_debut, $periode_debut) {
                    $r->where('date_de_fin', '>', $date_debut)
                        ->orWhere(function ($r) use ($date_debut, $periode_debut) {
                            $r->where('date_de_fin', '=', $date_debut)
                                ->where('periode_de_fin', '>=', $periode_debut);
                        });
                })
                ->where('employe_id', $modifications['employe_id']);

            if(isset($this->modele->id))
                $verification = $verification->where('id', '!=', $this->modele->id);

            $verification = $verification->first();

            if(!empty($verification))
                return traduction('messages.php.intranet.demande_deja_existante');

            if ($date_debut > $date_fin || ($date_debut == $date_fin && $periode_debut > $periode_fin))
                return traduction('messages.php.intranet.dates_incorrect');
        }

        return parent::enregistre($modifications, $modele);
    }

    /**
     *
     * On envoie un mail de validation à la création
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){
        
        if((!isset($modele_avant->id) || $modele_avant->statut == 2 && empty($modele->statut))  && fonctionnalite('emails_actifs_intranet')['demande_conge'])
            $this->email_validation_demande();

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    /**
	 *
	 * Défini comment une tache doit être affichée sur le planning
	 *
	 */
	public function affiche_sur_planning() {

		return $this->affiche();
	}

	/**
	 * 
	 * 
	 * On envoie un email pour valider la demande de congé
	 * 
	 */
	public function email_validation_demande() {

        $utilisateur = modele('utilisateur')
            ->where('id', $this->modele->employe_id)
            ->first();
        
		$modele_demande_conge = [
			'modele' => clone $this->modele,
			'employe' => $utilisateur
		];

        $destinataires = [];

		// Si le n+1 existe, et qu'il n'a pas encore validé la demande de congé
		if(empty($this->modele->valide_n1)) {

            $validateurs_n_plus_1 = modele('utilisateur')
                ->select('utilisateur.*')
                ->join('utilisateur_validation_conges_n_plus_1','valeur','utilisateur.id')
                ->where('cle_locale',$this->modele->employe_id)
                ->get();

            foreach($validateurs_n_plus_1 as $validateur){

                $modele_demande_conge['sujet'] = traduction('mails.validation_demande.merci_de_valider_demande_conges', $utilisateur->n_plus_1_langue,
                    array(
                        $validateur->prenom,
                        $validateur->nom,
                        $utilisateur->prenom,
                        $utilisateur->nom
                    )
                );

                $modele_demande_conge['destinataire'] = [
                    'email' => $validateur->email,
                    'prenom' => $validateur->prenom,
                    'nom' => $validateur->nom,
                    'langue' => $validateur->langue,
                ];

                $destinataires[] = $modele_demande_conge;
            }
		}
		// Sinon, on envoie un email au n+2 
		elseif(empty($this->modele->valide_n2)) {

            $validateurs_n_plus_2 = modele('utilisateur')
                ->select('utilisateur.*')
                ->join('utilisateur_validation_conges_n_plus_2','valeur','utilisateur.id')
                ->where('cle_locale',$this->modele->employe_id)
                ->get();

            foreach($validateurs_n_plus_2 as $validateur){

                $modele_demande_conge['sujet'] = traduction('mails.validation_demande.merci_de_valider_demande_conges', $utilisateur->n_plus_1_langue,
                    array(
                        $validateur->prenom,
                        $validateur->nom,
                        $utilisateur->prenom,
                        $utilisateur->nom
                    )
                );

                $modele_demande_conge['destinataire'] = [
                    'email' => $validateur->email,
                    'prenom' => $validateur->prenom,
                    'nom' => $validateur->nom,
                    'langue' => $validateur->langue,
                ];

                $destinataires[] = $modele_demande_conge;
            }
		}

        foreach($destinataires as $demande_conge){

			$periodes = champ_libre('employe_demande_conge','periode_de_debut')->champ->valeurs_possibles;

			$demande_conge['modele']->texte_periode_de_debut = $periodes[$demande_conge['modele']->periode_de_debut];
			$demande_conge['modele']->texte_periode_de_fin = $periodes[$demande_conge['modele']->periode_de_fin];

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 1,
                'destinataire' => [$demande_conge['destinataire']['email']],
                'sujet' => $demande_conge['sujet'],
            ];

            $variables_email = [
                'demande_conge' => $demande_conge
            ];

            $retour = $service_email->envoyer('eden::mails.validation_demande', $variables_email, $parametres_email);
		}
	}

    private function email_demande_traitee(){

        $demande_conge = $this->modele;
        $employe = modele('utilisateur', $demande_conge->employe_id);

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$employe->email],
            'sujet' => traduction('messages.php.mail_demande_conges.sujet'),
        ];

        $variables_email = [
            'demande_conge' => $demande_conge,
            'employe' => $employe
        ];

        return $service_email->envoyer('eden::mails.confirmation_demande', $variables_email, $parametres_email);;
    }
	/**
	 * 
	 * 
	 * Fonction qui met à jour le statut de la demande de congé
	 * 
	 */
	public function validation_demande_conge($reponse) { 

		$demande_conge = $this->modele;
        $fonctionnalite_email = fonctionnalite('emails_actifs_intranet')['demande_conge'] ?? false;
        $donnees = array();

		$employe = modele('utilisateur', $demande_conge->employe_id);
        $management_employe = management('utilisateur', $employe->id, $employe);
        $management_employe->charge_valeurs_champs_multiselection();
		$utilisateur_connecte = moi();

        if((in_array($utilisateur_connecte->id,$employe->validation_conges_n_plus_1) && !empty($this->modele->valide_n1))
            || (in_array($utilisateur_connecte->id,$employe->validation_conges_n_plus_2) && !empty($this->modele->valide_n2)))
            return traduction('messages.php.employe_demande_conge.deja_traite_par_validateur');

		if(in_array($utilisateur_connecte->id,$employe->validation_conges_n_plus_1)) {

			$donnees = ['valide_n1' => $reponse];

			// l'employé ne possède pas de n+2, ou si le n+1 refuse, on met à jour le statut
			if(empty($employe->validation_conges_n_plus_2) || $reponse == 2) 
				$donnees['statut'] = $reponse;
		}
		else if(in_array($utilisateur_connecte->id,$employe->validation_conges_n_plus_2))
			$donnees = ['valide_n2' => $reponse, 'statut' => $reponse];
        else if($utilisateur_connecte->type_utilisateur == 2)
            $donnees['statut'] = $reponse;

        $retour = $this->enregistre($donnees);

        if($retour !== true)
            return $retour;

        // on envoie un email si la demande a été acceptée par le n+1 et qu'il possède un n+2
        if($reponse == 1 && in_array($utilisateur_connecte->id,$employe->validation_conges_n_plus_1) && !empty($employe->validation_conges_n_plus_2) && $fonctionnalite_email)
            $this->email_validation_demande();

        //Si on a un statut = demande acceptée ou refusée donc on prévient l'employé
        if(!empty($this->modele->statut) && $fonctionnalite_email)
            $this->email_demande_traitee();

        return true;
	}

    /**
     *
     *
     * On vérifie les états de la demande
     *
     */
    public function supprime($modele = false)
    {

        //On bloque seulement les utilisateurs normaux si la demande a un statut, les admins ont le droit de supprimer
        if ($this->modele->statut > 0 && moi()->type_utilisateur == 0)
            return traduction('messages.php.note_de_frais.suppression_impossible');

        return parent::supprime($modele);
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        array_unshift($liste_options, 'valider_refuser');

        return $liste_options;
    }
}
