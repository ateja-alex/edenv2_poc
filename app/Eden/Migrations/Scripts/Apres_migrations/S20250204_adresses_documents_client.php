<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class S20250204_adresses_documents_client implements Script{

    public function execute(){

        $nouvelles_informations = [
            'selection_conditionnelle_champ' => 'client_id',
            'selection_conditionnelle_valeur' => 'client_id'
        ];

        $champs = Champ_libre::whereIn('type_element', Variables::$documents_vente_gescom)
            ->whereIn('nom_sql', ['adresse_de_livraison', 'adresse_de_facturation'])
            ->get();

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs);

        return true;
    }

}