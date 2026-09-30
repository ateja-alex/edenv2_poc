<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Eden\Models\Liste_libre;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;

class S20240624_script_rattrapage_filtres_appliques implements Script {

    public function execute(){

        $listes_libres = Liste_libre::where('filtres_appliques','Like','%utilisateur_connecte%')->get();

        foreach ($listes_libres as $liste_libre) {

            $type = 0;

            if(@unserialize($liste_libre->filtres_appliques) !== false) {
                $filtres_appliques = unserialize($liste_libre->filtres_appliques);
                $type = 1;
            }

		    elseif(!empty($liste_libre->filtres_appliques)) {
                $filtres_appliques = json_decode($liste_libre->filtres_appliques, true);
                $type = 2;
            }

            $changement = false;

            foreach($filtres_appliques as &$filtres_applique){
                if(is_array($filtres_applique) && in_array('utilisateur_connecte',$filtres_applique,true)){
                    foreach($filtres_applique as $filtre){
                        if($filtre == 'utilisateur_connecte') {
                            $filtres_applique = [
                                "texte" => "#utilisateur_connecte#",
                                "variable" => null
                            ];

                            $changement = true;
                        }
                    }
                }
            }

            if($changement){

                if($type == 1)
                    $liste_libre->filtres_appliques = serialize($filtres_appliques);
                elseif($type == 2)
                    $liste_libre->filtres_appliques = json_encode($filtres_appliques);

                $liste_libre->save();

                Cache_management::generation_liste_libre($liste_libre->id);
                Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id,true);
            }

        }

        return true;
    }
}