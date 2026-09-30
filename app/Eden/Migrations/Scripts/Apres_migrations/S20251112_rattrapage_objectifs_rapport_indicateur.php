<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20251112_rattrapage_objectifs_rapport_indicateur implements Script {

    public function execute(){

        $rapports_indicateur = Rapport_libre::where('type_rapport', 'indicateur')->get();

        foreach($rapports_indicateur as $rapport) {

            $modification = false;

            if(empty($rapport->parametrage_rapport_libre))
                continue;

            $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);

            if(isset($parametrage_rapport_libre->sens)){
                
                $modification = true;

                if(iconv('UTF-8', 'ASCII//TRANSLIT', $parametrage_rapport_libre->sens) === 'decroissant')
                    $parametrage_rapport_libre->reussi_si_inferieure = 1;
                else if($parametrage_rapport_libre->sens === 'croissant')
                    $parametrage_rapport_libre->reussi_si_inferieure = 0;
                
                unset($parametrage_rapport_libre->sens);
            }

            if(isset($parametrage_rapport_libre->objectif_global)){
                
                $modification = true;

                $parametrage_rapport_libre->type_calcul_objectif = 'fixe';
                $parametrage_rapport_libre->valeur_objectif = $parametrage_rapport_libre->objectif_global;
                
                unset($parametrage_rapport_libre->objectif_global);
            }

            if(isset($parametrage_rapport_libre->type_valeur)){

                $modification = true;

                $parametrage_rapport_libre->format_affichage = $parametrage_rapport_libre->type_valeur;
                unset($parametrage_rapport_libre->type_valeur);
            }

            if(!$modification)
                continue;

            $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);

            $rapport->save();
        }

        return true;
    }    
}