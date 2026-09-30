<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Panier_detail extends Model {

    protected $connection = "mysql";

    protected $table = 'panier_detail';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
