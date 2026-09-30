<?php

namespace App\Eden\Managements\Fiches\Document;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Fiche_management;
use App\Eden\Variables;

/**
 * Gestion des fiches documents
 */
class Fiche_document_management extends Fiche_management {

    public $document = true;
    public $modele = null;
    public $management = false;

    /**
     *
     * On ajoute les chemins pour simplifier l'affichage
     *
     */
    public function structure_fiche($donnees = array())
    {
        $structure_fiche = parent::structure_fiche($donnees);

        if(!empty($donnees['sans_traitement']) || !isset($structure_fiche['modules']))
            return $structure_fiche;

        $chemins_blocs = $this->chemins_blocs();

        $disponibilites_modules = service('document')->disponibilites_modules();

        // On modifie la structure de la fiche pour gérer les cas des blocs ne s'affichant qu'en modification et de récupérer le chemin du bloc
        // De plus, on retire certains blocs par manque de données comme les documents liés
        foreach ($structure_fiche['modules'] as $cle_structure => &$info_structure) {

            if (isset($info_structure['module'])) {

                if((isset($disponibilites_modules[$info_structure['module']]) &&
                    (
                        ($disponibilites_modules[$info_structure['module']] == 'modification' && empty($this->modele->id)) ||
                        ($disponibilites_modules[$info_structure['module']] == 'validation' && empty($this->modele->valide))
                    )) || !empty($donnees['cacher_'.$info_structure['module']])) {
                    unset($structure_fiche['modules'][$cle_structure]);
                    continue;
                }

                if(isset($chemins_blocs[$info_structure['module']]))
                    $info_structure['chemin'] = $chemins_blocs[$info_structure['module']];

                elseif(strpos($info_structure['module'],'fiche_'.$this->type_element) !== false){

                    if(empty($this->modele->id)){
                        unset($structure_fiche['modules'][$cle_structure]);
                        continue;
                    }

                    $info_structure['liste'] = true;
                }
                else
                    throw new Eden_exception('Module '.$info_structure['module'].' introuvable !');

                continue;
            }

            foreach ($info_structure as &$info_structure_tmp) {
                foreach ($info_structure_tmp['modules'] as $cle_structure => &$info_structure_niveau_2) {

                    if((isset($disponibilites_modules[$info_structure_niveau_2['module']]) &&
                    (
                        ($disponibilites_modules[$info_structure_niveau_2['module']] == 'modification' && empty($this->modele->id)) ||
                        ($disponibilites_modules[$info_structure_niveau_2['module']] == 'validation' && empty($this->modele->valide))
                    )) || !empty($donnees['cacher_'.$info_structure_niveau_2['module']])) {
                        unset($info_structure_tmp['modules'][$cle_structure]);
                        continue;
                    }

                    if(isset($chemins_blocs[$info_structure_niveau_2['module']]))
                        $info_structure_niveau_2['chemin'] = $chemins_blocs[$info_structure_niveau_2['module']];

                    elseif(strpos($info_structure_niveau_2['module'],'fiche_'.$this->type_element) !== false){

                        if(empty($this->modele->id)){
                            unset($info_structure_tmp['modules'][$cle_structure]);
                            continue;
                        }

                        $info_structure_niveau_2['liste'] = true;
                    }
                    else
                        throw new Eden_exception('Module '.$info_structure_niveau_2['module'].' introuvable !');
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

    public function modules_disponibles($uniquement_du_type = false){

        $dossiers_a_verifier = array(
            'blocs',
            'blocs/'.$this->type,
            'blocs/'.$this->type.'/'.$this->type_element,
        );

        if($uniquement_du_type)
            $dossiers_a_verifier = ['blocs/'.$this->type.'/'.$this->type_element];

        $emplacements = array(
            'standard' => app_path('Eden/Views/formulaires/include/document/'),
            'specifique' => resource_path('views/vendor/eden/formulaires/include/document/')
        );

        $modules = array();

        $compatibilite_utilisation = service('document')->compatibilites_utilisations_blocs();

        $disponibilites_modules = service('document')->disponibilites_modules();

        foreach($emplacements as $type_emplacement => $emplacement){

            foreach($dossiers_a_verifier as $dossier) {

                $chemin = $emplacement.$dossier;

                if (is_dir($chemin)) {

                    $repertoire = scandir($chemin);

                    foreach ($repertoire as $fichier) {

                        if ($fichier == '.' || $fichier == '..' || !is_file($chemin.'/'.$fichier) || strpos($fichier,'_extranet') !== false)
                            continue;

                        $fichier = str_replace('.blade.php', '', $fichier);

                        if(isset($compatibilite_utilisation[$fichier]) && !in_array($this->type_element,$compatibilite_utilisation[$fichier]))
                            continue;

                        $index_traduction = 'document.blocs.' . $fichier . '.titre';

                        $traduction = traduction($index_traduction);

                        if ((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction) {

                            $nom_module = str_replace('_', ' ', $fichier);

                            $nom_module = ucfirst($nom_module);

                            service('traduction')->calcul_index_traduction(
                                17,
                                array(
                                    'document',
                                    'blocs',
                                    $fichier
                                ),
                                array(
                                    'titre' => $nom_module,
                                ),
                                $type_emplacement == 'standard'
                            );

                            $traduction = $nom_module;

                            Cache_management::partage_oublie_traductions();
                            Cache_management::invalide();
                        }

                        $modules[$fichier] = array(
                            'nom' => $traduction . ' (' .$fichier. ')',
                        );

                        if(isset($disponibilites_modules[$fichier]))
                            $modules[$fichier]['restriction'] = $disponibilites_modules[$fichier];
                    }
                }
            }
        }

        ksort($modules);

        return $modules;
    }

    /**
     *
     * Récupére les chemins des blocs
     *
     */
    public function chemins_blocs(){

        $chemins_blocs = array();

        $dossiers_a_verifier = array(
            'blocs/'.$this->type.'/'.$this->type_element,
            'blocs/'.$this->type,
            'blocs',
        );

        $emplacements = array(
            'specifique' => resource_path('views/vendor/eden/formulaires/include/document/'),
            'standard' => app_path('Eden/Views/formulaires/include/document/')
        );

        foreach($emplacements as $type_emplacement => $emplacement) {

            foreach ($dossiers_a_verifier as $dossier) {

                $chemin = $emplacement . $dossier;

                if (is_dir($chemin)) {

                    $repertoire = scandir($chemin);

                    foreach ($repertoire as $fichier) {

                        if (isset($chemins_blocs[$fichier]) || $fichier == '.' || $fichier == '..' || !is_file($chemin . '/' . $fichier))
                            continue;

                        $fichier = str_replace('.blade.php', '', $fichier);

                        $chemins_blocs[$fichier] = str_replace('/','.',$dossier).'.'.$fichier;
                    }
                }
            }
        }

        return $chemins_blocs;
    }

    /**
     *
     * Permet de dupliquer la structure d'une fiche
     *
     */
    public function dupliquer_structure($structure_fiche,$extranet){

        $elements_disponibles = $this->chemins_blocs();

        $compatibilite_utilisation = service('document')->compatibilites_utilisations_blocs();

        foreach($compatibilite_utilisation as $bloc => $documents){

            if(!isset($elements_disponibles[$bloc]))
                continue;

            if(!in_array($this->type_element,$documents))
                unset($elements_disponibles[$bloc]);
        }

        $elements_disponibles = array_keys($elements_disponibles);

        if (isset($structure_fiche['modules'])) {

            foreach ($structure_fiche['modules'] as $cle_structure => &$info_structure) {

                if (isset($info_structure['module'])) {
                    if (!in_array($info_structure['module'], $elements_disponibles))
                        unset($structure_fiche['modules'][$cle_structure]);
                } else {
                    foreach ($info_structure as &$info_structure_tmp) {
                        foreach ($info_structure_tmp['modules'] as $cle_structure => &$info_structure_niveau_2) {
                            if (!in_array($info_structure_niveau_2['module'], $elements_disponibles))
                                unset($info_structure_tmp['modules'][$cle_structure]);
                        }
                    }
                }
            }

            // On restructure les données pour ne pas avoir de valeurs vides et ainsi que des clés qui se suivent correctement
            foreach ($structure_fiche['modules'] as $cle_info_structure => &$info_structure) {

                if (isset($info_structure['module']))
                    continue;

                foreach ($info_structure as $cle_info_structure_tmp => &$info_structure_tmp) {
                    if (empty($info_structure_tmp['modules']))
                        unset($info_structure[$cle_info_structure_tmp]);
                    else
                        $info_structure_tmp['modules'] = array_values($info_structure_tmp['modules']);
                }

                if (empty($info_structure))
                    unset($structure_fiche['modules'][$cle_info_structure]);
                else
                    $info_structure = array_values($info_structure);
            }

            $structure_fiche['modules'] = array_values($structure_fiche['modules']);

        }

        $contenu_fichier = "<?php\n\nreturn ".var_export($structure_fiche, true).";\n";

        if ($extranet)
			\Storage::put('eden_fiche_'.$this->type_element.'_extranet.php', $contenu_fichier);
		else
			\Storage::put('eden_fiche_'.$this->type_element.'.php', $contenu_fichier);

        return true;
    }

    /**
     *
     * Filtre la structure de la fiche pour ne récupérer que les listes sur fiche
     *
     */
    public function instancie_listes($structure){

        $listes = [];

        foreach ($structure['modules'] as $module){

            if(!isset($module['module'])){

                foreach ($module as $onglets){

                    foreach ($onglets['modules'] as $sous_module) {

                        if (!empty($sous_module['liste']))
                            $listes['unitaire'][] = $sous_module['module'];

                    }

                }

                continue;

            }

            if (!empty($module['liste']))
                $listes['unitaire'][] = $module['module'];
        }


        return $listes;

    }

    /**
     *
     * On surcharge pour les documents pour gérer le modèle par défaut des paiements
     *
     */
    public function listes_sur_fiche($structure, $campagne_de_prospection_en_cours = false){

        $listes = parent::listes_sur_fiche($structure,$campagne_de_prospection_en_cours);

        if (!empty($listes['listes_sur_fiche']['unitaire'])) {
            $champ_paiement = $this->type == 'vente' ? 'client_id' : 'fournisseur_id';

            foreach($listes['listes_sur_fiche']['unitaire'] as &$liste){
                if($liste['liste_libre']->type_element == 'paiement'){
                    $liste['modele_par_defaut']['cle'] = $champ_paiement;
                    $liste['modele_par_defaut']['valeur'] = $this->modele->{$champ_paiement};
                }
            }
        }

        return $listes;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        if(isset($donnees['type_element'], $donnees['id_element']))
            $this->management = management($donnees['type_element'], $donnees['id_element']);

        foreach ($options_fil_ariane as &$option) {
            if(empty($option['parametres']))
                $option['parametres'] = array();

            $option['parametres']['management_element'] = $this->management;
        }

        if(fonctionnalite('gescom_afficher_bandeau_du_bas') != true) {

            $options_fil_ariane[] = [
                'id' => 'supprimer',
                'ordre' => 0,
                'v-if' => 'document.id && document.inactif !== 1',
                'option_a_droite' => true,
            ];
            $options_fil_ariane[] = [
                'id' => 'dupliquer',
                'ordre' => 1,
                'v-if' => 'document.id && document.inactif !== 1',
                'option_a_droite' => true,
            ];
            $options_fil_ariane[] = [
                'id' => 'enregistrer',
                'ordre' => 2,
                'v-if' => 'document.inactif !== 1',
                'option_a_droite' => true,
                'disponible_creation' => true,
            ];
        }

        if(isset(fonctionnalite('versionning_document')[$this->type_element]) && fonctionnalite('versionning_document')[$this->type_element] == true) {
            $options_fil_ariane[] = [
                'id' => 'versionning_documents',
                'ordre' => 1,
                'v-if' => 'document.id'
            ];
        }

        $options_fil_ariane[] = [
            'id' => 'imprimer',
            'ordre' => 0,
            'v-if' => 'document.id'
        ];

        if(table_libre($this->type_element)->envoyer_email)
            $options_fil_ariane[] = [
                'id' => 'envoyer_par_mail',
                'ordre' => 4,
                'v-if' => 'document.id'
            ];

        $options_fil_ariane[] = [
            'id' => 'transformations_possibles',
            'ordre' => 5,
            'v-if' => 'document.id && document.valide === 1'
        ];

        $options_fil_ariane[] = [
            'id' => 'valider',
            'ordre' => 6,
            'v-if' => 'document.id && document.valide !== 1 && document.inactif !== 1'
        ];

        if(in_array($this->type_element, array('facture_vente', 'avoir_vente', 'acompte_vente', 'facture_achat', 'avoir_achat', 'acompte_achat'))) {

            $options_fil_ariane[] = [
                'id' => 'statut_non_regle',
                'ordre' => 7,
                'v-if' => 'document.id && document.valide === 1 && document.regle === 1'
            ];

            $options_fil_ariane[] = [
                'id' => 'statut_regle',
                'ordre' => 7,
                'v-if' => 'document.id && document.valide === 1 && document.regle !== 1'
            ];
        }

        if(in_array($this->type_element, ['facture_vente', 'avoir_vente'])
            && !empty($this->modele->entite_id)
            && management('entite', $this->modele->entite_id)->modele->facturation_electronique_active == 1) {

            $options_fil_ariane[] = [
                'id' => 'document_envoyer_facturation_electronique',
                'ordre' => 7,
                'v-if' => 'document.id && document.valide === 1 && (!document.statut_facturation_electronique || document.statut_facturation_electronique == 3)'
            ];

            if(management('facturation_electronique_cycle_de_vie')->reste_a_payer($this->type_element, $this->modele) > 0)
                $options_fil_ariane[] = [
                    'id' => 'document_poser_statut_cycle_de_vie',
                    'ordre' => 7,
                    'v-if' => 'document.id && document.statut_facturation_electronique >= 203 && ![210, 213, 501].includes(Number(document.statut_facturation_electronique))'
                ];
        }

        if(service('mfiles')->verifier_synchronisation_mfiles($this->type_element)) {
            $options_fil_ariane[] = [
                'id' => 'envoi_mfiles',
                'ordre' => 8
            ];
        }

        if($this->management !== false)
            $this->management->actions_sur_formulaire_demander_approbation_manuelle($options_fil_ariane);

        if(fonctionnalite('gescom_afficher_bandeau_du_bas') != true && fonctionnalite('gescom_suppression_facture_valide') == 'empecher_suppression' && $this->type_element == "facture_vente") {
            foreach ($options_fil_ariane as $index => $option) {
                if($option['id'] === 'supprimer')
                    $options_fil_ariane[$index]['v-if'] = 'document.id && document.valide !== 1';
            }
        }

        foreach ($options_fil_ariane as $index => $option) {
            if($option['id'] === 'suppression' || $option['id'] === 'enregistrement' || $option['id'] === 'pdf_modele_document')
                unset($options_fil_ariane[$index]);
        }

        if(in_array($this->type_element, Variables::$documents_comptabilisable) &&
            profil($this->type_element, $this->management->modele->entite_id ?? null, 'comptabilisation') && 
            fonctionnalite('listes_factures_autres_action_comptabiliser') !== false)
            $options_fil_ariane[] = [
                'id' => 'comptabiliser',
                'ordre' => 1,
                'v-if' => 'document.id && document.valide && !document.comptabilise'
            ];

        return $options_fil_ariane;

    }
}
