<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20221109_liste_107_doublon implements Script
{

    public function execute()
    {

        $champ_libre_condition = Champ_libre::where('nom_sql', 'theme')->where('type_element', 'tableau_de_bord_contenu')->get();

        $retour = false;

        foreach ($champ_libre_condition as $champ) {
            if ($champ->liste_choix == 117)
                $retour = true;
        }

        if($retour === true)
            return $retour;

        $nouvelles_informations = array(
            'liste_choix' => 117,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champ_libre_condition);

        return $retour;

    }
}
