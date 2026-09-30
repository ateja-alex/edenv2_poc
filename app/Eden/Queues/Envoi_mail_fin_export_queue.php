<?php

namespace App\Eden\Queues;

use App\Eden\Exceptions\Eden_exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class Envoi_mail_fin_export_queue implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_a_realiser;
    private $export;
    private $nom_fichier;
    private $nombre_fichiers;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export,$nom_fichier, $nombre_fichiers = 1) {

        $this->export = $export;
        $this->nom_fichier = $nom_fichier;
        $this->nombre_fichiers = $nombre_fichiers;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        // on récupère un export à réaliser (on les traite un à la fois).
        $this->export_a_realiser = modele('export')->where('id',$this->export)->first();

        if(strpos($this->nom_fichier, 'zip') !== false){

            $zip = new ZipArchive();
            $nom_sans_extension = explode('.', $this->nom_fichier)[0];

            if(in_array($this->export_a_realiser->type_export,['sur_mesure_pdf', 'pdf']))
                $extension = 'pdf';
            else if(in_array($this->export_a_realiser->type_export,['sur_mesure_csv', 'csv']))
                $extension = 'csv';
            else
                $extension = 'xlsx';

            if ($zip->open(storage_path('app/public/exports/'.$this->nom_fichier), ZipArchive::CREATE | ZipArchive::OVERWRITE ) === TRUE) {
                
                for($i = 1; $i <= $this->nombre_fichiers; $i++){

                    $chemin_fichier = storage_path('app/public/exports/' . $nom_sans_extension . '_partie_' . $i . '.' . $extension);

                    if(file_exists($chemin_fichier))
                        $zip->addFile($chemin_fichier, basename($chemin_fichier));
                }
            }

            $zip->close();

            for($i = 1; $i <= $this->nombre_fichiers; $i++){

                $chemin_fichier = storage_path('app/public/exports/' . $nom_sans_extension . '_partie_' . $i . '.' . $extension);

                if(file_exists($chemin_fichier))
                    unlink($chemin_fichier);
            }
        }

        $this->export_a_realiser->termine = 1;
        $this->export_a_realiser->fichier = $this->nom_fichier;
        $this->export_a_realiser->save();

        $retour = $this->envoyer_mail_fin_export();

        $message_notification = traduction('mails.export_donnees.export_termine') . " <a href='" . asset('storage/exports/'.$this->export_a_realiser->fichier) . "'>" . traduction('mails.export_donnees.ici') . "</a>";

        if($this->export_a_realiser->type_element_createur == 'utilisateur') {

            $notification = management('notification');

            $infos = array(

                'date' => date('Y-m-d H:i:s'),
                'utilisateur_id' => $this->export_a_realiser->element_id_createur,
                'zone' => 'navbar_notifications',
                'contenu_html' => $message_notification,
            );

            $notification->enregistre($infos);
        }

        if($retour !== true) {
            throw new Eden_exception("L'envoi de mail a échoué dans la queue Export_queue. Erreur : " . $retour);
        }
    }

    private function envoyer_mail_fin_export(){

        $champs_mail = [
            'utilisateur' => 'email',
            'client' => 'adresse_email',
            'contact' => 'adresse_email'
        ];

        $createur = modele($this->export_a_realiser->type_element_createur, $this->export_a_realiser->element_id_createur);

        $email = $createur->{$champs_mail[$this->export_a_realiser->type_element_createur]};

        $langue = $createur->langue ?? 'fr';

        $lien_export = $this->export_a_realiser->type_element_createur != 'utilisateur' ? str_replace(env('APP_URL'), 'https://'.fonctionnalite('url_extranet'), asset('storage/exports/'.$this->export_a_realiser->fichier)) : asset('storage/exports/'.$this->export_a_realiser->fichier);

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$email],
            'sujet' => 'Votre export est terminé',
        ];

        $variables_email = [
            'langue_destinataire' => $langue,
            'lien_export' => $lien_export
        ];

        return $service_email->envoyer('eden::mails.export_donnees', $variables_email, $parametres_email);
    }
}