<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;

class Transporteur_controller extends Fiche_controller {


	public function ajouterTarif (Request $request, $type_element, $id_element) {

		$id_tarif = false;
		$is_exists = modele('transporteur_tarif_livraison')
						->where(
								[
									'zone_id' => $request->zone_id,
									'poids_min' => $request->poids_min,
									'poids_max' => $request->poids_max,
									'transporteur_id' => $id_element
								])->first();

        if(!empty($is_exists->id)) {
        	$id_tarif = $is_exists->id;
        }

        $retour = management('transporteur_tarif_livraison', $id_tarif)->enregistre([
			'zone_id' => $request->zone_id,
			'poids_min' => $request->poids_min,
			'tarif' => $request->tarif,
			'poids_max' => $request->poids_max,
			'transporteur_id' => $id_element
        ]);
        
        $tarif = modele('transporteur_tarif_livraison')
					->where(
							[
								'zone_id' => $request->zone_id,
								'poids_min' => $request->poids_min,
								'poids_max' => $request->poids_max,
								'transporteur_id' => $id_element
							])
					->where('transporteur_id', $id_element)
					->join('zone_transporteur', 'zone_transporteur.id', 'transporteur_tarif_livraison.zone_id')
					->select('transporteur_tarif_livraison.*', 'zone_transporteur.nom')
					->first() ;

		// Si id_tarif est false, c'est une création, on transmet le nouvel élément pour ajout a la liste
        return response()->json(
        					[
        						'success' => $retour,
        						'tarif' => ($id_tarif === false ? $tarif : false)
        					]);
	}

	public function enleverTarif (Request $request, $type_element, $id_element) {
        $retour = modele('transporteur_tarif_livraison')
                    ->where(
                        [
                            'id' => $request->id_tarif,
                            'transporteur_id' => $id_element
                        ])
                    ->delete();

        if($retour) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
	}

}