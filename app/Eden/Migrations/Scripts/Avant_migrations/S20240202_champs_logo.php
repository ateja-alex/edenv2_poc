<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20240202_champs_logo implements Script
{

    public function execute()
    {
        try {
            $champs_logo = Champ_libre::where('type', 7)->get();

            foreach ($champs_logo as $champ_logo) {
                $nouvelles_informations = [
                    'type_fichier' => $champ_logo->format_champ,
                    'format_champ' => '',
                ];

                if($champ_logo->nom_sql == 'logo')
                    $nouvelles_informations['format_champ'] = 'logo';

                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, [$champ_logo]);

            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors des modifications sur les champs logo : {$e}");
        }

        return true;
    }
}