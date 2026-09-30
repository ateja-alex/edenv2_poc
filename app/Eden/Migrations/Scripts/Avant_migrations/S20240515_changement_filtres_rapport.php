<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20240515_changement_filtres_rapport implements Script {

    public function execute() {

        $rapports_libre = Rapport_libre::where('parametrage_rapport_libre', '!=', null)->get();
        
        foreach ($rapports_libre as $rapport) {
            $rapport_modifie = false;
            try {
                $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);
            } catch(\Exception | \Throwable $e) {
                return  new \Exception("Une erreur c'est produite lors du formatage des filtres du rapport : " . $rapport->id_rapport . " " . $e->getMessage());
            }

            if(!empty($parametrage_rapport_libre) && !isset($parametrage_rapport_libre->filtres_rapport))
                $parametrage_rapport_libre->filtres_rapport = [];

            if(!empty($parametrage_rapport_libre->filtre_dates_mensuelles)) {
                $parametrage_rapport_libre->filtres_rapport[] = $rapport->type_element . '.' . $parametrage_rapport_libre->filtre_dates_mensuelles;
                unset($parametrage_rapport_libre->filtre_dates_mensuelles);
                $rapport_modifie = true;
            }
            if(!empty($parametrage_rapport_libre->filtre_utilisateurs)) {
                $parametrage_rapport_libre->filtres_rapport[] = $rapport->type_element . '.' . $parametrage_rapport_libre->filtre_utilisateurs;
                unset($parametrage_rapport_libre->filtre_utilisateurs);
                $rapport_modifie = true;
            }
            if(!empty($parametrage_rapport_libre->filtre_entites)) {
                $parametrage_rapport_libre->filtres_rapport[] = $rapport->type_element . '.' . $parametrage_rapport_libre->filtre_entites;
                unset($parametrage_rapport_libre->filtre_entites);
                $rapport_modifie = true;
            }

            if($rapport_modifie) {
                try {
                    $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);
                } catch(\Exception | \Throwable $e) {
                    return  new \Exception("Une erreur c'est produite lors du formatage des filtres du rapport : " . $rapport->id_rapport . " " . $e->getMessage());
                }
                $rapport->save();
            }

        }

        \DB::select('TRUNCATE TABLE eden_rapports_parametres');

        return true;
    }
}

