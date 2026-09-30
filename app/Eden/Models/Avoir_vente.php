<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Avoir_vente extends Model {

    protected $connection = "mysql";

    protected $table = 'avoir_vente';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
