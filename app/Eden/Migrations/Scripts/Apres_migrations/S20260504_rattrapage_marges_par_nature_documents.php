<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class S20260504_rattrapage_marges_par_nature_documents implements Script{

    public function execute(){

        $champs_a_rattraper = Champ_libre::where('nom_sql', 'marge_par_nature')->whereIn('type_element', Variables::$documents_gescom)->get();

        foreach($champs_a_rattraper as $champ){

            modele($champ->type_element)->avec_inactifs()->whereNotNull($champ->nom_sql)->chunk(500, function($documents) use ($champ){
                
                foreach($documents as $document){

                    $marges_par_nature = json_decode($document->marge_par_nature, true);
                    
                    if(is_array($marges_par_nature)){

                        $marge_par_nature_rattrapee = [];

                        foreach($marges_par_nature as $marge_par_nature){

                            if(!is_array($marge_par_nature))
                                continue;

                            $marge_par_nature_rattrapee[$marge_par_nature['id']] = $marge_par_nature['marge'];
                        }

                        if(empty($marge_par_nature_rattrapee))
                            continue;

                        $marge_par_nature_rattrapee = json_encode($marge_par_nature_rattrapee);
                        management($champ->type_element, $document->id, $document)->enregistre_modele(['marge_par_nature' => $marge_par_nature_rattrapee]);
                    }
                }
            });
        }
        return true;
    }
}