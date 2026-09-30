<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Formulaire;

class S20241129_mise_en_place_type_formulaire implements Script
{
    public function execute(){

        $formulaires_a_corriger = Formulaire::where(function($condition) {
                $condition->where('nom_formulaire', 'like', 'creation_volee_%')
                ->orWhere('nom_formulaire', 'like', 'extranet_%')
                ->orWhere('nom_formulaire', 'like', 'intranet_%')
                ->orWhere('nom_formulaire', 'like', 'fiche_%')
                ->orWhere('nom_formulaire', 'like', 'chronometre_%');
            })
            ->where(function($condition) {
                $condition->whereNull('type_formulaire')
                    ->orWhere('type_formulaire','');
            })
            ->get();

        foreach($formulaires_a_corriger as $formulaire){

            $formulaire->type_formulaire = strpos($formulaire->nom_formulaire,'creation_volee_') !== false ? 'creation_volee' :
                (strpos($formulaire->nom_formulaire,'extranet_') !== false ? 'extranet' :
                    (strpos($formulaire->nom_formulaire,'intranet_') !== false ? 'intranet' :
                        (strpos($formulaire->nom_formulaire,'fiche_') !== false ? 'fiche' :
                            (strpos($formulaire->nom_formulaire,'chronometre_') !== false ? 'chronometre' : null))));

            $formulaire->save();

            if(file_exists(app_path().'/Migrations/Formulaires_libres/'.$formulaire->nom_formulaire.'.php'))
                Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);
        }

        return true;
    }
}