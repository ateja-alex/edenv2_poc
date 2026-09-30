<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;

class Zone_transporteur_controller extends Fiche_controller {


	public function ajouterDepartement (Request $request, $type_element, $id_element) {
		$is_exists = modele('zone_transporteur_cp')
						->where(
								[
									'pays_id' => $request->pays_id,
									'departement' => $request->departement,
									'zone_id' => $id_element
								])->exists();

        if(!$is_exists) {

            management('zone_transporteur_cp')->enregistre([
				'pays_id' => $request->pays_id,
				'departement' => $request->departement,
				'zone_id' => $id_element
            ]);
            
            $retour = modele('zone_transporteur_cp')
						->where(
								[
									'pays_id' => $request->pays_id,
									'departement' => $request->departement,
									'zone_id' => $id_element
								])
						->join('pays', 'pays.id', 'pays_id')
						->select('zone_transporteur_cp.*', 'pays.nom')->first() ;
            return response()->json(
            					[
            						'success' => true,
            						'localisation' => $retour
            					]);
        }

        return response()->json(['success' => false]);
	}

	public function enleverDepartement (Request $request, $type_element, $id_element) {
		
        $retour = modele('zone_transporteur_cp')
                    ->where(
                        [
                            'id' => $request->id_localisation,
                            'zone_id' => $id_element
                        ])
                    ->delete();

        if($retour) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
	}

}