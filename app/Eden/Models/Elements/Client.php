<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Client extends Element {
	
	protected $table = 'client';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	public function liste() {
		
		return modele('client')->select('nom', 'id')->get()->pluck('nom', 'id');
	}
}
