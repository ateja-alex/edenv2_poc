<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Librairies\Budgea\Exception;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use App\Eden\Managements\Maintenance_management;

use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Champ_libre;

class S20221125_remplacement_vue_sous_formulaire implements Script{

    public function execute(){

        $formulaires = ['client', 'fournisseur', 'article'];

        foreach ($formulaires as $formulaire){

            $formulaire_bdd = Formulaire::where('nom_formulaire', $formulaire)->first();
            $les_champs = Formulaires_champs::where('nom_formulaire', $formulaire)->where('type_champ', 2)->get();

            if(empty($formulaire_bdd) || empty($les_champs))
                continue;

            foreach ($les_champs as $champ){

                $vue = $champ->nom_vue;

                $champ->type_champ = 3;
                $champ->nom_vue = null;
                $champ->type_vue = null;

                $sous_formulaire = modele_par_defaut('eden_sous_formulaire');
                $sous_formulaire->nom_sous_formulaire = $formulaire . '_sous_formulaire_' . $vue;
                $sous_formulaire->nom_formulaire_parent = $formulaire;
                $sous_formulaire->type_element_enfant = $vue;
                $sous_formulaire->formulaire_id = $formulaire_bdd->id;
                if(is_array($sous_formulaire->data_vue))
                    $sous_formulaire->data_vue = json_encode($sous_formulaire->data_vue);

                if(is_array($sous_formulaire->remplacement_supplementaire))
                    $sous_formulaire->remplacement_supplementaire = json_encode($sous_formulaire->remplacement_supplementaire);

                $champ_liaison = Champ_libre::where('type_element', $vue)->where('type', 42)->where('type_element_ajax', $formulaire)->first();

                if(empty($champ_liaison))
                    continue;

                $sous_formulaire->champ_liaison = $champ_liaison->nom_sql;

                $management_sous_formulaire = management('eden_sous_formulaire');

                $management_sous_formulaire->enregistre($sous_formulaire->toArray());

                try{

                    $nom_affichage_sous_formulaire = table_libre($vue)->nom_table;

                }
                catch (Exception $e){

                    $nom_affichage_sous_formulaire = $vue;

                }

                $champ->nom_sous_formulaire = $formulaire . '_sous_formulaire_' . $vue;
                $champ->nom_affichage_sous_formulaire = $nom_affichage_sous_formulaire;
                $champ->id_sous_formulaire = $management_sous_formulaire->modele->id;

                $champ->save();

                Maintenance_management::generer_fichier_migration_formulaire($formulaire);
                Maintenance_management::generer_fichier_migration_sous_formulaire($champ->nom_sous_formulaire);
                $sous_formulaire = null;
            }

        }

        return true;

    }

}