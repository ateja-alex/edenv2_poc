<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Element {
	
	public $timestamps = false;
	
	protected $connection = 'mysql-ticket';

	public function initie_requete_pour_liste(){

		return $this->where('client_id_easydev', config('eden.client_id_easydev'));
	}
	
}