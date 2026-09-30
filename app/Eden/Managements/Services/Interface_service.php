<?php

namespace App\Eden\Managements\Services;

class Interface_service {

	/**
	 *
	 * Liste des blocs à afficher dans les documents liés
	 *
	 */
	public function blocs_documents_lies($vente = true) {
		
		if($vente === true)
			$types_elements = \App\Eden\Variables::$bloc_documents_lies_vente;
		else
			$types_elements = \App\Eden\Variables::$bloc_documents_lies_achat;
		
		$a_retourner = array();
		
		foreach($types_elements as $type_element) {
			
			if(fonctionnalite('gescom_'.$type_element) !== true)
				continue;
				
			$a_retourner[] = $type_element;
		}

		$blocs_documents_lies = [];

		if(count($a_retourner) < 7){
			foreach($a_retourner as $type_element){
				$blocs_documents_lies[][] = $type_element;
			}
		}
		else{

			if($vente === true)
				$blocs_documents_lies = \App\Eden\Variables::$repartition_bloc_documents_lies_vente;
			else
				$blocs_documents_lies = \App\Eden\Variables::$repartition_bloc_documents_lies_achat;

			foreach($blocs_documents_lies as $key_colonne => &$colonne){

				foreach($colonne as $key_type_element => $type_element){
					if(!in_array($type_element, $a_retourner))
						unset($colonne[$key_type_element]);
				}

				$blocs_documents_lies[$key_colonne] = array_values($colonne);

				if(empty($blocs_documents_lies[$key_colonne]))
					unset($blocs_documents_lies[$key_colonne]);
			}
		}

		return $blocs_documents_lies;
	}
}
