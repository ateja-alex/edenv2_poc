<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Licence_management extends Element_management{

    public $ensembles = false;

    /**
     *
     * On garde les détails
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        if(empty($this->modele) && env('BASE_LICENCE') !== true && !defined('synchronisation_licence_reference'))
            $modifications['specifique'] = 1;

        if(!empty($modifications['ensembles']))
            $this->ensembles = $modifications['ensembles'];

        if(!empty($this->modele) && isset($modifications['nombre'])){

            $utilisateurs = modele('utilisateur')->where('licence_id',$this->modele->id)->count();

            if($utilisateurs > $modifications['nombre'])
                return traduction('messages.php.licence.reduction_nombre_licence_impossble');
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

        if($this->ensembles == false)
            return;

        $specifique = env('BASE_LICENCE') !== true && !defined('synchronisation_licence_reference') ? 1 : 0;

        $elements = modele('licence_element')
            ->where('licence_id',$this->modele->id)->get()->groupBy('ensemble_id')->map(function($ensemble){
                return $ensemble->groupBy('type')->map(function($type){
                    return $type->keyBy('nom');
                });
            });

        $elements_ensemble = modele('licence_ensemble_element')->whereIn('licence_ensemble_id',$this->ensembles)->get()
            ->groupBy('licence_ensemble_id')
            ->map(function($ensemble){
                return $ensemble->groupBy('type')->map(function($type){
                    return $type->keyBy('nom');
                });
            });

        // On récupère les élements des ensembles sélectionnés pour la licence et on vient ajouter et supprimer les éléments
        foreach($elements_ensemble as $ensemble_id => $modifications_par_type){

            foreach($modifications_par_type as $type => $modifications) {

                foreach ($modifications as $element => $osf) {

                    if (isset($elements[$ensemble_id][$type][$element])) {
                        unset($elements[$ensemble_id][$type][$element]);
                        continue;
                    }

                    management('licence_element')->enregistre(array(
                        'licence_id' => $this->modele->id,
                        'ensemble_id' => $ensemble_id,
                        'type' => $type,
                        'nom' => $element,
                        'specifique' => $specifique,
                    ));
                }
            }
        }

        foreach ($elements as $ensemble_id => $elements_par_type){

            foreach($elements_par_type as $type => $elements_a_supprimer) {

                foreach ($elements_a_supprimer as $element_a_supprimer) {

                    management('licence_element', $element_a_supprimer->id, $element_a_supprimer)->supprime();
                }
            }
        }

        return true;
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

        Cache_management::invalide();
    }

}