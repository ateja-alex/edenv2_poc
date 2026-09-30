<?php

namespace App\Eden\Managements\Elements;

class Licence_ensemble_management extends Element_management{

    public $elements = array();

    /**
     *
     * On garde les détails
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        if(empty($this->modele) && env('BASE_LICENCE') !== true && !defined('synchronisation_licence_reference'))
            $modifications['specifique'] = 1;

        $this->elements = array();

        $types = array(
            'routes' => 1,
            'types_elements' => 2,
            'modules' => 3
        );

        foreach($types as $cle => $type){

            if(!empty($modifications[$cle]))
                $this->elements[$type] = $modifications[$cle];
        }


        return parent::enregistre($modifications, $modele);
    }

    /**
     *
     * On enregistre les détails
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $specifique = env('BASE_LICENCE') !== true && !defined('synchronisation_licence_reference') ? 1 : 0;

        $elements = modele('licence_ensemble_element')->where('licence_ensemble_id',$this->modele->id)->get()->groupBy('type')
            ->map(function($type){
                return $type->keyBy('nom');
            });

        $ajouts = [];
        $suppressions = [];

        // On parcourt les éléments présents pour les enregistrer en tant que licence_ensemble_element

        for($type = 1;$type <= 3; $type++){

            if(isset($this->elements[$type])) {

                $modifications = $this->elements[$type];

                // On ajoute une ligne dans licence_ensemble_element et récupère les ajouts
                foreach ($modifications as $element) {

                    if (isset($elements[$type][$element])) {
                        unset($elements[$type][$element]);
                        continue;
                    }

                    $management_elmeent = management('licence_ensemble_element');

                    $management_elmeent->enregistre(array(
                        'licence_ensemble_id' => $this->modele->id,
                        'type' => $type,
                        'nom' => $element,
                        'specifique' => $specifique
                    ));

                    $ajouts[] = array(
                        'type' => $type,
                        'nom' => $element
                    );
                }
            }

            // On supprime une ligne dans licence_ensemble_element et récupère les suppressions
            if(isset($elements[$type])) {

                foreach ($elements[$type] as $element_a_supprimer) {

                    management('licence_ensemble_element', $element_a_supprimer->id, $element_a_supprimer)->supprime();

                    $suppressions[] = $element_a_supprimer->type.'/'.$element_a_supprimer->nom;
                }

            }
        }

        // On met à jour les licences utilisant ces ensembles pour supprimer et ajouter les différents éléments
        $licences_element = modele('licence_element')
            ->where('ensemble_id',$this->modele->id)
            ->get();

        // Ajout des nouveaux éléments
        foreach(array_unique($licences_element->pluck('licence_id')->toArray()) as $licence_id) {

            foreach ($ajouts as $informations_ajout) {

                management('licence_element')->enregistre(array(
                    'licence_id' => $licence_id,
                    'ensemble_id' => $this->modele->id,
                    'type' => $informations_ajout['type'],
                    'nom' => $informations_ajout['nom'],
                    'specifique' => $specifique
                ));
            }
        }

        // Suppression des nouveaux éléments
        foreach($licences_element as $licence_element){

            if(in_array($licence_element->type.'/'.$licence_element->nom,$suppressions))
                management('licence_element', $licence_element->id, $licence_element)->supprime();
        }

        return true;
    }

    /**
     *
     * On vérifie qu'aucune licence n'utilise cet ensemble
     *
     */
    public function supprime($modele = false){

        $licence = modele('licence_element')->where('ensemble_id',$this->modele->id)->first();

        if(!empty($licence))
            return traduction('messages.php.licence.ensemble_non_supprimable');

        return parent::supprime($modele);
    }

    /**
     *
     * On supprime les détails
     *
     */
    public function methodes_post_suppression($modele){

        parent::methodes_post_suppression($modele);

        $elements = modele('licence_ensemble_element')->where('licence_ensemble_id',$this->modele->id)->get();

        foreach($elements as $element_a_supprimer){

            management('licence_ensemble_element',$element_a_supprimer->id,$element_a_supprimer)->supprime();
        }
    }

}