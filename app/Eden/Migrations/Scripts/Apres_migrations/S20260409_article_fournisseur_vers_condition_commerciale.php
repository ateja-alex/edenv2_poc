<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Element_log;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20260409_article_fournisseur_vers_condition_commerciale implements Script
{

    public function execute() {

        if(!Schema::hasTable('article_fournisseur_evolution_prix') && Schema::hasColumn('condition_commerciale', 'tarif'))
            return true;

        $conditions_commerciales = modele('condition_commerciale')->whereNotNull('article_fournisseur_id')->get()->keyBy('article_fournisseur_id');
        $article_fournisseur = modele('article_fournisseur')->whereNotIn('id', $conditions_commerciales->keys())->get();
        $management = management('condition_commerciale');

        // création des conditions commerciales à partir des articles fournisseurs n'ayant pas de condition commerciale associée
        foreach ($article_fournisseur as $af) {

            if(isset($conditions_commerciales[$af->id]))
                continue;

            $condition_commerciale = [
                'article_fournisseur_id' => $af->id,
                'prix_achat' => $af->tarif,
                'conditionnement' => $af->conditionnement_id,
            ];

            $management->modele = null;
            $management->enregistre($condition_commerciale);

            $conditions_commerciales->put($af->id, $management->modele);
        }

        // mise à jour des détails de logs : on corrige le nom des champs erronés
        DB::update('
            UPDATE element_log_detail 
            JOIN element_log ON element_log.id_element_log = element_log_detail.id_element_log
            SET champ = "prix_achat" 
            WHERE champ = "tarif" AND type_element = "article_fournisseur_evolution_prix"
        ');

        // rattachement des logs d'évolution de prix des articles fournisseurs aux conditions commerciales correspondantes
        $logs_a_rattacher = Element_log::select('element_log.*', 'article_fournisseur_evolution_prix.article_fournisseur_id')
            ->join('article_fournisseur_evolution_prix', function ($join) {
                $join->on('article_fournisseur_evolution_prix.id', '=', 'element_log.id_element')
                    ->where('element_log.type_element', 'article_fournisseur_evolution_prix');
            })
            ->whereIn('article_fournisseur_evolution_prix.article_fournisseur_id', $conditions_commerciales->keys())
            ->get()
            ->groupBy('article_fournisseur_id');

        foreach($conditions_commerciales as $article_fournisseur_id => $condition_commerciale) {

            if (isset($logs_a_rattacher[$article_fournisseur_id])) {

                foreach ($logs_a_rattacher[$article_fournisseur_id] as $log) {

                    if(empty($log->champ))
                        continue;
                    
                    $management_champ = management('article_fournisseur_evolution_prix')->champ($log->champ);

                    $log->id_element = $condition_commerciale->id;
                    $log->type_element = 'condition_commerciale';

                    if(empty($log->valeur_apres_txt) && !empty($log->valeur_apres))
                        $log->valeur_apres_txt = $management_champ->affiche($log->valeur_apres);

                    $log->save();
                }
            }
        }

        // suppression du type d'élément article_fournisseur_evolution_prix qui n'est plus utilisé
        Script_management::supprimer_type_element('article_fournisseur_evolution_prix');

        $this->passage_article_fournisseur_en_fiche();
        $this->ajout_sous_formulaire_condition_commerciale();
        $this->supprimer_champ_tarif();

        return true;
    }

    private function supprimer_champ_tarif() {
        
        $formulaires_champs = Formulaires_champs::where('type_element', 'article_fournisseur')
            ->where('nom_sql', 'tarif')
            ->get();

        $colonnes = Colonne::select('listes_libres_colonnes.*', 'eden_listeslibres.type_element', 'eden_listeslibres.id as liste_libre_id', 'eden_listeslibres.id_rapport')
            ->join('eden_listeslibres', 'listes_libres_colonnes.liste_libre_id', '=', 'eden_listeslibres.id')
            ->where('eden_listeslibres.type_element', 'article_fournisseur')
            ->where('valeur', 'like', '%tarif%')
            ->get();
        
       Champ_libre::where('type_element', 'article_fournisseur')
            ->where('nom_sql', 'tarif')
            ->update(['inactif' => 1]);

        if(file_exists(app_path()."/Migrations/article_fournisseur.php"))
            Table_libre_management::generer_fichier_migration('article_fournisseur', true); 
        
        foreach ($formulaires_champs as $champ) {

            $champ->delete();

            if(file_exists(app_path()."/Migrations/Formulaires_libres/" . $champ->nom_formulaire . ".php"))
                Maintenance_management::generer_fichier_migration_formulaire($champ->nom_formulaire, true);
        }
        
        foreach ($colonnes as $colonne) {

            $colonne->delete();

            $nom_dossier_liste = "Listes_libres" . ($colonne->export ? "_export" : (str_contains($colonne->id_rapport, 'fiche_') ? "_fiches" : ""));

            if(file_exists(app_path()."/Migrations/" . $nom_dossier_liste . "/" . ($colonne->id_rapport ?? $colonne->type_element) . ".php"))
                Liste_libre_management::generer_fichier_migration_liste_libre($colonne->liste_libre_id, true);
        }  
    }

    /**
     * 
     * Passage de la table article fournisseur en fiche si un fichier de migration spé existe
     * 
     */
    private function passage_article_fournisseur_en_fiche() {

        if(!file_exists(app_path()."/Migrations/article_fournisseur.php"))
            return;

        $table = Table_libre::where('type_element', 'article_fournisseur')->first();

        if($table->fiche)
            return;

        $table->fiche = 1;
        $table->save();

        Table_libre_management::generer_fichier_migration('article_fournisseur', true);
    }

    /**
     * 
     * Ajout du sous formulaire de conditions commerciales à l'article fournisseur
     * 
     */
    private function ajout_sous_formulaire_condition_commerciale() {
    
        if(!file_exists(app_path()."/Migrations/Formulaires_libres/article_fournisseur.php"))
            return;

        $sous_formulaire_present = Formulaires_champs::where('type_element', 'article_fournisseur')
            ->where('type_champ', 3)
            ->where('nom_sous_formulaire', 'article_fournisseur_sous_formulaire_condition_commerciale')
            ->first();

        if($sous_formulaire_present !== null)
            return;

        $champ_sous_formulaire = new Formulaires_champs();
        $champ_sous_formulaire->type_element = 'article_fournisseur';
        $champ_sous_formulaire->nom_formulaire = 'article_fournisseur';
        $champ_sous_formulaire->type_champ = 3;
        $champ_sous_formulaire->ordre = Formulaires_champs::where('type_element', 'article_fournisseur')->max('ordre') + 1;
        $champ_sous_formulaire->nom_sous_formulaire = 'article_fournisseur_sous_formulaire_condition_commerciale';
        $champ_sous_formulaire->nom_affichage_sous_formulaire = 'formulaire.article_fournisseur.sous_formulaire.article_fournisseur_sous_formulaire_condition_commerciale.titre';
        $champ_sous_formulaire->save();
        
        Maintenance_management::generer_fichier_migration_formulaire('article_fournisseur');
    }
}
