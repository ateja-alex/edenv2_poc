<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Contact extends Element {
	
	protected $table = 'contact';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
}
