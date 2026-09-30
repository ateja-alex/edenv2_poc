<?php

namespace App\Eden\Managements\Elements;


use DB;


class Destinataire_management extends Element_management {

    /**
     *
     * On vérifie que les destinataire n'existe pas pour ce client
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(isset($this->champ_liaison_creation_sous_formulaire) && 'client_id' == $this->champ_liaison_creation_sous_formulaire)
            return parent::enregistre($modifications, $modele);

        $destinataire_existant = modele('destinataire')
            ->where('client_id',$modifications['client_id'])
            ->where('adresse_email',$modifications['adresse_email'])->first();

        if($destinataire_existant == null || $this->existe())
            return parent::enregistre($modifications, $modele);

        return traduction('messages.php.destinataire.mail_utilise');
    }


}
