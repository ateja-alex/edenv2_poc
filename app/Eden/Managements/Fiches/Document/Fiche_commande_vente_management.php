<?php

namespace App\Eden\Managements\Fiches\Document;

use App\Eden\Managements\Fiches\Document\Fiche_document_vente_management;

/**
 * Gestion des fiches documents ventes
 */
class Fiche_commande_vente_management extends Fiche_document_vente_management{
    /**
     *
     * Retourne les actions disponibles pour les documents
     *
     * @return array la liste des vues pour chaque action (une vue par action, pour surcharger plus facilement)
     *
     */
    public function options_fil_ariane($donnees) {

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $commande_vente_annulable = fonctionnalite('gescom_commande_vente_annulable_non_supprimable');

        if(!empty($commande_vente_annulable)){

            $options_fil_ariane[] = [
                'id' => "commande_vente_annuler",
                'ordre' => 9,
                'v-if' => 'document.id && document.valide === 1 && document.annule !== 1'
            ];

            if($commande_vente_annulable == 'annulable_non_supprimable') {
                foreach ($options_fil_ariane as $index => $option) {
                    if ($option['id'] === 'supprimer')
                        $options_fil_ariane[$index]['v-if'] = 'document.id && document.valide !== 1';
                }
            }

        }

        return $options_fil_ariane;

    }
}
