<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Element_image extends Model
{

    protected $table = "element_image";
    protected $primaryKey = "id";
    protected $fillable  = ['ordre'];
    public $timestamps = false;
}
