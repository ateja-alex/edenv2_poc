<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Avoir_vente_ligne;

class Avoir_vente_management extends Avoir_management {

	use Facturation_electronique_document_trait;
	
	
	/**
	 * 
	 * On vérifie la cloture comptable
	 * 
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		$modification_possible = $this->verifie_cloture_comptable($modifications);
		
		if($modification_possible === true) {
			
			return parent::enregistre($modifications, $modele);
		}
		
		return traduction('messages.php.document.modification_passe',null,[fonctionnalite('cloture_comptable_mensuelle_le')]);
	}
	
	/**
     *
     * On vérifie la cloture comptable
     *
     */
    public function supprime($modele = false) {

        if($modele === false && !empty($this->modele))
            $modele = $this->modele;

        if($modele->valide == 1)
            return traduction('messages.php.document.supprimer_document_valide');
		
		$modification_possible = $this->verifie_cloture_comptable();
		
		if($modification_possible === false)
			return traduction('messages.php.document.modification_passe',null,[fonctionnalite('cloture_comptable_mensuelle_le')]);
		
		// on regarde si on doit mettre des documents à jour
		if(!empty($this->modele->facture_id_source))
			$facture_id_source = $this->modele->facture_id_source;
		if(!empty($this->modele->acompte_id_source))
			$acompte_id_source = $this->modele->acompte_id_source;
		
		$retour = parent::supprime($modele);
		
		if(!empty($facture_id_source)) {
			
			management('facture_vente', $facture_id_source)->maj_total_document(true);
		}
		if(!empty($acompte_id_source)) {
			
			management('acompte_vente', $acompte_id_source)->maj_total_document(true);
		}
		
		return $retour;
	}
	
	/**
	 *
	 * Mise à jour du total du document
	 *
	 */
	public function maj_total_document($enregistrement_classique = false) {
		
		$modifications = parent::maj_total_document($enregistrement_classique);
		
		if(!$this->existe())
			return $modifications;
		
		if(!empty($this->modele->facture_id_source)) {
			
			$facture_management = management('facture_vente', $this->modele->facture_id_source);
			
			// on appelle cette méthode sur la facture pour mettre à jour les totaux
			// de la facture et des avoirs 
			$facture_management->maj_total_document(true);
		}
		elseif(!empty($this->modele->acompte_id_source)) {
			
			$acompte_management = management('acompte_vente', $this->modele->acompte_id_source);
			
			// on appelle cette méthode sur l'acompte pour mettre à jour les totaux
			// de l'acompte et des avoirs 
			$acompte_management->maj_total_document(true);
		}
		
		return $modifications;
	}
	
	
	
	/**
	 * 
	 * On va chercher la situation géographique du client
	 * 
	public function compta_situation_geographique() {
		
		return management('client', $this->modele->client_id)->situation_geographique_comptable();
	}
	 */
	
	/**
	 * 
	 * Retourne le bon code à utiliser pour les tiers
	 * 
	 */
	public function compta_compte_tiers($article) {
		
		// on regarde si le client a un compte comptable spécifique
		if(!empty($this->modele->client_id_tiers_payeur))
			$client = modele('client', $this->modele->client_id_tiers_payeur);
		else
			$client = modele('client', $this->modele->client_id);
		
		if(!empty($client->forcer_compte_comptable))
			return $client->forcer_compte_comptable;
		
		return fonctionnalite('compta_compte_general_clients');
	}
	
	/**
	 * 
	 * On va chercher la situation géographique du client
	 * 
	 */
	public function compta_compte_auxiliaire() {
		
		if(!empty($this->modele->client_id_tiers_payeur))
			return management('client', $this->modele->client_id_tiers_payeur)->compte_auxiliaire();
		else
			return management('client', $this->modele->client_id)->compte_auxiliaire();
	}

	/**
	* 
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	* 
	*/
	public function modele_lignes() {
		
		return new Avoir_vente_ligne;
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        return $liste_options;
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
				
				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.regle').'</span>';
			}
			else {
				
				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.non_regle').'</span>';
			}
			
			if($modele->comptabilise == 1) {

				$tags[] = '<span class="badge badge-warning" style="background: #249e8e">'.traduction('interface.listes.tags_pour_liste.comptabilise').'</span>';
			}
		}

		$tag_facturation_electronique = $this->tag_facturation_electronique($modele);

		if(!empty($tag_facturation_electronique))
			$tags[] = $tag_facturation_electronique;

		return implode(' ', $tags);
	}

	/**
	 *
	 * Trigger post validation d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_validation_document($modele){

		$this->genere_paiement_automatiques_avoirs();

        $retour = parent::methodes_post_validation_document($modele);

        if($retour !== true)
            return $retour;

		$comptabiliser_automatiquement = fonctionnalite('comptabiliser_automatiquement');
			
		if(!empty($comptabiliser_automatiquement['avoir_vente']))
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
		
		//On récupère tous les documents de type facture_vente
		$documents_lies = $this->documents_lies('facture_vente');

		//On récupère le total de l'avoir
		$montant_avoir = $this->modele->montant_document_ttc;

		//On enregistre dans un premier temps un paiement qui règle l'avoir
		$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir * -1, $this->modele->client_id, $this->modele->id, $this->_type_element);

		foreach($documents_lies as $facture){

			//On vérifie que la facture n'est pas déjà réglée
			if(!empty($facture['management']->modele->regle))
				continue;
			
			// le montant restant sur l'avoir est de 0
			if(empty($montant_avoir))
				continue;

			//Si lors du retrait du montant de la facture sur le montant_avoir celui ci est positif ou égal à zéro on peut régler cette facture
			if($montant_avoir >= $facture['management']->modele->solde_document_ttc){

				$this->enregistre_paiement_via_avoir($this->modele->date, $facture['management']->modele->solde_document_ttc, $this->modele->client_id, $facture['management']->modele->id, $facture['type_element']);

				$montant_avoir -= $facture['management']->modele->solde_document_ttc;
				
				continue;
			}

			
			// le montant de l'avoir restant est < au solde de la facture
			$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir, $this->modele->client_id, $facture['management']->modele->id, $facture['type_element']);
			
			// on vient de solder l'avoir
			$montant_avoir = 0;
		}

		// Si le montant_avoir restant est positif on enregistre un paiement sur le client mais qui n'est lié à aucun document
		if($montant_avoir > 0){

			$this->enregistre_paiement_via_avoir($this->modele->date, $montant_avoir, $this->modele->client_id, null, null);
		}

		return true;

	}

	/**
	 * 
	 * Enregistre un paiement
	 * 
	 */
	public function enregistre_paiement_via_avoir($date, $montant, $client_id, $id_document, $type_element){

		$infos = [
			'date' => $date,
			// 'montant' => $montant,
			'mode_paiement_id' => fonctionnalite('compta_generation_paiement_via_avoirs'),
			'compte_bancaire_id' => fonctionnalite('compta_banque_defaut_generation_paiement_via_avoir'),
			'client_id' => $client_id,
			'id_document' => $id_document,
			'type_element' => $type_element,
            'titre' => "Paiement automatique suite à avoir"
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

	}

	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	public function retourne_sujet_du_mail_pour_relecture() {

		return moi()->prenom ." vient d'éditer un avoir";
	}
	

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat';

        if(fonctionnalite('regroupement_articles_documents'))
            $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat_prefiltre';

        return parent::transformations_possibles($transformations_possibles);

    }

}