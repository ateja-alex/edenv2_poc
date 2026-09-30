<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne_divers_document extends Model {

    protected $table = 'ligne_divers_document';
	
    public $timestamps = false;

    protected  $appends = ['commentaire_wysiwyg','note_interne_wysiwyg'];


    public function getCommentaireWysiwygAttribute() {

        if(fonctionnalite('commentaires_wysiwyg_documents') === true) { 

            return $this->contenu;
        }

    }

    public function getNoteInterneWysiwygAttribute() {

        if(fonctionnalite('notes_internes_wysiwyg_documents') === true) { 

            return $this->contenu;
        }

    }
}
