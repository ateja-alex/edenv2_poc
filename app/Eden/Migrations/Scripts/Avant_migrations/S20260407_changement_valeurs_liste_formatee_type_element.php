<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Champs\Champ;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Variables;
use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Table_libre;
use DB;
use Schema;

class S20260407_changement_valeurs_liste_formatee_type_element implements Script{
    public function execute(){

        // Tout ou rien : un echec au milieu laisserait des champs deja passes en
        // liste 71 (plus selectionnes au relancement) avec des valeurs non converties
        return DB::transaction(fn() => $this->conversion());
    }

    private function conversion(){

        // type_reference est ajoutee par l'etape de structure (apres les scripts avant) :
        // si elle n'existe pas encore, aucun champ multiple ne peut y faire reference
        $avec_multiples = Schema::hasColumn('eden_champslibres', 'type_reference');

        $id_tables_documents = Table_libre::whereIn('type_element', Variables::$documents_gescom)->pluck('id', 'nom_table_sql')->toArray();

        $association_valeurs = array(
                1 => $id_tables_documents['acompte_vente'], //acompte_vente
                2 => $id_tables_documents['avoir_vente'], //avoir_vente
                3 => $id_tables_documents['bl_vente'], //bl_vente
                4 => $id_tables_documents['commande_vente'], //commande_vente
                5 => $id_tables_documents['devis_vente'], // devis_vente
                6 => $id_tables_documents['facture_vente'], // facture_vente
                7 => $id_tables_documents['avoir_achat'], // avoir_achat
                8 => $id_tables_documents['bl_achat'], // bl_achat
                9 => $id_tables_documents['commande_achat'], //commande_achat
                10 => $id_tables_documents['devis_achat'], //devis_achat
                11 => $id_tables_documents['facture_achat'], //facture_achat
                12 => $id_tables_documents['acompte_achat'], //acompte_achat
                13 => $id_tables_documents['bon_preparation_vente'], //bon_preparation_vente
                14 => $id_tables_documents['bon_retour_vente'], //bon_retour_vente
                15 => $id_tables_documents['bon_retour_achat'], //bon_retour_achat
            );

        // on recupère les tables qui ont un champ de type 20 et de liste_choix 82

        $champs = Champ_libre::where('type', 20)->where('liste_choix', 82)->get();

        $this->changement_valeur_simple($champs, $association_valeurs);

        // même logique mais pour les champs multiples
        if($avec_multiples) {
            $champs_multi = Champ_libre::where('type', 10)->where('type_reference', 20)->where('liste_choix', 82)->get();

            $this->changement_valeur_multiple($champs_multi, $association_valeurs);
        }

        // on fait pareil mais pour liste_choix 118. On change une valeur dans $assocuations_valeurs  : 14 qui correspond au bon_retour_vente dans ce cas

        $champs = Champ_libre::where('type', 20)->where('liste_choix', 118)->get();

        $this->changement_valeur_simple($champs, $association_valeurs);

        if($avec_multiples) {
            $champs_multi = Champ_libre::where('type', 10)->where('type_reference', 20)->where('liste_choix', 118)->get();

            $this->changement_valeur_multiple($champs_multi, $association_valeurs);
        }

        $this->changement_champ_formulaire('transformation_document_temps_modele', 'type_document');

        return true;
    }

    public function changement_valeur_simple($champs, $association_valeurs){
        foreach($champs as $champ){
            $champ->liste_choix = 71;
            $champ->save();
            if(file_exists(app_path('Migrations/'.$champ->type_element.'.php'))){
                Table_libre_management::generer_fichier_migration($champ->type_element,true);
            }
            $lignes_concernees = modele($champ->type_element)->whereNotNull($champ->nom_sql)->get();
            foreach($lignes_concernees as $ligne){
                $valeur = $ligne->{$champ->nom_sql};

                if(isset($association_valeurs[$valeur])){
                    $ligne->{$champ->nom_sql} = $association_valeurs[$valeur];
                    $ligne->save();
                }
            }
        }
    }

    public function changement_valeur_multiple($champs_multi, $association_valeurs){
        foreach($champs_multi as $champ_multi){
            $champ_multi->liste_choix = 71;
            $champ_multi->save();
            if(file_exists(app_path('Migrations/'.$champ_multi->type_element.'.php'))){
                Table_libre_management::generer_fichier_migration($champ_multi->type_element,true);
            }

            foreach($association_valeurs as $ancienne_valeur => $nouvelle_valeur){
                DB::table($champ_multi->table_pivot)
                    ->where('valeur', $ancienne_valeur)
                    ->update(['valeur' => $nouvelle_valeur]);
            }
        }
    }

    public function changement_champ_formulaire($nom_formulaire, $nom_sql){
        $champ = Formulaires_champs::where('nom_formulaire', $nom_formulaire)->where('nom_sql', $nom_sql)->first();
        
        if(!empty($champ)){
            $champ->type_champ = 2;
            $champ->type_vue = 'standard';
            $champ->nom_vue = 'type_document';
            $champ->save();
        }
    
        if (file_exists(app_path() . '/Migrations/Formulaires_libres/transformation_document_temps_modele.php'))
            Maintenance_management::generer_fichier_migration_formulaire('transformation_document_temps_modele');
    }
}