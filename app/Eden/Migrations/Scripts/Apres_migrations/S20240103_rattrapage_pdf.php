<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use File;
use Illuminate\Support\Facades\DB;

class S20240103_rattrapage_pdf implements Script
{

    public function execute()
    {

        $documents_gescom = Variables::$documents_gescom;

        foreach($documents_gescom as $document){

            $ids_documents = [];

            $dossier = storage_path('app/public/'.$document);

            if(!is_dir($dossier))
                continue;

            $repertoire = scandir($dossier);

            foreach($repertoire as $fichier) {

                if(in_array($fichier, array('.', '..')) || !str_contains($fichier,traduction('tables_libres.'.$document.'.element').'_'))
                    continue;

                $id = str_replace([traduction('tables_libres.'.$document.'.element').'_','.pdf'],'',$fichier);

                if(intval($id) == $id)
                    $ids_documents[] = $id;
            }

            $elements = modele($document)->whereIn('id',$ids_documents)->get();

            foreach($elements as $element){

                management($document,$element->id,$element)->recupere_chemin_pdf(true);
            }
        }

        DB::select('UPDATE versionning_document SET inactif = 1 WHERE url_document LIKE "public/%"');

        return true;
    }
}