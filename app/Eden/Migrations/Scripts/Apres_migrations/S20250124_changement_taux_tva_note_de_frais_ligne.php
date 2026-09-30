<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20250124_changement_taux_tva_note_de_frais_ligne implements Script
{

    public function execute()
    {
        try {
            $champs_logo = Champ_libre::where('nom_sql', 'taux_tva')->where('type_element', 'note_de_frais_lignes')->get();

            foreach ($champs_logo as $champ_logo) {
                $nouvelles_informations = [
                    'type' => 42,
                    'type_element_ajax' => 'code_tva',
                    'filtrage' => "[{\"champ\":\"sens\",\"condition_ou\":false,\"condition\":\"Where\",\"symbole\":\"=\",\"valeur\":\"1\"}]",
                ];

                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, [$champ_logo]);

            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors des modifications sur les champs taux_tva : {$e}");
        }

        return true;
    }
}