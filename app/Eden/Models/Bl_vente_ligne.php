<?php

namespace App\Eden\Models;

use App\Eden\Models\Elements\Element;
use Illuminate\Database\Eloquent\Model;

class Bl_vente_ligne extends Element {

    protected $connection = "mysql";

    protected $table = 'bl_vente_lignes';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
