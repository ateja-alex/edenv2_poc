<?php

namespace App\Eden\Managements\Elements;

class Configuration_email_management extends Element_management {

    /**
     *
     * On surcharge la méthode enregistre pour gérer les lignes des documents
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(isset($modifications['valeur_par_defaut']) && $modifications['valeur_par_defaut']){

            $count = modele('configuration_email')->where('type', $modifications['type'])->where('valeur_par_defaut', 1);

            if($this->existe())
                $count = $count->where('id', '!=', $this->modele->id);

            $count = $count->count();

            if($count >= 1)
                return traduction('messages.php.configuration_email.erreur_valeur_par_defaut');
        }

        $retour = parent::enregistre($modifications, $modele);

        return $retour;
    }
}