<?php

namespace App\Eden\Managements\Elements;

use DB;

class Article_categorie_comptable_management extends Element_management {

	/**
	 *
	 * On vérifie l'unicité de la combinaison famille/article avec categorie_comptable_id
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        $categorie_comptable_id = $modifications['categorie_comptable_id'] ?? $this->modele->categorie_comptable_id ?? null;
        $famille_id = $modifications['famille_id'] ?? $this->modele->famille_id ?? null;
        $article_id = $modifications['article_id'] ?? $this->modele->article_id ?? null;
        $eco_contribution = $modifications['eco_contribution'] ?? $this->modele->eco_contribution ?? 0;

        if(empty($categorie_comptable_id))
            return traduction('messages.php.article_categorie_comptable.enregistrement_impossible');

        $article_categorie_comptable = modele('article_categorie_comptable')
            ->where('categorie_comptable_id', $categorie_comptable_id);

        if(!empty($famille_id))
            $article_categorie_comptable->where('famille_id', $famille_id);

        if(!empty($article_id))
            $article_categorie_comptable->where('article_id', $article_id);

        if(!empty($this->modele->id))
            $article_categorie_comptable->where('id','!=', $this->modele->id);

        if ($eco_contribution) {
            $article_categorie_comptable->where('eco_contribution', 1);
        } else {
            $article_categorie_comptable->where(function ($query) {
                $query->where('eco_contribution', 0)
                    ->orWhereNull('eco_contribution');
            });
        }

        if(!empty($article_categorie_comptable->first()))
            return traduction('messages.php.article_categorie_comptable.enregistrement_impossible');

        return parent::enregistre($modifications, $modele);
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if(!empty($modele_avant) && ($modele_avant->code_tva_id != $this->modele->code_tva_id
            || $modele_avant->code_tva_achat_id != $this->modele->code_tva_achat_id)){

            $requete = "";

            $taux_vente = modele('code_tva',$this->modele->code_tva_id)->taux ?? 0;

            $taux_achat = modele('code_tva',$this->modele->code_tva_achat_id)->taux ?? 0;

            if($modele_avant->code_tva_id != $this->modele->code_tva_id)
                $requete.= "
                    SELECT fvl.id,'facture_vente_lignes' as type_ligne
                    FROM facture_vente fv
                    JOIN facture_vente_lignes fvl ON fvl.document_id = fv.id
                    JOIN client c ON fv.client_id = c.id 
                    LEFT JOIN article_categorie_comptable acc ON 
                    acc.article_id = fvl.article_id AND acc.categorie_comptable_id = COALESCE(fv.categorie_comptable_id,c.categorie_comptable_id)
                    WHERE COALESCE(fvl.categorie_comptable_article_id,acc.id,0) = {$this->modele->id}
                    AND COALESCE(fv.comptabilise,0) = 0
                    AND COALESCE(fvl.tva,0) != $taux_vente
                    UNION 
                    SELECT avl.id,'avoir_vente_lignes' as type_ligne
                    FROM avoir_vente av
                    JOIN avoir_vente_lignes avl ON avl.document_id = av.id
                    JOIN client c ON av.client_id = c.id 
                    LEFT JOIN article_categorie_comptable acc ON 
                    acc.article_id = avl.article_id AND acc.categorie_comptable_id = COALESCE(av.categorie_comptable_id,c.categorie_comptable_id,0)
                    WHERE COALESCE(avl.categorie_comptable_article_id,acc.id,0) = {$this->modele->id}
                    AND COALESCE(av.comptabilise,0) = 0
                    AND COALESCE(avl.tva,0) != $taux_vente
                    GROUP BY av.id
                ";

            if($modele_avant->code_tva_achat_id != $this->modele->code_tva_achat_id)
                $requete .= (!empty($requete) ? ' UNION ': '')."SELECT fal.id,'facture_achat_lignes' as type_ligne
                FROM facture_achat fa
                JOIN facture_achat_lignes fal ON fal.document_id = fa.id
                JOIN client c ON fa.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = fal.article_id AND acc.categorie_comptable_id = COALESCE(fa.categorie_comptable_id,c.categorie_comptable_id)
                WHERE COALESCE(fal.categorie_comptable_article_id,acc.id,0) = {$this->modele->id}
                AND COALESCE(fa.comptabilise,0) = 0
                AND COALESCE(fal.tva,0) != $taux_achat
                GROUP BY fa.id
                UNION 
                SELECT aal.id,'avoir_achat_lignes' as type_ligne
                FROM avoir_achat aa
                JOIN avoir_achat_lignes aal ON aal.document_id = aa.id
                JOIN client c ON aa.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = aal.article_id AND acc.categorie_comptable_id = COALESCE(aa.categorie_comptable_id,c.categorie_comptable_id,0)
                WHERE COALESCE(aal.categorie_comptable_article_id,acc.id,0) = {$this->modele->id}
                AND COALESCE(aa.comptabilise,0) = 0
                AND COALESCE(aal.tva,0) != $taux_achat
                GROUP BY aa.id";

            $lignes_a_modifier_par_type = collect(DB::select($requete))
                ->groupBy('type_ligne')->map(function($element){
                    return $element->pluck('id');
                });

            foreach($lignes_a_modifier_par_type as $type_element => $ids){

                $lignes_par_document = modele($type_element)->whereIn('id',$ids)->get();

                $type_element_document = str_replace('_lignes','',$type_element);

                $documents = modele($type_element_document)
                    ->whereIn('id',array_unique($lignes_par_document->pluck('document_id')->toArray()))
                    ->get();

                $lignes_par_document = $lignes_par_document->groupBy('document_id');

                foreach($documents as $document){

                    $lignes = $lignes_par_document[$document->id];

                    foreach($lignes as $ligne){
                        management($type_element,$ligne->id,$ligne)->enregistre([
                            'tva' => str_contains($type_element_document,'vente') ? $taux_vente : $taux_achat
                        ]);
                    }

                    management($type_element_document,$document->id,$document)->maj_total_document(true);
                }
            }
        }
    }
}