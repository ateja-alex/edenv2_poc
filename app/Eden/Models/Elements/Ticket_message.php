<?php

namespace App\Eden\Models\Elements;

use Illuminate\Database\Eloquent\Model;

class Ticket_message extends Element {
	
	public $timestamps = false;
	
	protected $connection = 'mysql-ticket';
	protected $table = 'ticket-message';
	

}