<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Colonne extends Table_avec_gestion_profil {

    protected $connection = 'mysql';

    protected $table = 'listes_libres_colonnes';
	
    public $timestamps = false;

    /**
     *
     * Utilisé pour appliquer automatiquement la gestion des profils, et la suppression des éléments inactifs
     *
     */
    public function newQuery($excludeDeleted = true){

        $builder = parent::newQuery($excludeDeleted);

        $colonnes_non_acces = [];

        if (session()->has('cache.droits_profils.divers_non_acces'))
            $colonnes_non_acces = session()->get('cache.droits_profils.divers_non_acces')['colonne_liste'] ?? [];

        if(!empty($colonnes_non_acces))
            $builder->whereNotIn('listes_libres_colonnes.id',$colonnes_non_acces);

        return $builder;
    }
}
