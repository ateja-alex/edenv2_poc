<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Models\Facture_vente_ligne;

use Cookie;
use Mail;

class Interface_fournisseur_controller extends Controller{

	/**
	 *
 	 * Affiche la page pour transmettre une facture
	 *
 	 */
	public function accueil($id_fournisseur, $clef_fournisseur_hachee){

		$fournisseur = management('fournisseur', $id_fournisseur);
		
		$factures_enregistreees = modele('facture_achat')->where('fournisseur_id', $id_fournisseur)->orderBy('date', 'desc')->get();
		
		$infos_pour_la_vue = array(
			
			'fournisseur' => $fournisseur,
			'factures_enregistreees' => $factures_enregistreees,
		);

		return view('eden::interface_fournisseur.accueil', $infos_pour_la_vue);
	}


	/**
	 *
 	 * Affiche le formulaire d'erreur de login
	 *
 	 */
	public function erreur_acces(){

		return view('eden::interface_fournisseur.erreur_connexion');
	}

	/**
	 * 
	 * Enregistre une facture transmise par un fournisseur
	 * 
	 */
	public function enregistre_facture_achat($id_fournisseur, $clef_fournisseur_hachee) {

		$fournisseur = management('fournisseur', $id_fournisseur);

		// On vérifie qu'un fichier a bien été transmis
		if ( !request()->hasFile('pdf_fournisseur') || !request()->file('pdf_fournisseur')->isValid() ) {
			return json_encode(['retour' => traduction('messages.php.fichier.joindre_fichier')]) ;
		}

		// On crée la facture d'achat
		$facture_achat = management('facture_achat');

		// On génère un nom de fichier
		$nom_fichier = time().'-'.request()->pdf_fournisseur->getClientOriginalName() ;
		
		request()->file('pdf_fournisseur')->storeAs('public', $nom_fichier);

		// On crée le tableau des modifications
		$modifications = array () ;
		$modifications['date'] = request()->date;
		$modifications['reference_fournisseur'] = request()->reference_fournisseur;
		$modifications['fournisseur_id'] = $fournisseur->modele->id;
		$modifications['pdf_fournisseur'] = $nom_fichier;

		// On crée l'article @todo
		$article_achat = modele('article', 1271);
		$modifications['articles'] = [
										[
											'article_id' => $article_achat->id,
											'designation' => $article_achat->designation,
											'tva' =>  20,
											'tarif' => request()->montant_document_ht,
											'quantite' => 1,
										]
									];

		// On enregistre le tout
		$retour = $facture_achat->enregistre($modifications);

		// Et on renvoi erreur ou succès
		return json_encode(['retour' => $retour]);

	}

}