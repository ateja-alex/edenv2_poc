<?php

namespace App\Eden\Managements\Elements;

class Trigger_eden_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false){

        if(isset($modifications['log_champs']))
            $this->logs_champs = $modifications['log_champs'];

        return parent::enregistre($modifications, $modele);
    }

    /**
     * @param $modele
     * @param $modele_avant
     * @param $modifications
     * @return void
     *
     * Suite à la modification on vide le cache du trigger
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $type_element = type_element_depuis_id($this->modele->type_element_id);

        oublie_cache_eden('triggers.'.$type_element);

        if(isset($this->logs_champs)){

            $logs_champs = json_decode($this->logs_champs);

            $logs_champ_bdd = modele('trigger_eden_log_champ')
                ->where('trigger_eden_id',$this->modele->id)
                ->get()->keyBy('champ');

            foreach($logs_champs as $champ){

                $element = $logs_champ_bdd->where('champ',$champ)->first();

                if(!empty($element)) {
                    $management = management('trigger_eden_log_champ', $element->id, $element);
                    unset($logs_champ_bdd[$champ]);
                }
                else
                    $management = management('trigger_eden_log_champ');

                $management->enregistre([
                    'trigger_eden_id' => $this->modele->id,
                    'champ' => $champ
                ]);
            }

            foreach($logs_champ_bdd as $log_champ_bdd){
                management('trigger_eden_log_champ', $log_champ_bdd->id, $log_champ_bdd)->supprime();
            }
        }
    }

    /**
     * @param $modele
     * @return void
     *
     * Suite à la suppression on vide le cache du trigger
     *
     */
    public function methodes_post_suppression($modele){

        parent::methodes_post_suppression($modele);

        $type_element = type_element_depuis_id($modele->type_element_id);

        oublie_cache_eden('triggers.'.$type_element);
    }

}