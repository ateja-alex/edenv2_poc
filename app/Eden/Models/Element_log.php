<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Element_log extends Model {
	
	protected $table = 'element_log';
	
    protected $primaryKey = 'id_element_log';
	
    public $timestamps = false;
	
	/**
     * On récupère le détail des logs
     */
    public function details_lignes() {
		
        return $this->hasMany('App\Eden\Models\Element_log_detail', 'id_element_log');
    }
}
