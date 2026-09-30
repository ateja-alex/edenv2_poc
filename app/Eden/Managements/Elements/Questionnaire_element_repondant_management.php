<?php

namespace App\Eden\Managements\Elements;

class Questionnaire_element_repondant_management extends Element_management {

    public function activer_action_post_reponse_questionnaire(){

        if($this->modele->type_element == 'client' && $this->modele->type_element_origine == 'campagne_de_prospection') {
            $modele = modele('campagne_de_prospection_client')
                ->where('client_id',$this->modele->element_id)
                ->where('campagne_de_prospection_id',$this->modele->element_origine_id)
                ->first();

            if(!empty($modele))
                management('campagne_de_prospection_client',$modele->id,$modele)->action_post_reponse_questionnaire($this->modele->id);
        }
        else
            management($this->modele->type_element,$this->modele->element_id)->action_post_reponse_questionnaire($this->modele->id);
    }
}