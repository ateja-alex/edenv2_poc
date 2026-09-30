<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20250903_trigger_doublon_note_de_frais implements Script{

    public function execute(){

        $trigger = modele('trigger_eden')->where('nom', 'Gestion doublon note de frais')->first();

        $requete_avant = "UPDATE note_de_frais nf1
                LEFT JOIN note_de_frais nf2
                    ON nf1.utilisateur_id = nf2.utilisateur_id
                    AND nf1.date = nf2.date
                    AND nf1.montant_ttc = nf2.montant_ttc
                    AND nf1.id != nf2.id
                    AND COALESCE(nf2.inactif,0) = 0
                    AND COALESCE(nf2.accepte, 0) IN (0,1)
                SET nf1.doublon_potentiel = IF(COALESCE(nf1.accepte, 0) IN (0,1) AND nf2.id IS NOT NULL,1,0)
                WHERE COALESCE(nf1.inactif,0) = 0;
            ";

        if($trigger !== null){

            if($trigger->requete != $requete_avant)
                return true;

            $management = management('trigger_eden',$trigger->id,$trigger);
        }
        else
            $management = management('trigger_eden');

        $table_note_de_frais = Table_libre::where('type_element','note_de_frais')->value('id');

        $retour = $management->enregistre(array(
            'nom' => 'Gestion doublon note de frais',
            'type_element_id' => $table_note_de_frais,
            'type_element_concerne_id' => $table_note_de_frais,
            'requete' => "UPDATE note_de_frais nf1
                LEFT JOIN note_de_frais nf2
                    ON nf1.utilisateur_id = nf2.utilisateur_id
                    AND nf1.date = nf2.date
                    AND nf1.montant_ttc = nf2.montant_ttc
                    AND nf1.id != nf2.id
                    AND COALESCE(nf2.inactif,0) = 0
                    AND COALESCE(nf2.accepte, 0) IN (0,1)
                SET nf1.doublon_potentiel = IF(COALESCE(nf1.accepte, 0) IN (0,1) AND nf2.id IS NOT NULL,1,0)
                WHERE COALESCE(nf1.inactif,0) = 0;
            ",
            'requete_elements_concernes' => "SELECT * FROM note_de_frais WHERE COALESCE(note_de_frais.inactif,0) = 0",
        ));

        if($retour !== true)
            throw new \Exception($retour, 500);

        return true;
    }
}