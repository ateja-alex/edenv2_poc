<?php

namespace App\Eden\Queues;

use App\Eden\Exceptions\Eden_exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class Synchronisation_externe_queue implements ShouldQueue{

    use Dispatchable, InteractsWithQueue, Queueable;

    protected $management_synchronisation;
    protected $management_origine;
    protected $type_evenement;
    protected $valeurs_champs_evenements;
    protected $champs_evenements_sortis;

    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($management_synchronisation, $management_origine, $type_evenement, $valeurs_champs_evenements, $champs_evenements_sortis) {
        $this->management_synchronisation = $management_synchronisation;
        $this->management_origine = $management_origine;
        $this->type_evenement = $type_evenement;
        $this->valeurs_champs_evenements = $valeurs_champs_evenements;
        $this->champs_evenements_sortis = $champs_evenements_sortis;
    }

    public function backoff(): array{
        return [600, 3600, 7200, 28800];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        synchronisation_service_en_cours(false);

        $fonction = $this->management_synchronisation->fonction_a_appeler($this->type_evenement);

        $retour = $this->management_synchronisation->origine()->service()->{$fonction}($this->valeurs_champs_evenements);

        if($retour['succes'] === false){
            
            management('synchronisation_service_element_erreur')->enregistre([
                'synchronisation_service_element_id' => $this->management_synchronisation->modele->id,
                'type_element' => $this->management_origine->_type_element,
                'element_id' => $this->management_origine->modele->id,
                'type_evenement' => $this->type_evenement,
                'valeurs_transmises' => json_encode($this->valeurs_champs_evenements),
                'code_erreur' => $retour['code'] ?? '#NA#',
                'message_erreur' => $retour['message'] ?? '#ERREUR INCONNUE#'
            ]);
        
            if($retour['retry'] === true)
                $this->fail();

            return;
        }

        if($this->champs_evenements_sortis->isNotEmpty()){

            $modifications = [];

            foreach($this->champs_evenements_sortis as $champ_evenement_sortant){

                if(!empty($champ_evenement_sortant['valeur_dur']))
                    $modifications[$champ_evenement_sortant['nom_sql']] = $champ_evenement_sortant['valeur_dur'];
                else{
                    if(!isset($retour['donnees'][$champ_evenement_sortant->nom_externe]))
                        continue;

                    $modifications[$champ_evenement_sortant['nom_sql']] = $retour['donnees'][$champ_evenement_sortant->nom_externe];
                }
            }

            if(!empty($modifications))
                $this->management_origine->enregistre($modifications);
        }

        management('synchronisation_service_element_historique')->enregistre([
            'synchronisation_service_element_id' => $this->management_synchronisation->modele->id,
            'type_element' => $this->management_origine->_type_element,
            'element_id' => $this->management_origine->modele->id,
            'type_evenement' => $this->type_evenement,
            'valeurs_transmises' => json_encode($this->valeurs_champs_evenements),
            'valeur_recus' => json_encode($retour['donnees'] ?? null)
        ]);
    }

}