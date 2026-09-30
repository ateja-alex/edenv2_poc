<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class S20250306_filtrage_adresses_document implements Script
{
    public function execute() {

        $nouvelles_informations = array();

        $types_documents = Variables::$documents_gescom;

        $filtrage_par_champ = [
            'adresse_de_livraison' => "[{\"champ\":\"type_adresse\",\"condition_ou\":false,\"condition\":\"WhereIn\",\"symbole\":\"\",\"valeur\":\"2,3\"}]",
            'adresse_de_livraison_client' => "[{\"champ\":\"type_adresse\",\"condition_ou\":false,\"condition\":\"WhereIn\",\"symbole\":\"\",\"valeur\":\"2,3\"}]",
            'adresse_de_facturation' => "[{\"champ\":\"type_adresse\",\"condition_ou\":false,\"condition\":\"WhereIn\",\"symbole\":\"\",\"valeur\":\"1,3\"}]",
        ];

        $champs_adresse = Champ_libre::whereIn('type_element', $types_documents)->whereIn('nom_sql', array_keys($filtrage_par_champ))->get()->groupBy('nom_sql');

        foreach ($champs_adresse as $nom_sql => $champs) {

            $nouvelles_informations['filtrage'] = $filtrage_par_champ[$nom_sql];
            Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs);
        }

        return true;
    }
}
