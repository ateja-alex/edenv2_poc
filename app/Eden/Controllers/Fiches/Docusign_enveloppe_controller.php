<?php

namespace App\Eden\Controllers\Fiches;
use App\Eden\Controllers\Fiche_controller;

class Docusign_enveloppe_controller extends Fiche_controller
{

    /**
     *
     * Permet d'envoyer la signature d'un document
     *
     */
    public function envoyer_signature(){

        $management = management('docusign_enveloppe', $this->id_element);

        $retour = $management->envoie_signature();

        $management_element = management($management->modele->type_element,$management->modele->element_id);

        return response()->json(array(
            'retour' => $retour,
            'redirection' => $management_element->lien_vers_element()
        ));
    }
}