<?php

namespace App\Eden\Managements\Services;


class Alimentation_timeline_service {

    /**
	 *
	 * Alimente la timeline avec un évènement
	 *
	 */
	public function alimentation_timeline($evenement, $type_element, $id_element, $texte, $autres_informations = array(), $type_echange = 12) {
        
		if(empty($id_element))
			return true;
		
		if(fonctionnalite($evenement) !== null) {
			
			if(!fonctionnalite($evenement))
				return true;
		}
		else if(strpos($evenement, '_vente') || strpos($evenement, '_achat')) {

            if (!strpos($evenement, '_vente'))
                $fonctionnalite = fonctionnalite('alimentation_timeline_document_achat');
            else
                $fonctionnalite = fonctionnalite('alimentation_timeline_document_vente');

            if (isset(fonctionnalite('alimentation_timeline_document_vente')[$evenement]))
                $evenement_tmp = $evenement;
            else
                $evenement_tmp = str_replace('creation_', '', $evenement);

            if(!isset($fonctionnalite[$evenement_tmp]) || !$fonctionnalite[$evenement_tmp])
                return true;
		}



		$echange = management('echange');

		$donnees = array(

			'type_element' => $type_element,
			'element_id' => $id_element,
			'type' => $type_echange,
			'date' => date('Y-m-d H:i:s'),
			'description' => $texte,
		);

		foreach($autres_informations as $cle => $valeur) {

			$donnees[$cle] = $valeur;
		}

		$echange->enregistre($donnees);
	}
}
