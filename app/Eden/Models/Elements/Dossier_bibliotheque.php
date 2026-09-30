<?php

namespace App\Eden\Models\Elements;

class Dossier_bibliotheque extends Element {

    /**
     *
     * Récupère les entites ayant le droit d'accès au dossier
     *
     */
    public function entites() {

        return $this->belongsToMany('App\Eden\Models\Elements\Entite', 'dossier_bibliotheque_droit_entites', 'cle_locale', 'valeur');
    }




}
