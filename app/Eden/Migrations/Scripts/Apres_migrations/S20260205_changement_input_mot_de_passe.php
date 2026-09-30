<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;

class S20260205_changement_input_mot_de_passe implements Script {

    public function execute() {

        $champ_mdp = Champ_libre::where('type_element', 'utilisateur')->where('nom_sql', 'mot_de_passe')->first();
        $champ_mdp->format_champ = 'mdp_systeme';
        $champ_mdp->save();
        if(file_exists(app_path('Migrations/utilisateur.php'))){
            Table_libre_management::generer_fichier_migration('utilisateur',true);
        }

        if(file_exists(app_path('Migrations/Formulaires_libres/utilisateur.php'))){
            
            $champ_mdp_formulaire = Formulaires_champs::where('nom_formulaire', 'utilisateur')->where('nom_vue', 'mot_de_passe')->first();
            
            $champ_mdp_formulaire->nom_sql = 'mot_de_passe';
            $champ_mdp_formulaire->type_vue = null;
            $champ_mdp_formulaire->nom_vue = null;
            $champ_mdp_formulaire->type_champ = null;
            $champ_mdp_formulaire->save();
            
            Maintenance_management::generer_fichier_migration_formulaire('utilisateur');
        }

        return true;
    }
}