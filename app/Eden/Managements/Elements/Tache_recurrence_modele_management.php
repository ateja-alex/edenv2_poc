<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Tache_recurrence_modele_management extends Element_management {

    /**
     *
     * On vérifie s'il n'y a pas des données à supprimer des modifications
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(in_array($modifications['type_frequence'], [3,4]) && isset($modifications['jour_concerne']) && !$modifications['jour_concerne']){

            if(isset($modifications['frequence_jour_concerne']))
                unset($modifications['frequence_jour_concerne']);

            if(isset($modifications['jours_concernes']))
                unset($modifications['jours_concernes']);
        }

        return parent::enregistre($modifications, $modele);


    }

    /**
     *
     * On actualise les valeurs de la liste formatée reprenant les modèles de récurrence
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        Cache_management::genere_valeurs_liste_formatees(5);
    }

    /**
     *
     * On actualise les valeurs de la liste formatée reprenant les modèles de récurrence
     *
     */
    protected function methodes_post_suppression($modele) {

        parent::methodes_post_suppression($modele);

        Cache_management::genere_valeurs_liste_formatees(5);
    }
}