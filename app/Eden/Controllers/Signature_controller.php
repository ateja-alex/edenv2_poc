<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cron_management;
use App\Http\Controllers\Controller;

class Signature_controller extends Controller {

	/**
	 *
	 * Permet de gérer les droits de docusign
	 *
	 */
	public function droit_docusign_api() {

        $scopes = ['impersonation','signature'];

        $lien_docusign = strtolower(env('APP_ENV')) == 'prod' ? 'account.docusign.com' : 'account-d.docusign.com';

		$replace = array(
            '{oauth_base_path}' => $lien_docusign,
            '{response_type}' => 'code',
            '{scope}' => rawurlencode(is_array($scopes) ? implode(" ", $scopes) : $scopes),
            '{client_id}' => config('fonctionnalites_integrations.docusign_client_id'),
            '{redirect_uri}' => route('parametrage.index'),
        );

        $url_recuperation_token = "https://{oauth_base_path}/oauth/auth?response_type={response_type}&scope={scope}&client_id={client_id}&redirect_uri={redirect_uri}";

        $url_destination = str_replace(array_keys($replace), array_values($replace), $url_recuperation_token);

		return redirect()->away($url_destination);
	}

    /**
     *
     * Permet de récupérer les informations nécessaires à l'ajout d'un document dans une signature docusign
     *
     */
    public function informations_fichiers($type_element,$element_id){

        $management_element = management($type_element,$element_id);

        $fichiers_disponibles = $management_element->fichiers_lies();

        $destinations_possibles = management('docusign_document')->destinations_possibles($management_element);

        return response()->json(
            array(
                'fichiers_disponibles' => $fichiers_disponibles,
                'destinations_possibles' => $destinations_possibles
            )
        );
    }

    public function relance_signature($element_id){

        $retour = management('docusign_enveloppe',$element_id)->relance_signature();

        return response()->json($retour);
    }

}