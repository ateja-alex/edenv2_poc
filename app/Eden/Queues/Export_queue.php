<?php

namespace App\Eden\Queues;

use App\Eden\Managements\Listes_management;
use App\Eden\Models\Liste_libre;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Export_queue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $export;
    protected $page;
    private $suffixe;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export, $page, $suffixe = '') {
        $this->export = $export;
        $this->page = $page;
        $this->suffixe = $suffixe;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        if(!defined('export_en_cours'))
            define('export_en_cours', 'excel');

        // on récupère un export à réaliser (on les traite un à la fois).
        $export_a_realiser = modele('export')->where('id',$this->export)->first();

        if($export_a_realiser->type_element_createur == 'utilisateur')
            session()->put('utilisateur_eden',modele('utilisateur',$export_a_realiser->element_id_createur));
        else {
            define('type_export_en_cours', 'extranet');

            management($export_a_realiser->type_element_createur,$export_a_realiser->element_id_createur)->creation_session();
        }

        // on initialise les différents attributs afin de récupérer les données à récupérer
        $id_liste = $export_a_realiser->id_liste;
        $parametres = json_decode($export_a_realiser->parametres, true);
        $type_export = $export_a_realiser->type_export;
        $service = service('export');

        if(in_array($type_export,['sur_mesure_pdf', 'pdf']))
            $extension = 'pdf';
        else if(in_array($type_export,['sur_mesure_csv', 'csv']))
            $extension = 'csv';
        else
            $extension = 'xlsx';

        $taille_chunk = $service->taille_chunk[$extension];

        $liste_libre = Liste_libre::where('id',$id_liste)->first();
        
        $management = liste($liste_libre->type_element,$liste_libre->id_rapport ?? false);
        $parametres['page'] = $this->page;
        $donnees = $management->recupere_liste($id_liste, $parametres, $taille_chunk, $type_export);

        if($this->page == 1)
            $export_a_realiser->nombre_en_cours = count($donnees['lignes']);
        else {
            $export_a_realiser->page = $this->page;
            $export_a_realiser->nombre_en_cours += count($donnees['lignes']);
        }

        $export_a_realiser->save();

        if(in_array($extension,['csv','xlsx']))
            $service->exporter_xlsx_csv($donnees, $export_a_realiser->fichier . $this->suffixe . '.' . $extension);
        else
            $service->exporter_pdf($donnees, $export_a_realiser->fichier . $this->suffixe);
    }
}