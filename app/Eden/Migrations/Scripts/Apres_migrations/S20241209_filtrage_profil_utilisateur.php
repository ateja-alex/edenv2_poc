<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20241209_filtrage_profil_utilisateur implements Script
{
    public function execute()
    {
        $nouvelles_informations = [
            'filtrage' => "[{\"champ\":\"extranet\",\"condition_ou\":false,\"condition\":\"Where\",\"symbole\":\"=\",\"valeur\":\"0\"},
            {\"champ\":\"extranet\",\"condition_ou\":true,\"condition\":\"WhereNull\",\"symbole\":\"\",\"valeur\":\"\"}]",
        ];

        $champ = Champ_libre::where('type_element', 'utilisateur')->where('nom_sql', 'profil_id')->get();

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champ);

        return true;
    }
}
