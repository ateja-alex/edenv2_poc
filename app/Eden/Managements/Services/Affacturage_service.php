<?php

namespace App\Eden\Managements\Services;


class Affacturage_service {

    /**
     * 
     * 
     * Retourne les PDF des documents qui sont passés en variables
     * 
     */
    public function prepare_liste_documents($liste_des_factures) {

        $documents = [];

        // on boucle sur la liste des factures pour retourner les PDFS
        foreach($liste_des_factures as $facture) {

            $documents[] = array(
                'facture' => $facture,
                'documents' => [storage_path('app/'.$facture->recupere_chemin_pdf())],
            );
        }

        return $documents;
    }
}