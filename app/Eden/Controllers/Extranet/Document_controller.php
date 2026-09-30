<?php

namespace App\Eden\Controllers\Extranet;

use App\Http\Controllers\Controller;

class Document_controller extends Controller {

	/**
	 *
	 * Permet de valider un devis vente depuis l'extranet avec ou non acceptation des CGV
	 *
	 */
	public function devis_vente_valider(Request $request){

		$phrase_commentaire_fiche = "Le devis à été accepté par ".$request->prenom." ".$request->nom;

		$nouveau_commentaire = management('message');

		$informations_commentaire = array(
			'commentaire' => $phrase_commentaire_fiche,
			'element_id' => $request->id_element,
			'type_element' => "devis_vente",
		);

		$nouveau_commentaire->enregistre($informations_commentaire);

		$devis_management = management('devis_vente',$request->id_element);

		$devis_management->accepte();

		return response()->json(['retour' => true]);
	}
}