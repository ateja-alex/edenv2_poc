<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20241018_reprise_filtres_rapport implements Script {

    public function execute() {

        $rapports_libre = Rapport_libre::where('parametrage_rapport_libre', '!=', null)->get();

        foreach ($rapports_libre as $rapport) {
            $rapport_modifie = false;
            try {
                $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);
            } catch(\Exception | \Throwable $e) {
                return  new \Exception("Une erreur c'est produite lors du formatage des filtres du rapport : " . $rapport->id_rapport . " " . $e->getMessage());
            }

            if(!empty($parametrage_rapport_libre) && !empty($parametrage_rapport_libre->filtres_rapport)) {
                foreach ($parametrage_rapport_libre->filtres_rapport as $id_filtre => $filtre) {
                    if(strpos($filtre, '.') !== false)
                        continue;

                    $parametrage_rapport_libre->filtres_rapport[$id_filtre] = $rapport->type_element . '.' . $filtre;
                    $rapport_modifie = true;
                }
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

