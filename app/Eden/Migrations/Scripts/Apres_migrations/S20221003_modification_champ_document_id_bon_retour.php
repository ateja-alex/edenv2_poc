<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;
use DB;

class S20221003_modification_champ_document_id_bon_retour implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'document_id')->where('type_element', 'bon_retour_vente_lignes')->get();

        $nouvelles_informations = array(
            'type_element_ajax' => 'bon_retour_vente',
        );
		
		$champ_libre_id = Champ_libre::where('nom_sql', 'document_id')->where('type_element', 'bon_retour_vente_lignes')->first();
		
		DB::select('ALTER TABLE `bon_retour_vente_lignes` DROP FOREIGN KEY `CE_champ_libre_'.$champ_libre_id->id_cl.'`;');
		
		DB::select('ALTER TABLE `bon_retour_vente_lignes` ADD CONSTRAINT `CE_champ_libre_'.$champ_libre_id->id_cl.'` FOREIGN KEY (`document_id`) REFERENCES `bon_retour_vente`(`id`);');


        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);
        
		return true;
    }
}

