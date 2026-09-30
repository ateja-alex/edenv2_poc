<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20220609_liste_formatee_compte_comptable implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type',20)->whereIn('liste_choix',array(29,501))->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'compte_comptable',
            'format_champ' => 'select',
            'liste_choix' => 0,
        );

        $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);

        return $retour;
    }
}
