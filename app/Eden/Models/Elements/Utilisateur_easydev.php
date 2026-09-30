<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Utilisateur_easydev extends Element {
	
	public $timestamps = false;
	
	protected $connection = 'mysql-ticket';
	protected $table = "utilisateur";
	
    protected $primaryKey = 'id';
	

}