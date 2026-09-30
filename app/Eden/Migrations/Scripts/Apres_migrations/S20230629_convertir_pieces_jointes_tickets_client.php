<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Element_piece_jointe;
use Illuminate\Support\Facades\Schema;

class S20230629_convertir_pieces_jointes_tickets_client implements Script {

    public function execute(){

        $tickets = modele('ticket_client')
            ->avec_inactifs()
            ->whereNotNull('pieces_jointes')
            ->get();

        //Verifie que la colonne existe 
        if (Schema::hasColumn('ticket_client_echange', 'piece_jointe'))
            $echanges = modele('ticket_client_echange')
                ->avec_inactifs()
                ->where(function($r){
                    $r->whereNotNull('piece_jointe')
                        ->orWhereNotNull('pieces_jointes');
                })
                ->get();
        else
            $echanges = modele('ticket_client_echange')
                ->avec_inactifs()
                ->where(function($r){
                    $r->whereNotNull('pieces_jointes');
                })
                ->get();

        $management = management('ticket_client');
        $management_echange = management('ticket_client_echange');

        foreach($tickets as $ticket){

            $management->modele = null;

            if(!isset($ticket->pieces_jointes))
                continue;

            $pieces_jointes = json_decode($ticket->pieces_jointes);

            foreach($pieces_jointes as $piece_jointe){

                $fichier = new Element_piece_jointe();
                $fichier->nom = $piece_jointe->nom_original;
                $fichier->titre = $piece_jointe->nom_original;
                $fichier->type_element = 'ticket_client';
                $fichier->element_id = $ticket->id;
                $fichier->chemin = str_replace('public/', '', $piece_jointe->url_storage);

                $fichier->save();
            }

            $management->modele = $ticket;
            $management->enregistre_modele(['pieces_jointes' => null]);
        }

        foreach($echanges as $echange){

            $management_echange->modele = null;

            if(!isset($echange->pieces_jointes))
                continue;

            $pieces_jointes = json_decode($echange->pieces_jointes);

            $ids_pieces_jointes = array();

            //On traite le cas des échanges créés depuis Eden qui utilisent le champ piece_jointe
            if(!empty($echange->piece_jointe)){

                $fichier = new Element_piece_jointe();
                $fichier->nom = $echange->piece_jointe;
                $fichier->titre = $echange->piece_jointe;
                $fichier->type_element = 'ticket_client';
                $fichier->element_id = $echange->suivi_recette;
                $fichier->chemin = $echange->piece_jointe;

                $ids_pieces_jointes[] = $fichier->id;
            }

            $nouveau_format = false;

            //On traite les pièces jointes provenant des échanges créés par mails
            foreach($pieces_jointes as $piece_jointe){

                //Si on détecte le nouveau format de valeur, on ne traite pas les pjs de cet échange
                if(is_int($piece_jointe)){

                    $nouveau_format = true;
                    continue;
                }

                if(empty($piece_jointe->url_storage) || empty($piece_jointe->nom_original))
                    continue;

                $fichier = new Element_piece_jointe();
                $fichier->nom = $piece_jointe->nom_original;
                $fichier->titre = $piece_jointe->nom_original;
                $fichier->type_element = 'ticket_client';
                $fichier->element_id = $echange->suivi_recette;
                $fichier->chemin = str_replace('public/', '', $piece_jointe->url_storage);

                $fichier->save();

                $ids_pieces_jointes[] = $fichier->id;
            }

            if($nouveau_format === true)
                continue;

            $management_echange->modele = $echange;

            //On enregistre les ids de pjs liées à l'échange
            if(!empty($ids_pieces_jointes))
                $management_echange->enregistre_modele(['pieces_jointes' => json_encode($ids_pieces_jointes)]);
            else
                $management_echange->enregistre_modele(['pieces_jointes' => null]);
        }

        return true;
    }
}

