<?php

namespace App\Eden\Managements\Elements;

class Transfert_inter_entrepot_articles_management extends Element_management {

    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        $this->generation_mouvement_de_stock();

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function generation_mouvement_de_stock(){

        $management_transfert_inter_entrepot = management('transfert_inter_entrepot', $this->modele->transfert_inter_entrepot_id);

        $anciens_mouvements = modele('mouvement_de_stock')
            ->where('type_document', 'transfert_inter_entrepot_articles')
            ->where('document_id', $this->modele->id)
            ->get();

        foreach($anciens_mouvements as $ancien_mouvement){

            management('mouvement_de_stock',$ancien_mouvement->id,$ancien_mouvement)->supprime();
        }

        $this->conditionnements = modele('conditionnement')->get()->pluck('quantite','id')->toArray();

        $article = modele('article')->where('id',$this->modele->article_id)->first();

        $article->quantite = 1;

        $quantites_par_article = $this->quantites_par_article([$article],$this->modele->quantite);

        $conditionnement = null;
        $quantite_conditionnement = 1;

        if($article->type != 1){

            $conditionnement = $this->modele->conditionnement_id;

            if(!empty($this->conditionnements[$conditionnement]))
                $quantite_conditionnement = $this->conditionnements[$conditionnement];
        }

        foreach($quantites_par_article as $article_id => $quantite_article) {

            $management_retrait = management('mouvement_de_stock');

            $mouvement_retrait = array(

                'date' => $management_transfert_inter_entrepot->modele->date,
                'entrepot_id' => $management_transfert_inter_entrepot->modele->entrepot_depart_id,
                'article_id' => $article_id,
                'reserve' => $management_transfert_inter_entrepot->modele->reserve,
                'quantite' => $quantite_article * $quantite_conditionnement * -1,
                'quantite_conditionnement' => !empty($conditionnement) ? $quantite_article : null,
                'conditionnement_id' => $conditionnement,
                'type_document' => 'transfert_inter_entrepot_articles',
                'document_id' => $this->modele->id,
                'type_de_mouvement' => 5,
            );

            $management_retrait->enregistre($mouvement_retrait);

            $management_ajout = management('mouvement_de_stock');

            $mouvement_ajout = array(

                'date' => $management_transfert_inter_entrepot->modele->date,
                'entrepot_id' => $management_transfert_inter_entrepot->modele->entrepot_arrivee_id,
                'article_id' => $article_id,
                'reserve' => $management_transfert_inter_entrepot->modele->reserve,
                'quantite' => $quantite_article * $quantite_conditionnement,
                'quantite_conditionnement' => !empty($conditionnement) ? $quantite_article : null,
                'conditionnement_id' => $conditionnement,
                'type_document' => 'transfert_inter_entrepot_articles',
                'document_id' => $this->modele->id,
                'type_de_mouvement' => 5,
            );

            $management_ajout->enregistre($mouvement_ajout);
        }
    }

    /**
     *
     * Création des mouvements de stocks pour les articles enfants de la production
     *
     */
    public function quantites_par_article($composants,$quantite,$quantites_par_article = []){

        // on sort les composants du stock
        foreach($composants as $composant) {

            if($composant->type_article == 1){

                $quantite_composant = $quantite * $composant->quantite;

                 $articles_nomenclature = modele('composition_article')
                        ->join('article','article_enfant_id', 'article.id')
						->where('composition_article.article_id', $composant->id)
						->select('article.id', 'quantite','composition_article.conditionnement','type_article')
						->get();

                 $quantites_par_article = $this->quantites_par_article($articles_nomenclature,$quantite_composant,$quantites_par_article);

                 continue;
            }

            $quantite_composant = $quantite * $composant->quantite;

            if(!empty($composant->conditionnement) && isset($this->conditionnements[$composant->conditionnement]))
                $quantite_composant *= $this->conditionnements[$composant->conditionnement];

            if(empty($quantites_par_article[$composant->id]))
                $quantites_par_article[$composant->id] = 0;

            $quantites_par_article[$composant->id] += $quantite_composant;
        }

        return $quantites_par_article;
    }
    
    public function supprime($modele = false){

        $anciens_mouvements = modele('mouvement_de_stock')
            ->where('type_document', 'transfert_inter_entrepot_articles')
            ->where('document_id', $this->modele->id)
            ->get();
        
        foreach ($anciens_mouvements as $ancien_mouvement){
            management('mouvement_de_stock', $ancien_mouvement->id, $ancien_mouvement)->supprime();
        }

        return parent::supprime($modele);
        
    }
    
}