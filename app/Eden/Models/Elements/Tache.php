<?php

namespace App\Eden\Models\Elements;

class Tache extends Element {

	public    $timestamps = false;

	protected $table      = "tache";
	protected $primaryKey = "id";
    protected $avec_parents = false;

    public function client() {

	    return $this->hasOne('App\Eden\Models\Elements\Client', 'id', 'client_id');
    }

     public function projet() {

	    return $this->hasOne('App\Eden\Models\Elements\Projet', 'id', 'projet_id');
    }


    public function avec_parents() {

        $this->avec_parents = true;

        return $this;
    }

    /**
     *
     * Supprime les tâches parentes de la requête
     *
     */
    public function newQuery($excludeDeleted = true) {

        $builder = parent::newQuery($excludeDeleted);

        // on retire automatiquement les éléments parents
        if($this->avec_parents === false) {

            $builder->where(\DB::raw("coalesce(".$this->table.".tache_parent, '')"), 'like', '');
        }

        return $builder;
    }
}
