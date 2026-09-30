<?php

namespace App\Eden\Models\Elements;

class Ligne_rapport_parametrable extends Element {
	
	protected $table = 'ligne_rapport_parametrable';
    protected $primaryKey = 'id';
    
    public function familles() {
		
		return \DB::table('ligne_rapport_parametrable_famille_id')->where('cle_locale', $this->id)->get()->pluck('valeur');
    }

}
