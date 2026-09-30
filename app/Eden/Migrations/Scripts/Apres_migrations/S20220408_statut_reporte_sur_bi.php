<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220408_statut_reporte_sur_bi implements Script {
	
	public function execute() {
		
		$elements = modele('budget_insight_transaction')->where('reporte_eden', 1)->get();
		
		foreach($elements as $element) {
			
			$element->statut_eden = 3;
			$element->save();
		}
		
		return true;
	}
}