<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;

class S20240725_update_rapport_tableau_bord implements Script
{

    public function execute() {
        $rapports_tableau_de_bord = modele('tableau_de_bord_contenu')->where('type', 1)->zero_ou_null('type_rapport')->get();

        $rapports = Rapport_libre::whereIn('id_rapport', $rapports_tableau_de_bord->pluck('element'))->get()->keyBy('id_rapport');

        $rapports_liste = $rapports->where('type_rapport', 'liste_libre')->pluck('id_rapport');

        $listes_libres = Liste_libre::whereIn('id_rapport', $rapports_liste)->get()->keyBy('id_rapport');

        foreach ($rapports_tableau_de_bord as $rapport) {
            if(empty($rapports[$rapport->element]))
                continue;

            $rapport->type_rapport = $rapports[$rapport->element]->type_rapport;

            if($rapport->type_rapport == 'liste_libre' && !empty($listes_libres[$rapport->element]))
                $rapport->id_liste = $listes_libres[$rapport->element]->id;

            $rapport->save();
        }

        return true;
    }
}