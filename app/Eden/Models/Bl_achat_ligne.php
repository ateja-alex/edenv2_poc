<?php

namespace App\Eden\Models;

use App\Eden\Models\Elements\Element;
use Illuminate\Database\Eloquent\Model;

use DB;

class Bl_achat_ligne extends Element {

    protected $connection = "mysql";

    protected $table = 'bl_achat_lignes';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;
	
	public function scopeSum_tarif_ttc($query) {
		
		$colonnes = $query->getQuery()->columns;
		
		$colonnes[] = DB::Raw("SUM(quantite * tarif * (100 + tva) / 100) as sum_tarif_ttc");
		
        return $query->select($colonnes);
    }
	
	public function scopeSum_tarif_ht($query) {
		
		$colonnes = $query->getQuery()->columns;
		
		$colonnes[] = DB::Raw("SUM(quantite * tarif) as sum_tarif_ht");
		
        return $query->select($colonnes);
    }
	
	public function scopeSum_quantite($query) {
		
		$colonnes = $query->getQuery()->columns;
		
		$colonnes[] = DB::Raw("SUM(quantite) as sum_quantite");
		
        return $query->select($colonnes);
    }
	
	public function article() {
		
        return $this->belongsTo('App\Eden\Models\Elements\Article');
    }
	
	public function entete() {
		
        return $this->belongsTo('App\Eden\Models\Elements\Bl_achat', 'id', 'document_id');
    }
	

	

}
