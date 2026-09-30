<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20220602_changement_type_cacher_acces_support_utilisateur implements Script
{

    public function execute()
    {

        $champ_libre_maj_cacher_acces_support['utilisateur'] = array(array('nom_sql' => 'cacher_acces_support'));

        Champ_libre_management::maj_champs_libres($champ_libre_maj_cacher_acces_support, 'INT(11)');

        $champs_libre_condition = Champ_libre::where('type_element', 'utilisateur')->where('nom_sql','cacher_acces_support')->get();

        $retour = false;

        $nouvelles_informations = array(
            'liste_choix' => 14,
        );

        foreach ($champs_libre_condition as $champ_libre){

            $champ_libre->type = 20;
        }

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libre_condition);

        return $retour;
    }
}
