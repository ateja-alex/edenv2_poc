<?php

namespace App\Eden\Managements\Elements;

class Modele_email_management extends Element_management {

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        if(!empty($modifications['par_defaut']) && (empty($this->modele_avant->id) || empty($this->modele_avant->par_defaut))){

            $modeles_emails = modele('modele_email')->where('type_element_id', $this->modele->type_element_id)
                ->where('id', '!=', $this->modele->id)
                ->where('par_defaut', 1)
                ->get();

            foreach ($modeles_emails as $modele_email) {
                management('modele_email',$modele_email->id,$modele_email)->enregistre_modele(['par_defaut' => 0]);
            }
        }

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }
}