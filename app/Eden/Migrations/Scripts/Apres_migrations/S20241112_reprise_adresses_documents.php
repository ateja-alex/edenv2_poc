<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20241112_reprise_adresses_documents implements Script
{

    public function execute()
    {

        $types_document_achat = Variables::$documents_achat_gescom;
        $types_document_vente = Variables::$documents_vente_gescom;

        $noms_champs = ['adresse_de_livraison', 'adresse_de_facturation'];
        $nouvelles_informations = [
            'type' => 42,
            'type_element_ajax' => 'adresse',
            'liste_choix' => null
        ];
        $nouvelles_informations_adresse_client = array_merge($nouvelles_informations, [
            'selection_conditionnelle_champ' => "client_id",
            'selection_conditionnelle_valeur' => "client_id",
        ]);

        foreach ($types_document_vente as $type_document){

            $champs_adresse = Champ_libre::where('type_element', $type_document)->whereIn('nom_sql', $noms_champs)->where('type', '!=', 42)->get();

            if($champs_adresse->isNotEmpty())
                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_adresse);
        }

        $nouvelles_informations['type_element_ajax'] = 'adresse_interne';

        foreach ($types_document_achat as $type_document){

            if(Schema::hasColumns($type_document, ['adresse_de_livraison','adresse_de_livraison_client','a_livrer_chez_client']))
                DB::update('UPDATE ' . $type_document . ' SET adresse_de_livraison = null, adresse_de_livraison_client = adresse_de_livraison where a_livrer_chez_client = 1');

            $champs_adresse = Champ_libre::where('type_element', $type_document)->whereIn('nom_sql', $noms_champs)->where('type', '!=', 42)->get();

            if($champs_adresse->isNotEmpty())
                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_adresse);

            $champ_adresse_client = Champ_libre::where('type_element', $type_document)->where('nom_sql', 'adresse_de_livraison_client')->where('type', '!=', 42)->get();

            if($champ_adresse_client->isNotEmpty())
                Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_adresse_client,$champ_adresse_client);
        }

        return true;
    }
}