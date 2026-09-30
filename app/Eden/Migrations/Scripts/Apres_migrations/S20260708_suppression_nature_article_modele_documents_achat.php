<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use Illuminate\Support\Facades\Schema;

class S20260708_suppression_nature_article_modele_documents_achat implements Script {

    public function execute(){

        $types_documents = Variables::$documents_achat_gescom;

        foreach($types_documents as $type_document){
            
            $formulaire_champs = Formulaires_champs::where('type_element', $type_document)->where('nom_sql', 'nature_article_modele')->get();
            
            foreach($formulaire_champs as $champ){

                $nom_formulaire = $champ->nom_formulaire;
                $champ->delete();

                if(file_exists(app_path('Migrations/Formulaires_libres/'.$nom_formulaire.'.php')))
                    Maintenance_management::generer_fichier_migration_formulaire($nom_formulaire);
            }

            $champ_libre = Champ_libre::where('type_element', $type_document)->where('nom_sql', 'nature_article_modele')->first();
            
            if(!empty($champ_libre)){
                
                if(!empty($champ_libre->index_traduction))
                    service('traduction')->supprime_index_traduction($champ_libre->index_traduction);
                
                if(Schema::hasTable($type_document) && Schema::hasColumn($type_document, 'nature_article_modele'))
                    Schema::table($type_document, function ($table) use ($champ_libre) {
                        $table->dropForeign('CE_champ_libre_' . $champ_libre->id_cl);
                        $table->dropColumn('nature_article_modele');
                    });
                    
                $champ_libre->delete();

                if(file_exists(app_path('Migrations/' . $type_document . '.php')))
                    Table_libre_management::generer_fichier_migration($type_document);
            }

        }

        return true;
    }
}