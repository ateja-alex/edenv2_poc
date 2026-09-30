<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Projet extends Element {
	
	protected $table = 'projet';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
}
