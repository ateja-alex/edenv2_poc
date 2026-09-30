<?php

namespace App\Eden\Managements\Services;

use App\Eden\Variables;

/**
 * 
 * Gestion globale des variables dans les textes dans Eden
 * 
 */
class Variables_service {

    /**
     *
     * Permet de récupérer les environnements de prod et de préprod
     *
     */
    public function environnements()
    {

        try {
            $url = env('EDEN_CONSOLE_API_URL').'eden/api/informations_projet';

            $donnees = http_build_query(
                array(
                    'cle' => config('services.eden.cle_api_eden'),
                )
            );

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => $donnees,
                )
            );

            $contexte = stream_context_create($options);
            $informations = file_get_contents($url, false, $contexte);

            $informations_decode = json_decode($informations, true);

            if (isset($informations_decode['url_prod']))
                return $informations_decode;

        } catch (\Exception|\Throwable $e) {

            return false;
        }

        return false;
    }

    /**
     *
     * Elements dont les index ont besoin d'être mise à jour lors de l'appel de la tâche cron
     *
     */
	public function cron_informations_mise_a_jour_index(){

        $types_elements = Variables::$documents_gescom;

        $types_elements[] = 'projet';
        $types_elements[] = 'ticket_client';
        $types_elements[] = 'client';
        $types_elements[] = 'contact';
        $types_elements[] = 'fournisseur';
        $types_elements[] = 'article';

        return $types_elements;
    }
}

