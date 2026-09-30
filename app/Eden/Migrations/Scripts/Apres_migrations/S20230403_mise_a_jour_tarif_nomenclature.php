<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Composition_article;

class S20230403_mise_a_jour_tarif_nomenclature implements Script
{

    public function execute(){

        $articles = modele('article')
                    ->select('article.*')
                    ->join('composition_article','article.id','composition_article.article_enfant_id')
                    ->whereNotIn('article.id',
                        Composition_article::get()->pluck('article_id')
                    )
                    ->get();

        foreach ($articles as $article){

            management('article', $article->id, $article)->mise_a_jour_prix_parents();

        }
        return true;
    }

}
