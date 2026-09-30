<?php

namespace App\Eden\Models\Elements;

class Campagne_de_prospection extends Element {
	
	protected $table = 'campagne_de_prospection';
	protected $primaryKey = 'id';

    public function clients() {

        return $this->belongsToMany('App\Eden\Models\Elements\Client');
    }

}
