<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Operation extends Element {
	
	protected $table = 'operation';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	/**
     * Liste des mouvements liés à une opération de tréso
     */
    public function mouvements() {
		
        return $this->hasMany('App\Eden\Models\Elements\Mouvement', 'id_operation');
    }
}
