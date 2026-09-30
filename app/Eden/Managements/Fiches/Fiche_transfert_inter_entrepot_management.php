<?php
namespace App\Eden\Managements\Fiches;
use App\Eden\Managements\Fiche_management;
/**
 * Gestion des fiches d'article
 */
class Fiche_transfert_inter_entrepot_management extends Fiche_management {

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'afficher_pdf',
            'ordre' => 0
        ];

        return $options_fil_ariane;
    }
    
}