<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Champs\Champ;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;

class Notification_manuelle_element_management extends Element_management {

    /**
     *
     * Permet de récupérer l'affichage d'un élément via son id
     *
     */
    public function recuperer_element_via_id($modele){

        if($modele->notification_manuelle_id == null || $modele->element_id == null)
            return '';

        $notification_manuelle = modele('notification_manuelle',$modele->notification_manuelle_id);

        if($notification_manuelle != null && $notification_manuelle->type_element_id != null){

            $type_element = Table_libre::where('id',$notification_manuelle->type_element_id)->first()->type_element;

            $modele_element = modele($type_element,$modele->element_id);

            if($modele_element != null)
                return management($type_element,$modele_element->id)->affiche_lien();
        }

        return '';
    }

    /**
     *
     * Permet de récupérer les destinataires du mail de notification
     *
     */
    public function destinataires($type){

        $type_lien = 'parametrage_destinataire_'.$type;

        $destinataires = service('email')
            ->chargement_elements($type_lien, [[$this->modele->element_id]], ['notification_manuelle_id' => $this->modele->notification_manuelle_id]);

        if($type == 'email'){
            $destinataires = array_map(function($destinataires_type){
                $destinataires_type = array_map(function($valeur){
                    return $valeur['valeur'];
                },$destinataires_type);

                return $destinataires_type;
            },$destinataires['valeurs'][0] ?? []);
        }
        else
            $destinataires = array_map(function($valeur){
                return $valeur['valeur'];
            },$destinataires['valeurs'][0] ?? []);

        return $destinataires;
    }

    /**
     *
     * Permet de récupérer les pièces jointes du mail de notification
     *
     */
    public function pieces_jointes_mail(){

        $pieces_jointes = service('email')
            ->chargement_elements('parametrage_piece_jointe_email', [[$this->modele->element_id]], ['notification_manuelle_id' => $this->modele->notification_manuelle_id]);

        $pieces_jointes = array_map(
            function($piece_jointe){

                if(isset($piece_jointe['valeur']) && strpos($piece_jointe['valeur'],'eden/element') === 0){
                    $type_element = explode('/',$piece_jointe['valeur'])[2];
                    $element_id = explode('/',$piece_jointe['valeur'])[3];
                    $piece_jointe['valeur'] = storage_path('app/'.management($type_element,$element_id)->recupere_chemin_pdf());
                }

                $extension = pathinfo($piece_jointe['valeur'], PATHINFO_EXTENSION);

                $nom = $piece_jointe['affichage_select'] ?? $piece_jointe['affichage'];

                if(!str_ends_with($nom,'.'.$extension))
                    $nom .= '.'.$extension;

                return [
                    'chemin' => $piece_jointe['valeur'],
                    'informations' => [
                        'as' => $nom
                    ]
                ];
            },
            $pieces_jointes['valeurs'][0] ?? []
        );

        return $pieces_jointes;

    }

    /**
     *
     * Permet de traduire dans la bonne langue la valeur de l'erreur lors de l'exécution de l'envoi d'une notification
     *
     */
    public function affichage_erreur($modele){

        preg_match_all('/#(.*?)#/', $modele->erreur_envoi, $correspondances);

        if(empty($correspondances))
            return $modele->erreur_envoi;

        $a_remplacer = $correspondances[0];
        $traductions = $correspondances[1];

        $remplacements = array();

        foreach($traductions as $traduction){

            $remplacements[] = traduction("interface.notification_manuelle_element.erreur.".$traduction);
        }

        return str_replace($a_remplacer,$remplacements,$modele->erreur_envoi);
    }
}