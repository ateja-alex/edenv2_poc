<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Models\Champ_libre;


/**
 * Gestion des fiches fournisseurs
 */
class Fiche_modele_de_document_management extends Fiche_management {
	
	/**
	 * 
	 * Prépare les données pour la fiche
	 * 
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
		
		$champs_jsons = ['header', 'body', 'articles', 'footer', 'recap_footer', 'annexes'];
		foreach ($champs_jsons as $champ) {
			
			$json = json_decode($donnees['modele_de_document']->$champ);

			if(!$json)
				$json = [];

			$donnees['modele_de_document']->{$champ.'_json'} = $json;
		}


		$donnees['variables_disponibles'] = [];

		$management = management('modele_de_document', $this->id_element);
		$types_de_document = $management->types_de_document();

		$champs_document = [];
		// Si on n'a qu'un type de document, on utilise tous les champs du document
		if(count($types_de_document) == 1) {
			$champs_document = Champ_libre::where('type_element', $types_de_document[0])->orderBy('nom')->get()->pluck('nom_sql', 'nom')->toArray();

		} elseif(count($types_de_document) > 1) {

			// On prends les champs du type de document 0
			$champs_document = Champ_libre::where('type_element', $types_de_document[0])->orderBy('nom')->get()->pluck('nom', 'nom_sql')->toArray();

			for($i = 1; $i < count($types_de_document); $i++) {

				// On prends tous les champs du type de document $i
				$champs_document_i = Champ_libre::where('type_element', $types_de_document[$i])->orderBy('nom')->get()->pluck('nom_sql', 'nom_sql');

				// Si pas présent sur type de document $i, on le supprime de type de document
				foreach($champs_document as $nom_sql => $osef) {
					if(!isset($champs_document_i[$nom_sql])) {
						unset($champs_document[$nom_sql]);
					}
				}
			}
		}

		$donnees['variables_disponibles']['Document'] = $champs_document;


		/*
		entite
		client
		projet
		adresse_de_facturation
		adresse_de_livraison
		nom_document
		type_element
		client_management
		tableau_tva
		paiements
		document_management
		fournisseur
		contacts
		*/

		return $donnees;
	}
}
