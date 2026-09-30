<?php

use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Managements\Listes_management;

/*
*
* Helper pour aller chercher un management pour un rapport
*
* @param $id_rapport string
*
*/
function rapport($id_rapport, $genere_erreur = true) {
	

	
	$tests = array(
		
		"\\App\\Managements\\Rapports\\".ucfirst($id_rapport)."_management",
		"\\App\\Eden\\Managements\\Rapports\\Rapports\\".ucfirst($id_rapport)."_management",
		"\\App\\Eden\\Managements\\Rapports\\Rapports\\Exemples\\".ucfirst($id_rapport)."_management",
	);

	$classe = classe_existante($tests);

	if(!empty($classe)) {

		$manager = new $classe($id_rapport);

		return $manager;
	}

	
	// est ce que c'est un rapport paramétrable ?
	if(strpos($id_rapport, 'rapport_parametrable_') !== false) {
		
		$id = str_replace('rapport_parametrable_', '', $id_rapport);
		
		$rapport = Rapport_libre::find($id);
		
		if($rapport->type == 'indicateur') {
			
			$manager = new App\Eden\Managements\Rapports\Rapport_indicateur_management($id_rapport);
			
			return $manager;
		}
		
	}
	
	// est ce que c'est un rapport paramétrable ? autre version
	$rapport_tmp = Rapport_libre::where('id_rapport', $id_rapport)->first();
	
	if($rapport_tmp !== null && !empty($rapport_tmp->parametrage_rapport_libre)) {
		
		if($rapport_tmp->type_rapport == 'indicateur') {
			
			$manager = new App\Eden\Managements\Rapports\Rapport_indicateur_management($id_rapport);
			
			return $manager;
		}
		
	}

	$rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();
	
	
	if(!empty($rapport)) {
		
		$liste_management = liste_rapport($id_rapport);
		
		return $liste_management->rapport_liste_libre($id_rapport);
	}


	if($genere_erreur === true)
		throw new \App\Eden\Exceptions\Eden_exception("Le rapport ".ucfirst($id_rapport)."_management n'a pas été trouvé dans les répertoires \\App\\Managements\\Rapports ou \\App\\Eden\\Managements\\Rapports\\Rapports");

	// on ne retourne pas d'erreur si le rapport n'a pas été trouvé
	return false;
}