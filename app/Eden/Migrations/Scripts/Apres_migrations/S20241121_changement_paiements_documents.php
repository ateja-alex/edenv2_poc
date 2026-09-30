<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class S20241121_changement_paiements_documents implements Script
{

    public function execute(){

        $structure = [];

        $champ_libre_id = Champ_libre::where('type_element', 'paiement')
            ->where('nom_sql','id_document')->first();

        $champ_libre_type_element = Champ_libre::where('type_element', 'paiement')
            ->where('nom_sql','type_element')->first();

        if($champ_libre_id->type != 22)
            Script_management::liste_formatee_a_autre_type_champ([
                'type' => 22,
                'contenu' => 'type_element',
            ], [$champ_libre_id]);

        if($champ_libre_type_element->type != 21)
            Script_management::liste_formatee_a_autre_type_champ([
                'type' => 21,
                'contenu' => '[{"type_element":"devis_vente","valeur":true},{"type_element":"commande_vente","valeur":true},{"type_element":"facture_vente","valeur":true},{"type_element":"acompte_vente","valeur":true},
                    {"type_element":"avoir_vente","valeur":true},{"type_element":"acompte_achat","valeur":true},{"type_element":"facture_achat","valeur":true},{"type_element":"avoir_achat","valeur":true},{"type_element":"note_de_frais","valeur":true}]',
            ], [$champ_libre_type_element]);

        $documents_gescom = Variables::$documents_gescom;

        foreach($documents_gescom as $document) {

            $structure = [];

            if (file_exists(storage_path('app/eden_fiche_'.$document.'_extranet.php')))
                $structure['extranet'] = include(storage_path('app/eden_fiche_'.$document.'_extranet.php'));

            if (file_exists(storage_path('app/eden_fiche_'.$document.'.php')))
                $structure['std'] = include(storage_path('app/eden_fiche_'.$document.'.php'));

            $fiche_management = fiche($document);

            foreach ($structure as $contexte => &$fiche) {

                // pour la fiche classique
                if (isset($fiche['modules'])) {

                    foreach ($fiche['modules'] as &$info_structure) {

                        if (isset($info_structure['module'])) {
                            if ($info_structure['module'] == 'paiement')
                                $info_structure['module'] = 'fiche_'.$document.'_paiement';
                            continue;
                        }

                        foreach ($info_structure as &$info_structure_tmp) {
                            foreach ($info_structure_tmp['modules'] as &$info_structure_niveau_2) {

                                if (isset($info_structure_niveau_2['module'])) {
                                    if ($info_structure_niveau_2['module'] == 'paiement')
                                        $info_structure_niveau_2['module'] = 'fiche_'.$document.'_paiement';
                                }
                            }
                        }
                    }
                }

                // pour la colonne de droite
                if (isset($fiche['colonne_droite'])) {

                    foreach ($fiche['colonne_droite'] as &$info_structure) {

                        if (isset($info_structure['module'])) {
                            if ($info_structure['module'] == 'paiement')
                                $info_structure['module'] = 'fiche_'.$document.'_paiement';
                        }
                    }
                }

                $fiche_management->genere_fichier_fiche($fiche, $contexte == 'extranet');

            }

        }

        return true;
    }

}
