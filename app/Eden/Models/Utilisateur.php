<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Cashier\Billable;

class Utilisateur extends Model {

	// use Billable;
	
    protected $table = "utilisateur";
    protected $primaryKey = "id";
    public $timestamps = false;
}
