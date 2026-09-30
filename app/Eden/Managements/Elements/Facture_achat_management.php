<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Facture_achat_ligne;
use App\Eden\Variables;

class Facture_achat_management extends Facture_management {

	/**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return new Facture_achat_ligne;
	}

	/**
	 *
	 * On remplit des données par défaut pour la transformation
	 *
	 * Cette méthode est surchargée dans les héritiers de cette classe (Facture_vente_management, etc)
	 *
	 */
	public function donnees_avant_transformation($management_element_origine) {

		// la date de règlement
		$this->modele->date_de_reglement = date('Y-m-d');

		parent::donnees_avant_transformation($management_element_origine);
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        $liste_options[] = 'choisir_rapprochement';

        return $liste_options;
    }

	/**
	 *
	 * von vérifie qu'il n'y a pas de doublon de reference facture fournisseur avant d'enregistrer
	 *
	 */
    public function enregistre($modifications = [], $modele = false) {

        if(!empty($modifications['reference_fournisseur'])) {

            if ($this->modele === null) {

                if(modele('facture_achat')->where('reference_fournisseur', $modifications['reference_fournisseur'])->count() > 0)
                    return traduction('messages.php.facture_achat.facture_ref_fournisseur_existante');
            }
            else {

                if(modele('facture_achat')->where('id', '<>', $this->modele->id)->where('reference_fournisseur', '=', $modifications['reference_fournisseur'])->count() > 0)
                    return traduction('messages.php.facture_achat.facture_ref_fournisseur_existante');

            }
        }

        $facturation_electronique_achat_avant = $this->modele->facturation_electronique_achat_id ?? null;

        $retour = parent::enregistre($modifications, $modele);

        if($retour === true && array_key_exists('facturation_electronique_achat_id', $modifications))
            $this->synchronise_facturation_electronique_achat($facturation_electronique_achat_avant, $modifications['facturation_electronique_achat_id']);

        return $retour;
	}

	private function synchronise_facturation_electronique_achat($avant, $apres) {

		if($avant == $apres)
			return;

		if(!empty($avant)) {

			$ancienne_facturation_electronique_achat = management('facturation_electronique_achat', $avant);

			if($ancienne_facturation_electronique_achat->modele->facture_achat_id == $this->modele->id)
				$ancienne_facturation_electronique_achat->enregistre(['facture_achat_id' => null]);
		}

		if(!empty($apres)) {

			$facturation_electronique_achat = management('facturation_electronique_achat', $apres);

			if($facturation_electronique_achat->modele->facture_achat_id != $this->modele->id)
				$facturation_electronique_achat->enregistre(['facture_achat_id' => $this->modele->id]);
		}
	}

	/**
	 *
	 * Crée un mouvement de stock
	 *
	 */
	public function creer_mouvement_stock($parametre = "document") {

		return parent::creer_mouvement_stock($this->_type_element);
	}

	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->valide != 1) {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.pro_forma').'</span>';
		}
		else {

			if($modele->regle == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.reglee').'</span>';
			}
			else {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.non_reglee').'</span>';
			}
		}

		if($modele->annulee_par_avoir == 1 && $modele->annulee_v1 == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annulee').' V1</span>';

		}

		if($modele->annulee_par_avoir == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annulee_par_avoir').'</span>';
		}

		if($modele->avoir_partiel == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_partiel').'</span>';
		}

		if($modele->avoir_total == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_total').'</span>';
		}

		if($modele->comptabilise == 1) {

			$tags[] = '<span class="badge badge-warning" style="background: #249e8e">'.traduction('interface.listes.tags_pour_liste.comptabilisee').'</span>';
		}

		return implode('<br/>', $tags);
	}

	/**
	 *
	 * Affiche les informations pour les dernières factures saisies pour la saisie des factures fournisseurs via la bannette
	 *
	 */
	public function formate_derniere_facture_saisie() {

		return $this->affiche_lien();
	}

	/**
	 *
	 * Pour les factures achat, pas besoin de générer le PDF
	 *
	 */
    // Note Mathis 13/10/2021 : Commenté car très étrange de ne pas généré les pdf pour les facture achat, vue avec fred
//	public function creation_pdf() {
//
//		return true;
//	}

	/**
	 *
     *
     * On gère la suppression par avoir
     *
     */
    public function supprime($modele = false) {

		//on laisse la suppression standard
		if(fonctionnalite('gescom_suppression_facture_valide') == 'supprimer') {

			return parent::supprime($modele);
		}

		if($modele === false && !empty($this->modele))
			$modele = $this->modele;

		// la facture est déjà annulée par un avoir
		if($this->modele->annulee_par_avoir == 1)
			return traduction('messages.php.facture.annulation_impossible_avoir');

		// la facture n'est pas validée, on laisse le standard
		if($this->modele->valide != 1)
			return parent::supprime($modele);

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

		// Creation de l'avoir
		$modifications = array(

			'commentaires' => 'Facture à l\'origine de cet avoir: '. $modele->reference_document,
		);

		$retour = $this->transformer_document('avoir_achat', $modifications);

		// Modification commentaire de l'avoir
		if($retour[0] !== true)
			return $retour[0];

		$avoir = $retour[1];

		// Validation de l'avoir
		$avoir->valide();


		// Changement de statut de la facture
		$this->methodes_post_annulation_par_avoir($avoir);

		$this->enregistre_comme_regle();

		$avoir->enregistre_comme_regle();

		// on enregistre qu'elle a été supprimée
		$this->enregistre(array('annulee_par_avoir' => 1));

		// on logue la suppression par avoir
		$this->enregistrer_log(Variables::$types_logs['annulation_par_avoir']);

		return true;
    }

	/**
	 *
	 * On enregistre comme "transformées" les devis.
	 *
	 */
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification_document($modele, $modele_avant, $modifications);

		// On enregistre comme "transformées" les devis.
		$documents_lies = $this->documents_lies('devis_achat');

		foreach ($documents_lies as $devis) {

			$devis['management']->enregistre_modele(['transforme_en_facture' => 1]);
		}


	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['avoir_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'avoir_achat']);
        $transformations_possibles['bon_retour_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_retour_achat']);

        return parent::transformations_possibles($transformations_possibles);

    }

    /**
     *
     * methodes post validation
     *
     */
    protected function methodes_post_validation_document($modele) {
        
        $retour = parent::methodes_post_validation_document($modele);

        if($retour !== true)
            return $retour;

        $comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');

        if(!empty($comptabiliser_automatiquement['facture_achat']))
            return $this->comptabilise();

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

		$actions['imprimer_documents_scannes'] = '<span class="dropdown-item" @click="modale_imprimer_pdf_documents_scannes = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.imprimer_documents_scannes\')"></span></span>';

		return $actions;
	}

}
