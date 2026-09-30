<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;

class Email_facture_fournisseur_controller extends Fiche_controller {

	/**
	 * 
	 * Enregistre une facture achat
	 * 
	 */
	public function cree_facture_achat(Request $request, $type_element, $id_element) {
		
		$type_element_pour_email_facture_fournisseur = (isset(request()->type_element_pour_email_facture_fournisseur) ? request()->type_element_pour_email_facture_fournisseur : 'facture_achat');
		
		$management = fiche('email_facture_fournisseur', $id_element);
		
		$retour = $management->verifie_informations_pour_saisie_facture_achat($request->all());
		
		// une erreur
		if($retour !== true) {
			
			return redirect()->back()->with('erreur_facture_achat_'.$request->piece_jointe, $retour);
		}
		
		// on récupère les informations de la facture
		$facture_achat = array();
		
		$facture_achat['fournisseur_id'] = $request->fournisseur_id;
		$facture_achat['date'] = $request->date;
		$facture_achat['date_de_reglement'] = $request->date_de_reglement;
		$facture_achat['reference_fournisseur'] = $request->reference_fournisseur;
		
		$email_facture_fournisseur = management('email_facture_fournisseur', $id_element);
		
		$pieces_jointes = json_decode($email_facture_fournisseur->modele->pieces_jointes, true);
		
		foreach($pieces_jointes as $id => $piece_jointe) {
			
			// c'est pas la bonne PJ
			if($id != $request->piece_jointe)
				continue;
			
			// ok c'est la bonne PJ, on enregistre
			$facture_achat['pdf_fournisseur'] = 'email_recus/'.$piece_jointe['fichier'];
		}
		
		
		foreach($request->{'facture_achat_'.$request->piece_jointe} as $champ => $valeur) {
			
			$facture_achat[$champ] = $valeur;
		}
		
		$facture_achat = $management->complete_informations_pour_saisie_facture_achat($request->all(), $facture_achat);
		
		$management = management($type_element_pour_email_facture_fournisseur);
		
		// on gère les articles
		$articles = array();
		
		foreach($request->articles as $article) {
			
			if(empty($article['article_id']))
				continue;
			
			if(empty($article['tarif']))
				continue;
			
			$article_modele = modele('article', $article['article_id']);
			
			$article['quantite'] = 1;
			$article['designation'] = $article_modele->designation;
			
			$articles[] = $article;
			
		}
		
		$facture_achat['articles'] = $articles;
		
		$retour = $management->enregistre($facture_achat);
		
		// une erreur
		if($retour !== true) {
			
			return redirect()->back()->with('erreur_facture_achat_'.$request->piece_jointe, $retour);
		}
		
		// ok pas d'erreur, on enregistre le fait que la PJ a été saisie
		$email_facture_fournisseur = management('email_facture_fournisseur', $id_element);
		
		$pieces_jointes = json_decode($email_facture_fournisseur->modele->pieces_jointes, true);
		
		foreach($pieces_jointes as $id => $piece_jointe) {
			
			// c'est pas la bonne PJ
			if($id != $request->piece_jointe)
				continue;
			
			// ok c'est la bonne PJ, on enregistre
			$pieces_jointes[$id][$type_element_pour_email_facture_fournisseur.'_id'] = $management->modele->id;
		}
		
		$email_facture_fournisseur->enregistre(array('pieces_jointes' => json_encode($pieces_jointes)));
		
		// doit on passer le mail en traité
		$traite = true;
		
		foreach($pieces_jointes as $id => $piece_jointe) {
			
			if((!isset($pieces_jointes[$id]['facture_achat_id']) || empty($pieces_jointes[$id]['facture_achat_id'])) && (!isset($pieces_jointes[$id]['avoir_achat_id']) || empty($pieces_jointes[$id]['avoir_achat_id'])))
				$traite = false;
		}
		
		if($traite === true) {
			
			$email_facture_fournisseur->enregistre(array('traite' => 1));
		}
		
		// on valide la facture
		$retour = $management->valide();
			
		if($traite === true) {
			
			return redirect()->route('base_eden.liste.index', ['email_facture_fournisseur'])->with('message', traduction('messages.php.fiche_email_facture_fournisseur.facture_succes'));
		}
		
		return redirect()->back()->with('message', traduction('messages.php.fiche_email_facture_fournisseur.facture_succes'));
	}

	
}