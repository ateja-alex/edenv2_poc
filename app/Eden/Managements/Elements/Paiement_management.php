<?php

namespace App\Eden\Managements\Elements;



use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class Paiement_management extends Element_management {

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * On met à jour le solde du document
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		// on met à jour le solde du document (si nécessaire)
		$this->maj_solde_document();

		// on met à jour le solde du document (si nécessaire)
		if(!empty($this->modele->type_element) && !empty($this->modele->id_document) && in_array($this->modele->type_element,Variables::$documents_gescom)) {
			$document_management = management($this->modele->type_element, $this->modele->id_document);
			$document_management->gerer_pdf_post_modification();
		}

		// on met à jour la somme du bordereau lié au paiement
		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// on met à jour la transaction si nécessaire
		if(!empty($this->modele->transaction_id)) {

			management('budget_insight_transaction', $this->modele->transaction_id)->maj_statut_rapprochement();
		}


		if(!empty($modele_avant) && !empty($modele_avant->id)) {

            $this->maj_bordereau();
        }

	}

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * On met à jour le solde du document (si nécessaire)
	 *
	 */
	protected function methodes_post_suppression($modele) {

		// on met à jour le solde du document
		$this->maj_solde_document();

        //on met à jour la somme du bordereau lié au paiement
        $this->maj_bordereau();

		// Si le paiement était lié à un document
		if(!empty($this->modele->type_element) && !empty($this->modele->id_document)) {

			// Si le solde du document n'est plus de zéro, on annule son réglement
			$management = management($this->modele->type_element, $this->modele->id_document) ;
			if ($management->modele->solde_document_ttc != 0) {
				$management->annule_reglement();
			}
		}

		parent::methodes_post_suppression($modele);
	}

	/**
	 *
	 * Met à jour le solde du document (si nécessaire)
	 *
	 * @return true
	 *
	 */
	protected function maj_solde_document() {

		if(!empty($this->modele->type_element) && !empty($this->modele->id_document)) {

			$management = management($this->modele->type_element, $this->modele->id_document);

            if(empty($this->modele->titre))
                $this->enregistre_modele([
                    'titre' => traduction('document.paiement_document.titre').' '. $management->modele->reference_document,
                ]);

            if(in_array($this->modele->type_element,Variables::$documents_gescom))
			    $management->maj_total_document(true);
            else
                $management->maj_remboursement();
		}

		return true;
	}

    /**
     *
     * Met à jour le bordereau lié au paiement (si nécessaire)
     *
     * @return true
     *
     */
    protected function maj_bordereau() {

        $modele = $this->modele;

        if($modele->bordereau_id !=null) {

            $management = management('bordereau', $modele->bordereau_id);

            $management->calcul_somme_montant_bordereau();
        }


        return true;
    }

	/**
	 *
	 * Affiche le lien vers le document s'il y en a un dans la liste
	 *
	 */
	public function liste_reference_document($modele) {

		if(!empty($modele->type_element) && !empty($modele->id_document)) {

			return management($modele->type_element, $modele->id_document)->affiche_lien();
		}

		return '';
	}

	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function liste_tags($modele) {

		$tags = array();


		if($modele->rapproche == 1) {

			$tags[] = '<span class="badge badge-success">Rapproché</span>';
		}
		else {

			$tags[] = '<span class="badge badge-default">Non rapproché</span>';
		}


		if($modele->comptabilise == 1) {

			$tags[] = '<span class="badge badge-warning" style="background: #249e8e">Comptabilisée</span>';
		}

		return implode('<br/>', $tags);
	}

	/**
	 *
	 * Envoie le mail de récap des débits de CB
     *
	 */
	public function envoie_mail_recap_debits($parametres) {

		return false;
	}

	/**
	*
	* @cf description sur Element_management
	*
	*/
	protected function retraite_modifications($modifications) {

		// on ajoute le champ entite_id
		if(isset($modifications['client_id'])) {

			$client = management('client', $modifications['client_id']);

			$modifications['entite_id'] = $client->modele->entite_id;
		}

		// on ajoute le champ entite_id
		if(isset($modifications['fournisseur_id'])) {

			$fournisseur = management('fournisseur', $modifications['fournisseur_id']);

			$modifications['entite_id'] = $fournisseur->modele->entite_id;
		}

		// on retraite via le management Element de base
		$modifications = parent::retraite_modifications($modifications);

		return $modifications;
	}

	/**
	 *
	 * Comptabilise un paiement
	 *
	 */
	public function comptabilise() {

		if($this->modele->mode_paiement_id == fonctionnalite('compta_generation_paiement_via_avoirs') && !empty($this->modele->mode_paiement_id)){

			$this->enregistre_modele(array('comptabilise' => 1));

			return true;

		}

		if($this->modele->comptabilise == 1) {

			return traduction('messages.php.paiement.deja_comptabilise');
		}

		// on va chercher le compte pour le tiers
		$compte_tiers = $this->compta_compte_tiers();

		if($compte_tiers === false) {

			return traduction('messages.php.paiement.comptabilise_sans_tiers');
		}

		if(!is_numeric($compte_tiers)) {

			return $compte_tiers;
		}

		// on va chercher le compte pour la banque
		$compte_banque = $this->compta_compte_banque();

		if($compte_banque === false) {

			return traduction('messages.php.paiement.comptabilise_sans_compte_bancaire');
		}

		if(is_array($compte_banque) && !empty($compte_banque['erreur']))
			return $compte_banque['erreur'];

		$ecriture_id = modele('ecriture_comptable')->orderBy('ecriture_id', 'DESC')->take(1)->first();

		if($ecriture_id === null)
			$ecriture_id = 1 + management('ecriture_comptable')->increment_initial_ecriture_comptable();
		else
			$ecriture_id = $ecriture_id->ecriture_id + 1;

		// on vérifie qu'on a bien un compte banque & un journal
		$journal = $this->code_journal();



		if(is_array($journal) && !empty($journal['erreur']))
			return $journal['erreur'];

		$libelle = $this->compta_genere_libelle();

		// on inverse l'écriture si on est sur un montant négatif
		$infos_ecriture = array(

			'ecriture_id' => $ecriture_id,
			'date' => $this->modele->date,
			'journal_id' => $journal,
			'compte_comptable_id' => $this->compta_compte_tiers(),
			'auxiliaire' => $this->compta_compte_auxiliaire(),
			// 'auxiliaire' => '',
			'debit' => 0,
			'credit' => $this->modele->montant,
			'type_element' => $this->_type_element,
			'element_id' => $this->modele->id,
			'entite_id' => $this->modele->entite_id,
			'reference' => '',
			'libelle' => $libelle,
		);

		if($this->modele->montant < 0) {

			$infos_ecriture['debit'] = $this->modele->montant * -1;
			$infos_ecriture['credit'] = 0;
		}

		$ecriture = management('ecriture_comptable');

		$retour_1 = $ecriture->enregistre($infos_ecriture);

		if($retour_1 !== true) {

			return $retour_1;
		}

		$infos_ecriture = array(

			'ecriture_id' => $ecriture_id,
			'date' => $this->modele->date,
			'journal_id' => $journal,
			'compte_comptable_id' => $compte_banque,
			// 'auxiliaire' => $this->compta_compte_auxiliaire(),
			'auxiliaire' => '',
			'debit' => $this->modele->montant,
			'credit' => 0,
			'type_element' => $this->_type_element,
			'element_id' => $this->modele->id,
			'entite_id' => $this->modele->entite_id,
			'reference' => '',
			'libelle' => $libelle,
		);

		// on inverse l'écriture si on est sur un montant négatif
		if($this->modele->montant < 0) {

			$infos_ecriture['debit'] = 0;
			$infos_ecriture['credit'] = $this->modele->montant * -1;
		}

		$ecriture = management('ecriture_comptable');

		$retour_2 = $ecriture->enregistre($infos_ecriture);



		if($retour_2 !== true) {

			return $retour_2;
		}

		// on enregistre le document comme comptabilisé
		$this->enregistre_modele(array('comptabilise' => 1));

		// on logue la comptabilisation du document
        // $this->log_comptabilisation();

		return true;
	}

	/**
	 *
	 * Retourne le libellé a utiliser pour l'écriture comptable
	 *
	 */
	protected function compta_genere_libelle() {

		$libelle = $this->modele->titre;

		if(!empty($this->modele->type_element) && !empty($this->modele->id_document)) {

			$libelle_parametrable = fonctionnalite('compta_libelle_paiement');

			if(!empty($libelle_parametrable))
				$libelle = service('publipostage')->publipostage_texte($libelle_parametrable, 'paiement', [$this->modele->id]);
		}

		return $libelle;
	}

	/**
	 *
	 * Retourne le compte tiers à utiliser pour la comptabilisation de ce paiement
	 *
	 */
	protected function compta_compte_tiers() {

		if(!empty($this->modele->client_id)) {

			/**
			 * @todo gérer les tiers internationnaux
			 */

			$client = modele('client', $this->modele->client_id);

			if(!empty($client->forcer_compte_comptable))
				return $client->forcer_compte_comptable;

			return fonctionnalite('compta_compte_general_clients');
		}

		if(!empty($this->modele->fournisseur_id)) {

			/**
			 * @todo gérer les tiers internationnaux
			 */
			return fonctionnalite('compta_compte_general_fournisseurs');
		}

		return false;
	}

	/**
	 *
	 * Retourne le compte banque à utiliser pour la comptabilisation de ce paiement
	 *
	 */
	protected function compta_compte_banque() {

		if(empty($this->modele->compte_bancaire_id))
			return array('erreur' => traduction('messages.php.paiement.comptabilise_non_associe_compte_bancaire'));

		$compte_bancaire = modele('compte_bancaire', $this->modele->compte_bancaire_id);

		if(empty($compte_bancaire->compte_comptable))
			return array('erreur' => traduction('messages.php.paiement.compte_comptable_non_associe_compte_bancaire')." ".$compte_bancaire->nom);

		return $compte_bancaire->compte_comptable;
	}

	/**
	 *
	 * Retourne le journal de banque à utiliser pour la comptabilisation de ce paiement
	 *
	 */
	protected function code_journal() {

		if(empty($this->modele->compte_bancaire_id))
			return array('erreur' => traduction('messages.php.paiement.comptabilise_non_associe_compte_bancaire'));

		$compte_bancaire = modele('compte_bancaire', $this->modele->compte_bancaire_id);

		if(empty($compte_bancaire->journal))
			return array('erreur' => traduction('messages.php.paiement.aucun_journal_associe_compte_bancaire')." ".$compte_bancaire->nom);

		return $compte_bancaire->journal;
	}

	/**
	 *
	 * Retourne le compte auxiliaire à utiliser pour la comptabilisation de ce paiement
	 *
	 */
	protected function compta_compte_auxiliaire() {

		if(!empty($this->modele->client_id))
			return management('client', $this->modele->client_id)->compte_auxiliaire();

		if(!empty($this->modele->fournisseur_id))
			return management('fournisseur', $this->modele->fournisseur_id)->compte_auxiliaire();

		return '';
	}

	/**
	 *
	 * Trigger appelé lorsque on débite une CB manuellement (opération non lié à un document de gestion commerciale)
	 *
	 * Cette méthode est prévue pour être surchargée, pour gérer des logs, envoyer un mail, etc...
	 *
	 */
    public function methode_post_formulaire_encaissement_cb($formulaire) {

		return true;
    }

	/**
	 *
	 *
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 *
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);

		if(fonctionnalite('listes_factures_autres_action_comptabiliser') !== false)
			$actions['comptabiliser_documents'] = '<span class="dropdown-item" @click="modale_comptabiliser_documents = true"><i class="fa fa-fw fa-check"></i> <span v-html="$root.traduction(\'interface.listes.comptabiliser\')"></span></span>';

        $liste_libre = Liste_libre::where('id', $id_liste)->first();

        if($liste_libre != null && $liste_libre->id_rapport == 'fiche_bordereau_paiement') {

            $actions['rattache_paiement'] = '<a v-if="$root.bordereau.statut == 0 || $root.bordereau.statut == null" class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_rattacher_paiement_' . $id_liste . '"><i class="far fa-plus-square"></i> <span v-html="$root.traduction(\'interface.listes.rattacher_les_paiements\')"></span></a>';
            $actions['detache_paiement'] = '<a v-if="$root.bordereau.statut == 0 || $root.bordereau.statut == null" class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_detacher_paiement_' . $id_liste . '"><i class="far fa-minus-square"></i> <span v-html="$root.traduction(\'interface.listes.detacher_paiements\')"></span></a>';

        }

		return $actions;
	}

	/**
	 *
	 * On ne peut pas modifier un paiement s'il est rapproché
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
		}

		// on va calculer le montant
		if(isset($modifications['type']) && isset($modifications['montant_saisi'])) {

			$type = $modifications['type'];
			$montant_saisi = $modifications['montant_saisi'];
		}
		else {

			if(!isset($modifications['type']) && !$this->existe())
				return traduction('messages.php.champ_obligatoire')." ".champ_libre('paiement','type')->modele->nom;

			if(!isset($modifications['montant_saisi']) && !$this->existe())
				return traduction('messages.php.champ_obligatoire')." ".champ_libre('montant','type')->modele->nom;

			$type = $this->modele->type;
			$montant_saisi = $this->modele->montant_saisi;
		}

		if(isset($modifications['montant_saisi'])) {

			if(empty($type))
				// encaissement
				$modifications['montant'] = $montant_saisi;
			else
				// décaissement
				$modifications['montant'] = $montant_saisi * -1;
		}

		// Permet de gérer l'enregistrement en négatif de la somme indiqué
		/*
		@note Frédéric : plus nécessaire avec l'introduction de type de paiement / montant saisi
        if(isset($modifications['montant'])){

			if(isset($modifications['fournisseur_id']) && !empty($modifications['fournisseur_id']) || (!empty($modele) && !isset($modifications['fournisseur_id']) && !empty($modele->fournisseur_id))){

				if(isset($modifications['montant']))
					$modifications['montant'] = $modifications['montant'] *-1;
				else if(!empty($modele) && !empty($modele->montant))
					$modifications['montant'] = $modele->montant * -1;
			}

        }
		*/


		// on est en création
		if(empty($modele))
			return parent::enregistre($modifications, $modele);

		$verification_necessaires = true;

		if(count($modifications) == 1 && array_key_exists('neutralise', $modifications))
			$verification_necessaires = false;

		if(count($modifications) == 2 && array_key_exists('type_element', $modifications) && array_key_exists('id_document', $modifications))
			$verification_necessaires = false;

		// Si on est censé bloqué mais que certains champs sont modifiables en poste validation, on enregistre ces derniers
		if(( $modele->rapproche == 1 || $modele->comptabilise == 1 ) && $verification_necessaires) {

			$champs_post_validation = array();

			foreach ($modifications as $nom_sql => $modification) {

				$champ = Champ_libre::where('type_element', 'paiement')->where('nom_sql', $nom_sql)->first();

				if($champ != null && $champ->modification_post_validation == 1)
					$champs_post_validation[] = $nom_sql;
			}

			if (!empty($champs_post_validation)) {

				foreach ($modifications as $nom_sql => $modification) {

					if(!in_array($nom_sql, $champs_post_validation))
						unset($modifications[$nom_sql]);
				}

				return parent::enregistre($modifications, $modele);
			}

			// on ne peut pas supprimer une opération rapprochée
			if($modele->rapproche == 1)
				return traduction('messages.php.paiement.modification_operation_rapprochee');

			// on ne peut pas supprimer une opération comptabilisée
			if($modele->comptabilise == 1)
				return traduction('messages.php.paiement.modification_operation_comptabilisee');
		}

		return parent::enregistre($modifications, $modele);
	}

	/**
	 *
	 * On ne peut pas supprimer un paiement s'il est rapproché
	 *
	 */
	public function supprime($modele = false) {

		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
			else {

				dd("Erreur lors de la suppression : le modèle n'a pas été trouvé");
			}
		}

		// on ne peut pas supprimer une opération rapprochée
		if($modele->rapproche == 1) {

			return traduction('messages.php.paiement.suppression_operation_rapprochee');
		}

		// on ne peut pas supprimer une opération comptabilisée
		if($modele->comptabilise == 1) {

			return traduction('messages.php.paiement.suppression_operation_comptabilisee');
		}

		return parent::supprime($modele);
	}

    /**
     *
     * Renvoie la valeur négative du montant
     *
     */
    public function calcul_montant_negatif($modele) {

        if($modele->montant != null) {

            return $modele->montant * -1;
        }

        return $modele->montant;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'detacher';

        return $liste_options;
    }
}
