<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220601_retrocompatibilite_rapports implements Script {

	public function execute() {


		$rapports = \App\Eden\Models\Rapport_libre::whereNotNull('parametrage_rapport_libre')->get();

		foreach($rapports as $rapport) {

			$json = json_decode($rapport->parametrage_rapport_libre);

			$nouveau_json = json_decode($rapport->parametrage_rapport_libre);

			if($rapport->type_rapport == 'indicateur') {

				$nouveau_json->series = array(array('Nom' => 'Nouvelle série'));
				$nouveau_json->serie = array('Nom' => 'Nouvelle série');

				$champs_libres = \App\Eden\Models\Champ_libre::where('type_element', $rapport->type_element)->get();

				foreach($champs_libres as $champ_libre) {

					if(empty($json->{'filtre_applique_'.$champ_libre->nom_sql}))
						continue;

					if(in_array($champ_libre->type, array(1,20))) {

						$filtre = $json->{'filtre_applique_'.$champ_libre->nom_sql};

						$nouveau_filtre = array();

						foreach($filtre as $valeur) {

							$nouveau_filtre[$valeur] = 'true';
						}

						$nouveau_json->{'filtre_applique_'.$champ_libre->nom_sql} = $nouveau_filtre;
					}

				}
			}

			$rapport->parametrage_rapport_libre = json_encode($nouveau_json);
			$rapport->save();
		}

		return true;
	}
}
