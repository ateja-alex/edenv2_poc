<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Liste_libre;


class Page_adv_service {

    /**
     *
     * Récupère les informations nécessaires pour l'affichage de la liste
     *
     */
    public function informations_pour_liste($type_element, $id_rapport = false) {

		if($id_rapport === false) {

			$liste_libre = Liste_libre::where('type_element', $type_element)->where(function ($requete) {
				$requete->where('id_rapport', '')->orWhereNull('id_rapport');
			})->first();
		}
		else {

			$liste_libre = Liste_libre::where('type_element', $type_element)->where('id_rapport', $id_rapport)->first();
		}

		return array(
			'id_liste' => $liste_libre->id,
			'options_liste' => [],
			'type_element' => $type_element,
		);
    }
}
