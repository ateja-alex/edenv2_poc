<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Script_management;
use App\Eden\Variables;

class S20230125_recalcule_fiche_specifique implements Script {

    public function execute(){

        $affichage_onglets = fonctionnalite('gescom_document_utiliser_onglets');

        $fonctionnalites_a_verifier = array(
            'gescom_afficher_recap_documents_lies' => true,
            'message' => true,
            'gescom_pj_sur_document' => false,
            'gescom_commentaires_sur_document' => false,
            'gestion_livraison' => true,
            'gestion_facturation' => true,
            'vente' => array(
                'gestion_de_projet' => true,
                'devis_vente' => array(
                    'paiements_sur_les_devis' => false,
                    'gescom_echeances' => false,
                ),
                'commande_vente' => array(
                    'paiements_sur_les_commandes' => false,
                    'gescom_echeances' => false,
                ),
                'facture_vente' => array(
                    'gescom_echeances' => false,
                    'gestion_abonnement' => false,
                    'gestion_recurrences_factures' => false
                ),
            ),
        );

        $this->fonctionnalites = config('fonctionnalites');

        $documents_gescom_par_type = array(
            'vente' => Variables::$documents_vente_gescom,
            'achat' => Variables::$documents_achat_gescom,
        );

        $fonctionnalites_modifier_initial['base'] = $this->verification_fonctionnalite($fonctionnalites_a_verifier);

        if(fonctionnalite('gescom_document_utiliser_bloc_documents_lies')
            && fonctionnalite('gescom_afficher_recap_documents_lies')){

            $fonctionnalites_modifier_initial['base']['ajout'][] = 'documents_lies';
            $fonctionnalites_modifier_initial['base']['retrait'][] = 'documents_lies';
        }

        if($affichage_onglets === false){

            $fonctionnalites_modifier_initial['base']['ajout'][] = 'pied_de_page';
            $fonctionnalites_modifier_initial['base']['retrait'][] = 'recap';
        }

        foreach($documents_gescom_par_type as $type => $documents){

            $fonctionnalites_modifier = $fonctionnalites_modifier_initial['base'];

            if(isset($fonctionnalites_a_verifier[$type]))
                $fonctionnalites_modifier_initial[$type] = $this->verification_fonctionnalite($fonctionnalites_a_verifier[$type], $fonctionnalites_modifier);
            else
                $fonctionnalites_modifier_initial[$type] = $fonctionnalites_modifier;

            foreach ($documents as $document) {

                \Storage::delete('eden_fiche_'.$document.'.php');

                $fonctionnalites_modifier = $fonctionnalites_modifier_initial[$type];

                if(isset($fonctionnalites_a_verifier[$type][$document]))
                    $fonctionnalites_modifier = $this->verification_fonctionnalite($fonctionnalites_a_verifier[$type][$document], $fonctionnalites_modifier);
                
                if(!empty($fonctionnalites_modifier['ajout']) || !empty($fonctionnalites_modifier['retrait']) || $affichage_onglets === false){

                    $fiche_management = fiche($document);

                    $structure_par_defaut = $fiche_management->structure_fiche_par_defaut();

                    if (!isset($structure_par_defaut['modules']))
                        continue;

                    if(!empty($fonctionnalites_modifier['retrait']))
                        $structure_par_defaut = $this->retrait_element($structure_par_defaut,$fonctionnalites_modifier['retrait']);

                    if(!empty($fonctionnalites_modifier['ajout']))
                        $structure_par_defaut = $this->ajout_element($structure_par_defaut,$fonctionnalites_modifier['ajout']);

                    if($affichage_onglets === false)
                        $structure_par_defaut = $this->enlever_onglets($structure_par_defaut);

                    $contenu_fichier = "<?php\n\nreturn ".var_export($structure_par_defaut, true).";\n";

                    \Storage::put('eden_fiche_'.$document.'.php', $contenu_fichier);
                }
            }
        }

        return true;
    }

    public function verification_fonctionnalite($fonctionnalites_a_verifier,$fonctionnalites_modifier = null){

         $correspondance_fonctionnalite_blocs = $this->correspondance_fonctionnalite_blocs();

        if($fonctionnalites_modifier == null){
            $fonctionnalites_modifier = array(
                'ajout' => array(),
                'retrait' => array()
            );
        }

        foreach ($fonctionnalites_a_verifier as $fonctionnalite => $valeur_par_defaut) {

            if(is_array($valeur_par_defaut))
                continue;

            if (isset($this->fonctionnalites[$fonctionnalite]) && $this->fonctionnalites[$fonctionnalite] != $valeur_par_defaut) {
                if ($valeur_par_defaut === false)
                    $fonctionnalites_modifier['ajout'][] = $correspondance_fonctionnalite_blocs[$fonctionnalite];
                else
                    $fonctionnalites_modifier['retrait'][] = $correspondance_fonctionnalite_blocs[$fonctionnalite];
            }
        }

        return $fonctionnalites_modifier;
    }

    public function correspondance_fonctionnalite_blocs(){

        return array(
            'gescom_afficher_recap_documents_lies' => 'documents_lies',
            'message' => 'message',
            'gescom_pj_sur_document' => 'gescom_pj_sur_document',
            'gescom_commentaires_sur_document' => 'gescom_commentaires_sur_document',
            'gestion_livraison' => 'adresse_de_livraison',
            'gestion_facturation' => 'adresse_de_facturation',
            'gestion_de_projet' => 'projet',
            'paiements_sur_les_devis' => 'paiement',
            'gescom_echeances' => 'echeances',
            'paiements_sur_les_commandes' => 'paiement',
            'gestion_abonnement' => 'recurrence',
            'gestion_recurrences_factures' => 'recurrence'
        );
    }

    public function retrait_element($structure_fiche,$retraits){
        
        foreach ($structure_fiche['modules'] as $cle_structure => &$info_structure) {

            if (isset($info_structure['module'])) {

                if(in_array($info_structure['module'],$retraits))
                    unset($structure_fiche['modules'][$cle_structure]);

                continue;
            }

            foreach ($info_structure as &$info_structure_tmp) {
                foreach ($info_structure_tmp['modules'] as $cle_structure => &$info_structure_niveau_2) {

                    if(in_array($info_structure_niveau_2['module'],$retraits))
                        unset($info_structure_tmp['modules'][$cle_structure]);
                }
            }
        }

        // On restructure les données pour ne pas avoir de valeurs vides et ainsi que des clés qui se suivent correctement
        foreach ($structure_fiche['modules'] as $cle_info_structure => &$info_structure) {

            if (isset($info_structure['module']))
                continue;

            foreach ($info_structure as $cle_info_structure_tmp => &$info_structure_tmp) {
                if(empty($info_structure_tmp['modules']))
                    unset($info_structure[$cle_info_structure_tmp]);
                else
                    $info_structure_tmp['modules'] = array_values($info_structure_tmp['modules']);
            }

            if(empty($info_structure))
                unset($structure_fiche['modules'][$cle_info_structure]);
            else
                $info_structure = array_values($info_structure);
        }

        $structure_fiche['modules'] = array_values($structure_fiche['modules']);

        return $structure_fiche;
    }

    public function ajout_element($structure_fiche,$ajouts){

        foreach($ajouts as $ajout){

            $module_ajout = [
                'module' => $ajout,
                'cacher_bloc_v_if' => '1',
                'afficher_par_defaut' => true,
                'taille_avant' => 0,
                'taille' => 12,
                'taille_apres' => 0,
            ];

            if(in_array($ajout,array('gescom_pj_sur_document','gescom_commentaires_sur_document'))){
                $structure_fiche['modules'][] = $module_ajout;
            }
            else{

                foreach($structure_fiche['modules'] as &$module) {

                    if (!isset($module['module']))
                        array_splice($module[0]['modules'], -1, 0, [$module_ajout]);
                }
            }
        }

        return $structure_fiche;
    }

    public function enlever_onglets($structure_fiche){

        $structure_module = [];
        $cle_ajout = 0;

        foreach($structure_fiche['modules'] as $cle_module => &$module) {

            if (!isset($module['module'])){
                unset($structure_fiche['modules'][$cle_module]);
                $structure_module = $module[0]['modules'];
                $cle_ajout = $cle_module;
            }
        }

        if(!empty($structure_module))
            array_splice($structure_fiche['modules'], $cle_ajout, 0, $structure_module);

        return $structure_fiche;
    }
}

