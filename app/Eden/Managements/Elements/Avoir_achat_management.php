<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Avoir_achat_ligne;

class Avoir_achat_management extends Avoir_management {
	
	/**
	* 
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	* 
	*/
	public function modele_lignes() {
		
		return new Avoir_achat_ligne;
	}
	
	/**
	 * 
	 * Retourne le bon code à utiliser pour les tiers
	 * 
	 */
	public function compta_compte_tiers($article) {
		
		return fonctionnalite('compta_compte_general_fournisseurs');
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        $liste_options[] = 'choisir_rapprochement';

        return $liste_options;
    }

	public function enregistre($modifications = array(), $modele = false) {

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

			if($ancienne_facturation_electronique_achat->modele->avoir_achat_id == $this->modele->id)
				$ancienne_facturation_electronique_achat->enregistre(['avoir_achat_id' => null]);
		}

		if(!empty($apres)) {

			$facturation_electronique_achat = management('facturation_electronique_achat', $apres);

			if($facturation_electronique_achat->modele->avoir_achat_id != $this->modele->id)
				$facturation_electronique_achat->enregistre(['avoir_achat_id' => $this->modele->id]);
		}
	}

	/**
	 *
	 * Trigger post validation d'un document
	 *
	 * @return boolean
	 *
	 */
	protected function methodes_post_validation_document($modele){

		$this->genere_paiement_automatiques_avoirs();

		$retour = parent::methodes_post_validation_document($modele);

        if($retour !== true)
            return $retour;

        $comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');

        if(!empty($comptabiliser_automatiquement['avoir_achat']))
            return $this->comptabilise();

        return true;

	}

	/**
	 * 
	 * Génération de paiement
	 * 
	 */
	public function genere_paiement_automatiques_avoirs() {

		//Si la fonctionnalité n'est pas activée on ne fait rien
		if(fonctionnalite('compta_generation_paiement_via_avoirs') == false){
			return ;
		}
		
		//On récupère tous les documents de type facture_achat
		$documents_lies = $this->documents_lies('facture_achat');

		//On récupère le total de l'avoir
		$montant_avoir = $this->modele->montant_document_ttc;
		

		//On enregistre dans un premier temps un paiement qui règle l'avoir
		$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir, $this->modele->fournisseur_id, $this->modele->id, $this->_type_element);

		foreach($documents_lies as $facture){

			//On vérifie que la facture n'est pas déjà réglée
			if(!empty($facture['management']->modele->regle))
				continue;
			
			// le montant restant sur l'avoir est de 0
			if(empty($montant_avoir))
				continue;
			
			//Si lors du retrait du montant de la facture sur le montant_avoir celui ci est positif ou égal à zéro on peut régler cette facture
			if($montant_avoir >= $facture['management']->modele->solde_document_ttc){
				$retour = $this->enregistre_paiement_via_avoir($this->modele->date, $facture['management']->modele->solde_document_ttc * -1, $this->modele->fournisseur_id, $facture['management']->modele->id, $facture['type_element']);

				$montant_avoir -= $facture['management']->modele->solde_document_ttc;
				
				continue;
			}

			
			// le montant de l'avoir restant est < au solde de la facture
			$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir * -1, $this->modele->fournisseur_id, $facture['management']->modele->id, $facture['type_element']);
			
			// on vient de solder l'avoir
			$montant_avoir = 0;
		}

		// Si le montant_avoir restant est positif on enregistre un paiement sur le fournisseur mais qui n'est lié à aucun document
		if($montant_avoir > 0){

			$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir * -1, $this->modele->fournisseur_id, null, null);
		}

		return true;

	}

	/**
	 * 
	 * Enregistre un paiement
	 * 
	 */
	public function enregistre_paiement_via_avoir($date, $montant, $fournisseur_id, $id_document, $type_element){

		$infos = [
			'date' => $date,
			// 'montant' => $montant,
			'mode_paiement_id' => fonctionnalite('compta_generation_paiement_via_avoirs'),
			'compte_bancaire_id' => fonctionnalite('compta_banque_defaut_generation_paiement_via_avoir'),
			'fournisseur_id' => $fournisseur_id,
			'id_document' => $id_document,
			'type_element' => $type_element,
		];
		
		if($montant >= 0) {
			
			$infos['type'] = 0;
			$infos['montant_saisi'] = $montant;
		}
		else {
			
			$infos['type'] = 1;
			$infos['montant_saisi'] = $montant * -1;
		}

		$retour = management('paiement')->enregistre($infos);
		
		return array($retour, $infos);

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