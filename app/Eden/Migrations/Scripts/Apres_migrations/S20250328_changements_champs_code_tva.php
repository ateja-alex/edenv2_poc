<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20250328_changements_champs_code_tva implements Script
{

    public function execute()
    {
        try {
            $champs_code_tva = Champ_libre::where('type', '20')->where('liste_choix', '115')->get();

            foreach ($champs_code_tva as $champ_logo) {
                $nouvelles_informations = [
                    'type' => 42,
                    'type_element_ajax' => 'code_tva',
                    'liste_choix' => null,
                    'format_champ' => 'select',
                ];

                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, [$champ_logo]);

            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors des modifications sur les champs champs_code_tva : {$e}");
        }

        return true;
    }
}