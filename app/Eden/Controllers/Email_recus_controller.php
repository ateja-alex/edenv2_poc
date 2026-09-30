<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Models\Microsoft_email;

class Email_recus_controller extends Controller
{

    /**
     * @param $id_email_recu
     * @return void
     *
     * Permet de charger les pièces jointes d'un email reçu
     *
     */
    public function charger_pieces_jointes($id_email_recu)
    {

        $email_recu = modele('email_recus')->where('id', $id_email_recu)->first();

        $synchro_mail = modele('synchro_mail')->where('utilisateur_id',$email_recu->utilisateur_id)->first();

        if(!empty($synchro_mail) && $synchro_mail->type == 1) {

            $service = service('microsoft_email');

            $route_api = '/users/' . $synchro_mail->email_synchro;

            if (!empty($synchro_mail->dossier_synchroniser))
                $route_api .= '/mailFolders/' . $synchro_mail->dossier_synchroniser;

            try {
                $email_microsoft = $service->graph->createRequest('GET', $route_api . '/messages/' . $email_recu->id_mail)
                    ->setReturnType(Microsoft_email::class)
                    ->execute();

            }
            catch(\Exception $e){
                return response()->json(array(
                    'erreur' => true,
                    'message' => traduction('message.php.email_recus.erreur_recuperation_piece_jointe')
                ));
            }

            $email_microsoft->graph = $service->graph;
            $email_microsoft->adresse_email_boite = $synchro_mail->email_synchro;
            $email_microsoft->dossier_piece_jointe = 'email_recus';

            $contenu = $email_microsoft->contenu_nettoye();
            $pieces_jointes_microsoft = $email_microsoft->getAttachments();

            $pieces_jointes = array();

            foreach ($pieces_jointes_microsoft as $piece_jointe) {
                $pieces_jointes[] = [
                    'fichier' => $piece_jointe->localisation_eden,
                    'nom' => $piece_jointe->getName()
                ];
            }
        }
        else{

            //@todo gérer le cas des autres types de récupération de mail
            $pieces_jointes = [];
            $contenu = $email_recu->texte_html;
        }

        foreach($pieces_jointes as &$pieces_jointe) {

            $pieces_jointe['fichier'] = str_replace('email_recus/', '', $pieces_jointe['fichier']);
        }

        $management = management('email_recus',$email_recu->id,$email_recu);

        $management->enregistre([
            'texte_html' => $contenu,
            'pieces_jointes' => json_encode($pieces_jointes),
            'pieces_jointes_charges' => 1,
        ]);

        return response()->json(array(
            'modele' => $management->modele
        ));
    }
}