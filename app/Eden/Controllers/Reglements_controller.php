<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Reglements_controller extends Controller {

	/**
	 *
	 * Enregistre les règlements saisis
	 *
	 */
    public function reglements_a_recevoir_post(Request $request) {

		if(empty($request->documents_id))
			return redirect()->route('base_eden.liste.rapport', ['reglements_a_recevoir'])->with('erreurs', ['Aucun document']);

		$factures = modele('facture_vente')->whereIn('id', $request->documents_id)->get();

		$management = management('facture_vente');
        $erreurs    = [];

		if($factures !== null) {

			foreach($factures as $facture) {

				if(!empty($request->get('montant_paiement_'.$facture->id))) {

					$infos_paiement = [
                        'compte_bancaire_id' => $request->compte_bancaire_id,
                        'mode_paiement_id'   => $request->mode_paiement_id,
                        'montant'            => $request->get('montant_paiement_' . $facture->id),
                        'date'               => date('Y-m-d'),
					];

					$retour = $management->ajouter_paiement('facture_vente', $facture->id, $infos_paiement);

					if($retour !== true)
					    $erreurs[] = traduction('messages.php.paiement.erreur_enregistrement_facture',null,[$facture->reference_document,$retour]);
				}
			}
		}

        if(count($erreurs))
            return redirect()->back()->with('erreurs', $erreurs);

		return redirect()->route('base_eden.liste.rapport', ['reglements_a_recevoir'])->with('message', traduction('messages.php.maj_informations'));
    }
}
