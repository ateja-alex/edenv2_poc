<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;

class S20220708_regroupement_multiples_fonctionnalite implements Script {

    public function execute() {

        $fonctionnalites_a_regrouper = array();

        $nom_nouvelle_fonctionnalite = 'alimentation_timeline_document_vente';

        $correspondance = array(
            'devis_vente' => 'alimentation_timeline_creation_devis_vente',
            'facture_vente' => 'alimentation_timeline_facture_vente',
            'bl_vente' => 'alimentation_timeline_creation_bl_vente',
            'acompte_vente' => 'alimentation_timeline_acompte_vente',
            'avoir_vente' => 'alimentation_timeline_avoir_vente',
            'commande_vente' => 'alimentation_timeline_creation_commande_vente',
            'bon_preparation_vente' => 'alimentation_timeline_creation_bon_preparation_vente',
        );

        $fonctionnalites_a_regrouper[$nom_nouvelle_fonctionnalite] = $correspondance;

        $nom_nouvelle_fonctionnalite = 'gescom_document';

        $correspondance = array(
            'devis_vente' => 'gescom_devis_vente',
            'devis_achat' => 'gescom_devis_achat',
            'facture_vente' => 'gescom_facture_vente',
            'facture_achat' => 'gescom_facture_achat',
            'bl_vente' => 'gescom_bl_vente',
            'bl_achat' => 'gescom_bl_achat',
            'acompte_vente' => 'gescom_acompte_vente',
            'acompte_achat' => 'gescom_acompte_achat',
            'avoir_vente' => 'gescom_avoir_vente',
            'avoir_achat' => 'gescom_avoir_achat',
            'commande_vente' => 'gescom_commande_vente',
            'commande_achat' => 'gescom_commande_achat',
            'bon_retour_vente' => 'gescom_bon_retour_vente',
            'bon_preparation_vente' => 'gescom_bon_preparation_vente',
        );

        $fonctionnalites_a_regrouper[$nom_nouvelle_fonctionnalite] = $correspondance;

        $nom_nouvelle_fonctionnalite = 'bloquer_validation_document_si_client_retard_paiement';

        $correspondance = array(
            'devis_vente' => 'bloquer_validation_devis_vente_si_client_retard_paiement',
            'devis_achat' => 'bloquer_validation_devis_achat_si_client_retard_paiement',
            'facture_vente' => 'bloquer_validation_facture_vente_si_client_retard_paiement',
            'bl_vente' => 'bloquer_validation_bl_vente_si_client_retard_paiement',
            'acompte_vente' => 'bloquer_validation_acompte_vente_si_client_retard_paiement',
            'avoir_vente' => 'bloquer_validation_avoir_vente_si_client_retard_paiement',
            'commande_achat' => 'bloquer_validation_commande_achat_si_client_retard_paiement',
        );

        $fonctionnalites_a_regrouper[$nom_nouvelle_fonctionnalite] = $correspondance;

        $nom_nouvelle_fonctionnalite = 'valider_document_enregistrement';

        $correspondance = array(
            'devis_vente' => 'valider_devis_vente_enregistrement',
            'commande_vente' => 'valider_commande_vente_enregistrement',
            'bl_vente' => 'valider_bl_vente_enregistrement',
            'avoir_achat' => 'valider_avoir_achat_enregistrement',
            'facture_achat' => 'valider_facture_achat_enregistrement',
        );

        $fonctionnalites_a_regrouper[$nom_nouvelle_fonctionnalite] = $correspondance;

        $retour_regroupement = Script_management::regrouper_fonctionnalite($fonctionnalites_a_regrouper);

        return $retour_regroupement;
    }
}
