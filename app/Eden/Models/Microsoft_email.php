<?php

namespace App\Eden\Models;

use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\Message;

class Microsoft_email extends Message {

    public $graph = null;
    public $adresse_email_boite = null;
    public $pieces_jointes = null;
    public $dossier_piece_jointe = null;

    public function getAttachments(){

        if($this->pieces_jointes !== null)
            return $this->pieces_jointes;

        $extensions_bloques = explode(';',fonctionnalite('releve_mail_ticket_client_extension_piece_jointe_bloque'));
        $taille_bloque = intval(fonctionnalite('releve_mail_ticket_client_taille_maximale_piece_jointe_en_mo')) * pow(10,6) ;

        $pieces_jointes = $this->graph->createRequest('GET', '/users/'.$this->adresse_email_boite.'/messages/'.$this->getId().'/attachments')
            ->setReturnType(FileAttachment::class)
            ->execute();

        foreach($pieces_jointes as $index => $piece_jointe){

            $extension = explode('.',$piece_jointe->getName());

            $extension = !isset($extension[1]) ? 'png' : $extension[1];

            if((defined('synchro_mail') && $piece_jointe->getIsInline()) || $piece_jointe->getSize() > $taille_bloque || in_array($extension,$extensions_bloques) || empty($piece_jointe->getContentBytes())) {
                unset($pieces_jointes[$index]);
                continue;
            }

            $contenu = base64_decode($piece_jointe->getContentBytes()->getContents(),true);

            $piece_jointe->localisation_eden = $this->dossier_piece_jointe.'/'.uniqid().'.'.$extension;

            \Storage::put('public/'.$piece_jointe->localisation_eden, $contenu);
        }

        $this->pieces_jointes = $pieces_jointes;

        return $pieces_jointes;
    }

    public function contenu_nettoye(){

        $contenu = $this->getBody()->getContent();

        if(empty($contenu))
            return $contenu;

        foreach($this->getAttachments() as $index_piece_jointe => $piece_jointe){

            if($piece_jointe->getIsInline()) {
                $contenu = str_replace('src="cid:' . $piece_jointe->getContentId() . '"', 'src="/storage/' . $piece_jointe->localisation_eden . '"', $contenu);

                unset($this->pieces_jointes[$index_piece_jointe]);
            }
            else
                $contenu = preg_replace("~href\=\"(.[^']*)?\"\>".str_replace('~','\~',$piece_jointe->getName())."~", '/href="/storage/' . $piece_jointe->localisation_eden . '">'.$piece_jointe->getName().'/', $contenu);
        }

        return $contenu;
    }
}