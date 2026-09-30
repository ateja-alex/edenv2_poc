<?php

namespace App\Eden\Managements\Fiches\Document;

use App\Eden\Managements\Fiches\Document\Fiche_document_achat_management;

/**
 * Gestion des fiches documents ventes
 */
class Fiche_devis_achat_management extends Fiche_document_achat_management{

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees) {

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'devis_statut_document_accepte',
            'ordre' => 9,
            'v-if' => 'document.id && document.valide === 1 && !document.accepte'
        ];
        $options_fil_ariane[] = [
            'id' => 'devis_statut_document_refuse',
            'ordre' => 10,
            'v-if' => 'document.id && document.valide === 1 && !document.accepte'
        ];

        $options_fil_ariane[] = [
            'id' => 'devis_statut_document_attente',
            'ordre' => 9,
            'v-if' => 'document.id && document.valide === 1 && document.accepte'
        ];

        return $options_fil_ariane;
    }
}
