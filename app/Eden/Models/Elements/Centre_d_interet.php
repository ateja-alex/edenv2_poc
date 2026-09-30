<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Centre_d_interet extends Element {
	
	protected $table = 'client';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
}
