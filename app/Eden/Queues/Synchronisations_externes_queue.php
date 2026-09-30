<?php

namespace App\Eden\Queues;

use App\Eden\Exceptions\Eden_exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class Synchronisations_externes_queue implements ShouldQueue{

    use Dispatchable, InteractsWithQueue, Queueable;

    protected $synchronisations;
    protected $type_element;
    protected $element_id;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($synchronisations,$type_element,$element_id) {
        $this->synchronisations = $synchronisations;
        $this->type_element = $type_element;
        $this->element_id = $element_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        synchronisation_service_en_cours(false);

        $element_management = management($this->type_element, $this->element_id);

        foreach($this->synchronisations as $synchronisation) {
            $management_synchronisation = management('synchronisation_service_element', $synchronisation->id, $synchronisation);
            $management_synchronisation->synchronisation_element($element_management);
        }
    }

}