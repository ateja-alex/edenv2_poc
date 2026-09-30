<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Mouvement extends Element {
	
	protected $table = 'mouvement';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	/**
     * Liste des mouvements liés à une opération de tréso
     */
    public function operation() {
		
        return $this->belongsTo('App\Eden\Models\Elements\Operation', 'id_operation');
    }
}
