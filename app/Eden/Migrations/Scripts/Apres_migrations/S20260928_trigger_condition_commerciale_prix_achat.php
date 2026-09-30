<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 *
 * Remplace la logique PHP de Condition_commerciale_management (copie du prix d'achat de la condition
 * commerciale par défaut du fournisseur prioritaire vers l'article/le conditionnement) par deux triggers
 * applicatifs, pour rendre la règle configurable sans code.
 *
 */
class S20260928_trigger_condition_commerciale_prix_achat implements Script{

    public function execute(){

        $table_condition_commerciale_id = Table_libre::where('type_element', 'condition_commerciale')->value('id');
        $table_article_id = Table_libre::where('type_element', 'article')->value('id');
        $table_conditionnement_id = Table_libre::where('type_element', 'conditionnement')->value('id');

        $this->enregistre_trigger(
            'Copie prix achat condition commerciale vers article',
            $table_condition_commerciale_id,
            $table_article_id,
            "SELECT article.*
                FROM article
                JOIN article_fournisseur ON article_fournisseur.article_id = article.id
                JOIN condition_commerciale ON condition_commerciale.article_fournisseur_id = article_fournisseur.id
                WHERE condition_commerciale.id = #id_cible#
                AND COALESCE(condition_commerciale.inactif,0) = 0
                AND article_fournisseur.fournisseur_prioritaire = 1
                AND condition_commerciale.catalogue_tarif_id IS NULL
                AND COALESCE(condition_commerciale.conditionnement,0) = 0
                AND condition_commerciale.prix_achat > 0",
            "UPDATE article
                JOIN article_fournisseur ON article_fournisseur.article_id = article.id
                JOIN condition_commerciale ON condition_commerciale.article_fournisseur_id = article_fournisseur.id
                SET article.prix_d_achat = condition_commerciale.prix_achat
                WHERE condition_commerciale.id = #id_cible#
                AND COALESCE(condition_commerciale.inactif,0) = 0
                AND article_fournisseur.fournisseur_prioritaire = 1
                AND condition_commerciale.catalogue_tarif_id IS NULL
                AND COALESCE(condition_commerciale.conditionnement,0) = 0
                AND condition_commerciale.prix_achat > 0"
        );

        $this->enregistre_trigger(
            'Copie prix achat condition commerciale vers conditionnement',
            $table_condition_commerciale_id,
            $table_conditionnement_id,
            "SELECT conditionnement.*
                FROM conditionnement
                JOIN condition_commerciale ON condition_commerciale.conditionnement = conditionnement.id
                JOIN article_fournisseur ON article_fournisseur.id = condition_commerciale.article_fournisseur_id
                WHERE condition_commerciale.id = #id_cible#
                AND COALESCE(condition_commerciale.inactif,0) = 0
                AND article_fournisseur.fournisseur_prioritaire = 1
                AND condition_commerciale.catalogue_tarif_id IS NULL
                AND COALESCE(condition_commerciale.conditionnement,0) != 0
                AND condition_commerciale.prix_achat > 0",
            "UPDATE conditionnement
                JOIN condition_commerciale ON condition_commerciale.conditionnement = conditionnement.id
                JOIN article_fournisseur ON article_fournisseur.id = condition_commerciale.article_fournisseur_id
                SET conditionnement.prix_achat = condition_commerciale.prix_achat
                WHERE condition_commerciale.id = #id_cible#
                AND COALESCE(condition_commerciale.inactif,0) = 0
                AND article_fournisseur.fournisseur_prioritaire = 1
                AND condition_commerciale.catalogue_tarif_id IS NULL
                AND COALESCE(condition_commerciale.conditionnement,0) != 0
                AND condition_commerciale.prix_achat > 0"
        );

        return true;
    }

    private function enregistre_trigger($nom, $type_element_id, $type_element_concerne_id, $requete_elements_concernes, $requete){

        $trigger = modele('trigger_eden')->where('nom', $nom)->first();

        if($trigger !== null){

            if($trigger->requete == $requete)
                return;

            $management = management('trigger_eden', $trigger->id, $trigger);
        }
        else
            $management = management('trigger_eden');

        $retour = $management->enregistre(array(
            'nom' => $nom,
            'type_element_id' => $type_element_id,
            'type_element_concerne_id' => $type_element_concerne_id,
            'requete' => $requete,
            'requete_elements_concernes' => $requete_elements_concernes,
        ));

        if($retour !== true)
            throw new \Exception($retour, 500);
    }
}
