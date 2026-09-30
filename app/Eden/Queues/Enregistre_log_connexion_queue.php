<?php

namespace App\Eden\Queues;

use App\Eden\Exceptions\Eden_exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Enregistre_log_connexion_queue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adresse_ip;
    protected $email;
    protected $succes;
    protected $support;
    protected $navigateur;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($adresse_ip, $email, $succes, $support, $navigateur) {
        $this->adresse_ip = $adresse_ip;
        $this->email = $email;
        $this->succes = $succes;
        $this->support = $support;
        $this->navigateur = $navigateur;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $ctx = stream_context_create(array('http'=>
            array(
                'timeout' => 60,
                'ignore_errors' => true,
            )
        ));

        $location = 'inconnu';

        $reponse = @file_get_contents("http://ipinfo.io/$this->adresse_ip/geo", false, $ctx);

        // $http_response_header est automatiquement rempli par file_get_contents
        $code_http = null;
        if (!empty($http_response_header)) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $match);
            $code_http = $match[1] ?? null;
        }

        if ($reponse !== false && $code_http == 200) {
            $json = json_decode($reponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $pays   = $json['country'] ?? 'inconnu';
                $region = $json['region']  ?? 'inconnu';
                $ville  = $json['city']    ?? 'inconnu';
                $location = "$pays, $region, $ville";
            }
        }     

        management('log_tentative_connexion')->enregistre([
            'adresse_email' => $this->email,
            'ip' => $this->adresse_ip,
            'navigateur' => $this->navigateur,
            'emplacement' => $location,
            'support' => $this->support,
            'date' => date('Y-m-d H:i:s'),
            'succes' => $this->succes,
            'source' => 1,
        ]);
    }

    /**
     * Determine the time at which the job should retry.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds($this->attempts() * 60);
    }
}