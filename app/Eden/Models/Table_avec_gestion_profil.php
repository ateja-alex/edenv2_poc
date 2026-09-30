<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Table_avec_gestion_profil extends Model {

    /**
     *
     * Utilisé pour appliquer automatiquement la gestion des profils, et la suppression des éléments inactifs
     *
     */
    public function newQuery($excludeDeleted = true){

        $builder = parent::newQuery($excludeDeleted);

        $colonnes_non_acces = [];

        if (session()->has('cache.droits_profils.divers_non_acces'))
            $colonnes_non_acces = session()->get('cache.droits_profils.divers_non_acces')[$this->table] ?? [];

        if(!empty($colonnes_non_acces) && !in_array($this->table,service('profil')->types_elements_gestion_droits()))
            $builder->whereNotIn($this->table.'.'. ($this->primaryKey ?? 'id'),$colonnes_non_acces);

        return $builder;
    }
}
