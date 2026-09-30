<?php

namespace App\Eden\Managements\Elements;

class Article_recurrent_management extends Element_management {

    public function lien_document($modele){

        return management($modele->type_document,$modele->document_id)->affiche_lien();
    }

}