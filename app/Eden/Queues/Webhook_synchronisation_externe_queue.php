<?php

namespace App\Eden\Queues;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Webhook_synchronisation_externe_queue implements ShouldQueue{

    use Dispatchable, InteractsWithQueue, Queueable;

    protected $management_synchronisation;
    protected $donnees;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($management_synchronisation, $donnees) {
        $this->management_synchronisation = $management_synchronisation;
        $this->donnees = $donnees;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        $this->management_synchronisation->webhook($this->donnees);
    
        return;
    }

}