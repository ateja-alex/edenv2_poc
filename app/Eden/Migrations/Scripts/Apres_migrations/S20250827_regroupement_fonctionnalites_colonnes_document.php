<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20250827_regroupement_fonctionnalites_colonnes_document implements Script {

    public function execute() {

        $anciennes_fonctionnalites = [

            'devis_vente_prix_d_achat_et_marge',
            'devis_vente_prix_d_achat_et_marge_pourcentage',
            'commande_vente_prix_d_achat_et_marge',
            'commande_vente_prix_d_achat_et_marge_pourcentage',
            'facture_vente_prix_d_achat_et_marge',
            'facture_vente_prix_d_achat_et_marge_pourcentage'
        ];

        $colonnes_a_ajouter = array();

        foreach($anciennes_fonctionnalites as $ancienne_fonctionnalite){

            $valeur = fonctionnalite($ancienne_fonctionnalite);

            if($valeur == true && strpos($ancienne_fonctionnalite, 'pourcentage') !== false && 
                !in_array('marge_brute_pourcentage', $colonnes_a_ajouter)){

                if(!in_array('prix_achat', $colonnes_a_ajouter))
                    $colonnes_a_ajouter['prix_achat'] = true;

                $colonnes_a_ajouter['marge_brute_pourcentage'] = true;
                $colonnes_a_ajouter['marge_pourcentage'] = true;
            }
            else if($valeur == true && !in_array('marge_brute_montant', $colonnes_a_ajouter)){

                if(!in_array('prix_achat', $colonnes_a_ajouter))
                    $colonnes_a_ajouter['prix_achat'] = true;

                $colonnes_a_ajouter['marge_brute_montant'] = true;
                $colonnes_a_ajouter['marge'] = true;
            }
        }

        Script_management::modifier_fonctionnalites(array('documents_colonnes_a_afficher_vente' => $colonnes_a_ajouter));
        Script_management::modifier_fonctionnalites(array('documents_colonnes_a_afficher_achat' => $colonnes_a_ajouter));

        return true;
    }
}
