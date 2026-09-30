<?php

namespace App\Eden\Managements\Services;

class Pdf_gescom_service {

	/**
	 *
	 * Retourne une adresse formatée
	 *
	 */
	public function adresse_facturation_formatee($document_management) {
		
		// on a une adresse saisie en dur
		if(!empty($document_management->modele->adresse_de_facturation_texte)) {
			
			$adresse = explode("\n", $document_management->modele->adresse_de_facturation_texte);
			
			$adresse[0] = '<b>'.$adresse[0].'</b>';
			
			return implode('<br/>', $adresse);
		}
		
		// on doit créer l'adresse
		if(empty($document_management->modele->adresse_de_facturation))
			return '';
			
		$adresse_tmp = modele('adresse', $document_management->modele->adresse_de_facturation);
		
		$adresse = array();
		
		if(!empty($adresse_tmp->societe)) {
			
			$adresse[] = '<b>'.$adresse_tmp->societe.'</b>';
		}
		elseif($document_management->est_une_vente()) {
			
			$adresse[] = '<b>'.modele('client', $document_management->modele->client_id)->nom.'</b>';
		}
		elseif($document_management->est_un_achat()) {
			
			$adresse[] = '<b>'.modele('fournisseur', $document_management->modele->fournisseur_id)->nom.'</b>';
		}
		
		if(!empty($adresse_tmp->prenom) || !empty($adresse_tmp->nom))
			$adresse[] = $adresse_tmp->prenom.' '.$adresse_tmp->nom;
		
		$adresse[] = $adresse_tmp->adresse;
		
		if(!empty($adresse_tmp->adresse_complement))
			$adresse[] = $adresse_tmp->adresse_complement;
		
		$adresse[] = $adresse_tmp->code_postal.' '.$adresse_tmp->ville;

		return implode('<br/>', $adresse);
	}
	
	/**
	 *
	 * Retourne une adresse formatée
	 *
	 */
	public function adresse_livraison_formatee($document_management) {
		
		// on a une adresse saisie en dur
		if(!empty($document_management->modele->adresse_de_livraison_texte)) {
			
			$adresse = explode("\n", $document_management->modele->adresse_de_livraison_texte);
			
			$adresse[0] = '<b>'.$adresse[0].'</b>';
			
			return implode('<br/>', $adresse);
		}
		
		// on doit créer l'adresse
		if(empty($document_management->modele->adresse_de_livraison))
			return '';
			
		$adresse_tmp = modele('adresse', $document_management->modele->adresse_de_livraison);
		
		$adresse = array();
		
		if(!empty($adresse_tmp->societe)) {
			
			$adresse[] = '<b>'.$adresse_tmp->societe.'</b>';
		}
		elseif($document_management->est_une_vente()) {
			
			$adresse[] = '<b>'.modele('client', $document_management->modele->client_id)->nom.'</b>';
		}
		elseif($document_management->est_un_achat()) {
			
			$adresse[] = '<b>'.modele('fournisseur', $document_management->modele->fournisseur_id)->nom.'</b>';
		}
		
		if(!empty($adresse_tmp->prenom) || !empty($adresse_tmp->nom))
			$adresse[] = $adresse_tmp->prenom.' '.$adresse_tmp->nom;
		
		$adresse[] = $adresse_tmp->adresse;
		
		if(!empty($adresse_tmp->adresse_complement))
			$adresse[] = $adresse_tmp->adresse_complement;
		
		$adresse[] = $adresse_tmp->code_postal.' '.$adresse_tmp->ville;

		return implode('<br/>', $adresse);
	}

	/**
	 * 
	 * Retourne les CGV s'il faut les joindre au document
	 * 
	 */
	public function ajoute_cgv_au_document($document_management) {
		
		if($document_management->est_une_vente()) {

			if(empty(parametre_entite($document_management->modele->entite_id, 'cgv_sur_'.$document_management->_type_element)))
				return false;
		
			$cgv = modele('entite', $document_management->modele->entite_id)->cgv;
			
			if(empty($cgv))
				return false;
			
			return $cgv;
		}
		else{

			if(empty(parametre_entite($document_management->modele->entite_id, 'cga_sur_'.str_replace('achat','vente',$document_management->_type_element))))
				return false;
			
			$cga = modele('entite', $document_management->modele->entite_id)->cga;
			
			if(empty($cga))
				return false;
			
			return $cga;
		}
		
	}
}	
