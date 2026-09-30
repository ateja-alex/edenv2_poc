<?php

namespace App\Eden\Managements\Elements;

class Bl_achat_lignes_management extends Document_lignes_management {

	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

		// met à jour les infos sur la commande achat & les stocks réservés
		$this->mise_a_jour_statut_ligne_commande_achat();

		// met à jour les stocks réels (entrée en stock)
		$this->mise_a_jour_stocks(false);
	}


	/**
	 *
	 * On crée les lignes à réceptionner pour les fournisseurs
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// met à jour les infos sur la commande achat & les stocks réservés
		$this->mise_a_jour_statut_ligne_commande_achat();

		// met à jour les stocks réels (entrée en stock)
		$this->mise_a_jour_stocks();
	}

	/**
	 *
	 * On met à jour les status de la ligne
	 *
	 */
	public function mise_a_jour_statut_ligne_commande_achat() {

		if($this->modele->type_element_source != 'commande_achat')
			return;

        $management_document_origine = $this->management_entete_origine();

        $quantite_recue = $management_document_origine->calcule_nombre_transformations_ligne($this->modele->id_ligne_source);

		$management_ligne_commande_achat = $this->management_ligne_origine();

		$ligne_element_source = $management_ligne_commande_achat->modele;

		$conditionnement_ligne_achat = $management_ligne_commande_achat->conditionnement_de_la_ligne();

		$quantite_origine = $conditionnement_ligne_achat * $ligne_element_source->quantite;

		\Log::info("Bl_achat_lignes_management, quantite reçue pour la ligne ".$this->modele->id_ligne_source." de achat_vente_lignes =>");


		if($quantite_recue == 0) {

			// non traité
			$transforme = 0;
			$recue = 0;
			$transforme_reliquat = $quantite_origine;
		}
		elseif($quantite_recue < $quantite_origine) {

			// partiellement traité
			$transforme = 1;
			$recue = 0;
			$transforme_reliquat = $quantite_origine - $quantite_recue;
		}
		else {

			// traité
			$transforme = 2;
			$recue = 1;
			$transforme_reliquat = 0;
		}

        $receptionne_par = null;
        $date_de_reception = null;

        if(in_array($transforme, array(1,2))){

            $date_de_reception = date("Y-m-d H:i:s");

            $receptionne_par = $management_ligne_commande_achat->champ('receptionne_par')->recupere_valeurs_du_modele($this->modele->id_ligne_source);

            if(!in_array(moi()->id, $receptionne_par))
                $receptionne_par[] = moi()->id;

        }

		$modifications_ligne = array(

			'transforme' => $transforme,
			'transforme_reliquat' => $transforme_reliquat,
			'recue' => $recue,
			'quantite_recue' => $quantite_recue,
			'reliquat_reception' => $transforme_reliquat,
            'date_de_reception' => $date_de_reception,
            'receptionne_par' => $receptionne_par,
		);

		$management_ligne_commande_achat->enregistre_modele($modifications_ligne);

        $management_ligne_commande_achat->management_principal = $this->management_principal ?? $this->management_entete();

		// on met à jour les stocks
		$management_ligne_commande_achat->mise_a_jour_stocks();

		// on met à jour la commande vente avec les réceptions
		$management_ligne_commande_achat->met_a_jour_commande_vente_avec_receptions();

		return true;
	}

}
