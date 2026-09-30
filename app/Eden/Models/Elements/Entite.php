<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Entite extends Element {
	
	protected $table = 'entite';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	/**
	 * 
	 * Retourne la liste des entités
	 * 
	 */
	public function liste() {
		
		return modele('entite')->orderBy('nom')->get();
	}
}
