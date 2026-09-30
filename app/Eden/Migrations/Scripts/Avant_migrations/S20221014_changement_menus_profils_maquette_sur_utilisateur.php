<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20221014_changement_menus_profils_maquette_sur_utilisateur implements Script
{

    public function execute()
    {

        $profil_id = Champ_libre::where('nom_sql', 'profil_id')->where('type_element', 'utilisateur')->get();

        $nouvelles_informations_profil = array(
            'type' => 20,
            'liste_choix' => 122,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_profil, $profil_id);

        $menus_id = Champ_libre::where('nom_sql', 'menus_id')->where('type_element', 'utilisateur')->get();

        $nouvelles_informations_menus = array(
            'type' => 20,
            'liste_choix' => 509,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_menus, $menus_id);

        $maquette_id = Champ_libre::where('nom_sql', 'maquette_id')->where('type_element', 'utilisateur')->get();

        $nouvelles_informations_maquette = array(
            'type' => 42,
            'type_element_ajax' => 'maquette',
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_maquette, $maquette_id);

        return $retour;

    }
}
