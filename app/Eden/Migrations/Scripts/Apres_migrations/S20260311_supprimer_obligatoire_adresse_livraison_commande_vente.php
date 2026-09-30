<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20260311_supprimer_obligatoire_adresse_livraison_commande_vente implements Script {

    public function execute(){

        if(file_exists(app_path('Migrations/commande_vente.php'))){
            $adresse_livraison = Champ_libre::where('type_element', 'commande_vente')->where('nom_sql', 'adresse_de_livraison')->first();
            if($adresse_livraison->obligatoire == 1){
                $adresse_livraison->obligatoire = 0;
                $adresse_livraison->save();

                Table_libre_management::generer_fichier_migration('commande_vente',true);
            }
        }

        return true;
    }
}