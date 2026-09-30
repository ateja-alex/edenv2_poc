<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20230418_contacts_sur_documents implements Script
{

    public function execute()
    {

        $champ_libre_condition = Champ_libre::where('nom_sql', 'contacts_ids')->where('type_element', 'like', '%_vente')->get();

        $nouvelles_informations = array(
            'selection_conditionnelle_champ' => 'client_id',
            'selection_conditionnelle_valeur' => 'client_id',
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champ_libre_condition);

        return $retour;

    }
}
