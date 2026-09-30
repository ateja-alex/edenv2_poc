<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250310_reprise_reduction_commerciale implements Script{

    public function execute(){

        /* Reprise de données des tables
            reduction_commerciale,
            article_tarif_par_palier,
            article_client
        */

        $reductions_commerciales = DB::table('reduction_commerciale')->get();

        foreach($reductions_commerciales as $reduction_commerciale){

            management('condition_commerciale')->enregistre([
                'client_id' => $reduction_commerciale->client_id,
                'famille_id' => $reduction_commerciale->famille_id,
                'article_id' => $reduction_commerciale->article_id,
                'tarif' => $reduction_commerciale->type_reduction == 'prix_net' ? $reduction_commerciale->reduction : null,
                'remise' => $reduction_commerciale->type_reduction == 'pourcentage' ? $reduction_commerciale->reduction : null,
            ]);
        }

        $articles_client = DB::table('article_client')->get();

        foreach($articles_client as $article_client){

            management('condition_commerciale')->enregistre([
                'client_id' => $article_client->client_id,
                'article_id' => $article_client->article_id,
                'tarif' => $article_client->tarif,
                'palier_quantite' => empty($article_client->quantite) || $article_client->quantite == 0 ? null : $article_client->quantite,
            ]);
        }

        $article_tarif_par_palier = DB::table('article_tarif_par_palier')->get();

        foreach($article_tarif_par_palier as $article){

            management('condition_commerciale')->enregistre([
                'article_id' => $article->article_id,
                'tarif' => $article->tarif,
                'palier_quantite' => empty($article->quantite) || $article->quantite == 0 ? null : $article->quantite,
            ]);
        }

        $fichier = storage_path('app/eden_fiche_client.php');

        if(is_file($fichier)) {

            $contenu_tmp = file_get_contents($fichier);

            $contenu_tmp = str_replace(['client_reductions_commerciales', 'tarifs_par_client'], 'fiche_client_condition_commerciale', $contenu_tmp);

            file_put_contents($fichier, $contenu_tmp);
        }

        $fichier = storage_path('app/eden_fiche_article.php');

        if(is_file($fichier)) {

            $contenu_tmp = file_get_contents($fichier);

            $contenu_tmp = str_replace('article_tarifs_par_palier', 'fiche_article_article_conditions_commerciales', $contenu_tmp);

            file_put_contents($fichier, $contenu_tmp);
        }

        return true;
    }
}