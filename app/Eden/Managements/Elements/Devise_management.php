<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Variables;

class Devise_management extends Element_management {

    /**
     *
     * Vérification document utilise la devise
     *
     */
    public function document_utilise_devise(){

        if(!fonctionnalite('saisie_documents_devise_etrangere'))
            return true;

        $documents_a_verifier = Variables::$documents_gescom;

        foreach($documents_a_verifier as $document){

            $document_qui_utilise = modele($document)->where('devise',$this->modele->id)->first();

            if($document_qui_utilise !== null)
                return table_libre($document)->element." ".$document_qui_utilise->id." utilise la devise ".$this->modele->code.", vous ne pouvez donc pas rendre la devise indisponible";
        }

        return true;
    }

    /**
     *
     * Vérification document utilise la devise
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(isset($modifications['disponible']) && $modifications['disponible'] == 0) {

            $retour = $this->document_utilise_devise();

            if($retour !== true)
                return $retour;
        }

        return parent::enregistre($modifications,$modele);
    }

    /**
     *
     * Vérification document utilise la devise
     *
     */
    public function supprime($modele = false) {

        $retour = $this->document_utilise_devise();

        if($retour !== true)
            return $retour;

        return parent::supprime($modele);

    }

}