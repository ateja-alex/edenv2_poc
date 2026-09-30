<?php

namespace App\Eden\Models;

use App\Eden\Models\Elements\Element;
use Illuminate\Database\Eloquent\Model;

class Avoir_vente_ligne extends Element {

    protected $connection = "mysql";

    protected $table = 'avoir_vente_lignes';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
