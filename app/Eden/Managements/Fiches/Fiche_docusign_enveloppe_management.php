<?php
namespace App\Eden\Managements\Fiches;
use App\Eden\Managements\Fiche_management;

/**
 * Gestion des fiches docusign enveloppe
 */
class Fiche_docusign_enveloppe_management extends Fiche_management {

    /**
     * @param $donnees
     * @return mixed
     *
     * La fiche n'est pas accesible quand la signature a déjà été envoyé
     *
     */
    public function prepare_donnees_pour_fiche($donnees = array()){

        $donnees_fiche = parent::prepare_donnees_pour_fiche($donnees);

        if(!empty($donnees_fiche['management_element']->modele->statut))
            abort(403);

        return $donnees_fiche;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'envoyer_signature',
            'ordre' => 0
        ];

        return $options_fil_ariane;
    }
}