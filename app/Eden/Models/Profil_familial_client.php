<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Profil_familial_client extends Model {

    protected $connection = "mysql";

    protected $table = 'profil_familial';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
