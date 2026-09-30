<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;

class S20220506_regroupement_fonctionnalite_modification_si_document_valide implements Script {

    public function execute() {

        $nom_nouvelle_fonctionnalite = 'modification_document_valide';

        $correspondance = array(
            'devis_vente' => 'modification_devis_valide',
            'devis_achat' => 'modification_devis_achat_valide',
            //'facture_vente' => 'modification_devis_valide',
            'facture_achat' => 'modification_facture_achat_valide',
            'bl_vente' => 'modification_bl_vente_valide',
            'bl_achat' => 'modification_bl_achat_valide',
            //'acompte_vente' => 'modification_devis_valide',
            'acompte_achat' => 'modification_acompte_achat_valide',
            //'avoir_vente' => 'modification_devis_valide',
            'avoir_achat' => 'modification_avoir_achat_valide',
            'commande_vente' => 'modification_commande_valide',
            'commande_achat' => 'modification_commande_achat_valide',
            'bon_retour_vente' => 'modification_bon_retour_vente_valide',
            'bon_preparation_vente' => 'modification_bon_preparation_vente_valide',
        );
        $retour_regroupement = Script_management::regrouper_fonctionnalite(array($nom_nouvelle_fonctionnalite => $correspondance));

        return $retour_regroupement;
    }
}
