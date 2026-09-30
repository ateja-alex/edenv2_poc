<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use DB;

class S20260409_changement_type_colonne_liste_libre implements Script {

    /**
     * 
     * Change le type des colonnes de liste libre de "standard" à "concatenation" si elles ont plusieurs champs dans leur valeur ou un champ + du texte ou juste du texte.
     * 
     */

    public function execute(){

        $colonnes = Colonne::all()->groupBy('liste_libre_id');

        $listes_libres = Liste_libre::select('eden_listeslibres.*','eden_rapports.liste_sur_fiche','eden_rapports.export')
            ->leftJoin('eden_rapports','eden_rapports.id_rapport','eden_listeslibres.id_rapport')
            ->get();

        foreach($listes_libres as $liste_libre){

            $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres';

            if(!empty($liste_libre->id_rapport)) {
                $chemin_dossier_migrations = app_path() . '/Migrations/Rapports';
                if ($liste_libre->liste_sur_fiche == 1)
                    $chemin_dossier_migrations = app_path() . '/Migrations/Listes_libres_fiches';
                else if($liste_libre->export == 1)
                    $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_export';
            }

            $chemin_fichier = $chemin_dossier_migrations.'/'.(!empty($liste_libre->id_rapport)
                ? $liste_libre->id_rapport : $liste_libre->type_element).'.php';

            if(file_exists($chemin_fichier) && isset($colonnes[$liste_libre->id])){
                foreach($colonnes[$liste_libre->id] as $colonne){
                    if($colonne->type == 'standard' || (empty($colonne->type) && empty($colonne->champ) && empty($colonne->methode)) || $colonne->type == 'liaison'){
                        if($colonne->valeur == ''){
                            $colonne->valeur = 'id';
                            $colonne->save();
                        }
                        else if(preg_match('/^#[^#]+#$/', $colonne->valeur) == 0){
                            $colonne->type = 'concatenation';
                            $colonne->save();
                        }else{
                            $type_21_22 = false;
                            if($colonne->type == 'liaison'){
                                $colonne->type = 'standard';
                                if(!empty($colonne->champ) && json_decode($colonne->champ) !== null && json_last_error() === JSON_ERROR_NONE){
                                    $type_21_22 = true;
                                    $valeur_temp = json_decode($colonne->champ, true);
                                    $colonne->valeur = $valeur_temp['colonne_id_dynamique'].'|'.str_replace('#', '', $colonne->valeur);
                                }
                            }
                            if(!$type_21_22)
                                $colonne->valeur = str_replace('#', '', $colonne->valeur);

                            $colonne->save();
                        }
                    }
                    else if($colonne->type == 'methode' || !empty($colonne->methode)){
                        $colonne->valeur = str_replace('#', '', $colonne->valeur);
                        $colonne->save();
                    }
                }

                Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id, true);
            }
        }

        return true;
    }
}