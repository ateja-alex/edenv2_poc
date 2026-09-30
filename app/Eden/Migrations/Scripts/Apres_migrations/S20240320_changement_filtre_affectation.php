<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;

class S20240320_changement_filtre_affectation implements Script {

    public function execute() {

        $champs = Champ_libre::where(function ($query) {
            $query->where('type_element', 'tache')
                ->where('nom_sql', 'affectation');
        })->orWhere(function ($query) {
            $query->where('type_element', 'employe_demande_conge')
                ->where('nom_sql', 'employe_id');
        })->get();

        $type_elements = [];

        foreach ($champs as $champ) {
            if($champ->type == 42 && empty($champ->badge_filtre))
                $type_elements[] = $champ->type_element;
        }

        $listes_par_type = Liste_libre::whereIn('type_element', $type_elements)->get()->groupBy('type_element');
        $rapports_par_type = Rapport_libre::whereIn('type_element', $type_elements)->get()->groupBy('type_element');

        foreach ($listes_par_type as $type_element => $listes) {

            $champ = $type_element === 'tache' ? 'affectation' : 'employe_id';

            foreach ($listes as $liste) {
                if(empty($liste->filtres_appliques))
                    continue;

                $filtre = [];

                if(@unserialize($liste->filtres_appliques) !== false)
                    $filtre = unserialize($liste->filtres_appliques);

                elseif(!empty($liste->filtres_appliques))
                    $filtre = json_decode($liste->filtres_appliques, true);

                if(!empty($filtre) && (empty($filtre[$champ]) || !is_array($filtre[$champ]))){

                    foreach ($filtre as $index => $element_filtre) {
                        if(isset($element_filtre[0]) && $element_filtre[0] == $champ && is_array($element_filtre[1])) {
                            $filtre[$index] = [$champ, array_values($element_filtre[1])[0]];
                        }
                    }

                    $liste->save();

                    Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);

                    continue;
                }

                if(empty($filtre[$champ]) && empty($filtre[$champ]['texte']))
                    continue;

                $filtre[$champ] = [
                    'texte' => array_values($filtre[$champ])[0],
                    'variable' => ''
                ];

                $liste->filtres_appliques = serialize($filtre);

                $liste->save();

                Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);

            }
        }

        foreach ($rapports_par_type as $type_element => $rapports) {

            $champ = $type_element === 'tache' ? 'affectation' : 'employe_id';

            foreach ($rapports as $rapport) {
                if (empty($rapport->parametrage_rapport_libre))
                    continue;

                $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre, true);

                if (!empty($parametrage_rapport_libre['filtre_applique_' . $champ]) && is_array($parametrage_rapport_libre['filtre_applique_' . $champ])) {
                    $parametrage_rapport_libre['filtre_applique_' . $champ] = array_key_first($parametrage_rapport_libre['filtre_applique_' . $champ]);
                }

                $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);

                $rapport->save();
            }
        }

        return true;
    }
}
