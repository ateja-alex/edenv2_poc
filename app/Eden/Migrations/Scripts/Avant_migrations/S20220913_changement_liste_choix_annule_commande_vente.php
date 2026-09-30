<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20220913_changement_liste_choix_annule_commande_vente implements Script
{

    public function execute()
    {

        $champ_libre_condition = Champ_libre::where('nom_sql', 'annule')->where('type_element', 'commande_vente')->get();

        $nouvelles_informations = array(
            'liste_choix' => 600,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champ_libre_condition);

        return $retour;

    }
}
