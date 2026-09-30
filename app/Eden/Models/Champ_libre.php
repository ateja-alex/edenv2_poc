<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Champ_libre extends Table_avec_gestion_profil {

    protected $connection = "mysql";

    protected $table = 'eden_champslibres';
	
    protected $primaryKey = 'id_cl';
	
    public $timestamps = false;

    public $desactiver_gestion_traduction = false;
	
	/**
	 * 
	 * Utilisé pour appliquer automatiquement la suppression des éléments inactifs
	 * 
	 */
	public function newQuery($excludeDeleted = true) {
		
        $builder = parent::newQuery($excludeDeleted);

        $builder->where('eden_champslibres.inactif', 0);

        return $builder;
    }

    /**
	 * 
	 * Utilisé pour récupérer les champs dans la bonne langue
	 * 
	 */
	public function getNomAttribute() {

        if($this->desactiver_gestion_traduction)
            return $this->attributes['nom'];

        $nom_traduit = $this->attributes['nom'];

        if(!empty($this->index_traduction))
            $nom_traduit = traduction($this->index_traduction.'.nom');

		return $nom_traduit;
    }
}
