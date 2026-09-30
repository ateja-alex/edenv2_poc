<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Wiki_eden extends Element {
	
	public $timestamps = false;
	
	protected $connection = 'mysql-wiki';

	
}