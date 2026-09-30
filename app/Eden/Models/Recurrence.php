<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model {

    protected $table = "eden_recurrence_elements";
    protected $connection = "mysql";
    public $timestamps = false;

}
