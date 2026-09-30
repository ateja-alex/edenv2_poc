<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use DB;

class Article_categorie_comptable_controller extends Controller {

    public function verification_impacts_documents($article_categorie_comptable_id){

        $informations = request()->all();

        $article_categorie_comptable = modele('article_categorie_comptable',$article_categorie_comptable_id);

        $changements = array_filter($informations, function ($valeur, $colonne) use ($article_categorie_comptable) {
            return $article_categorie_comptable->{$colonne} != $valeur;
        }, ARRAY_FILTER_USE_BOTH);

        if(!array_key_exists('code_tva_id',$changements) && !array_key_exists('code_tva_achat_id',$changements))
            return response()->json(false);

        //On va récupérer les documents concernés par le changement
        $requete = "";
        
        if(array_key_exists('code_tva_id',$changements))
            $requete .= "SELECT fv.id,fv.reference_document,montant_document_ht,montant_document_ttc,'facture_vente' as type_element
                FROM facture_vente fv
                JOIN facture_vente_lignes fvl ON fvl.document_id = fv.id
                JOIN client c ON fv.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = fvl.article_id AND acc.categorie_comptable_id = COALESCE(fv.categorie_comptable_id,c.categorie_comptable_id)
                WHERE COALESCE(fvl.categorie_comptable_article_id,acc.id,0) = $article_categorie_comptable_id
                AND COALESCE(fv.comptabilise,0) = 0
                GROUP BY fv.id
                UNION 
                SELECT av.id,av.reference_document,montant_document_ht,montant_document_ttc,'avoir_vente' as type_element
                FROM avoir_vente av
                JOIN avoir_vente_lignes avl ON avl.document_id = av.id
                JOIN client c ON av.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = avl.article_id AND acc.categorie_comptable_id = COALESCE(av.categorie_comptable_id,c.categorie_comptable_id,0)
                WHERE COALESCE(avl.categorie_comptable_article_id,acc.id,0) = $article_categorie_comptable_id
                AND COALESCE(av.comptabilise,0) = 0
                GROUP BY av.id";

        if(array_key_exists('code_tva_achat_id',$changements))
            $requete .= (!empty($requete) ? ' UNION ': '')."SELECT fa.id,fa.reference_document,montant_document_ht,montant_document_ttc,'facture_achat' as type_element
                FROM facture_achat fa
                JOIN facture_achat_lignes fal ON fal.document_id = fa.id
                JOIN client c ON fa.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = fal.article_id AND acc.categorie_comptable_id = COALESCE(fa.categorie_comptable_id,c.categorie_comptable_id)
                WHERE COALESCE(fal.categorie_comptable_article_id,acc.id,0) = $article_categorie_comptable_id
                AND COALESCE(fa.comptabilise,0) = 0
                GROUP BY fa.id
                UNION 
                SELECT aa.id,aa.reference_document,montant_document_ht,montant_document_ttc,'avoir_achat' as type_element
                FROM avoir_achat aa
                JOIN avoir_achat_lignes aal ON aal.document_id = aa.id
                JOIN client c ON aa.client_id = c.id 
                LEFT JOIN article_categorie_comptable acc ON 
                acc.article_id = aal.article_id AND acc.categorie_comptable_id = COALESCE(aa.categorie_comptable_id,c.categorie_comptable_id,0)
                WHERE COALESCE(aal.categorie_comptable_article_id,acc.id,0) = $article_categorie_comptable_id
                AND COALESCE(aa.comptabilise,0) = 0
                GROUP BY aa.id";

        $documents = DB::select($requete);

        return response()->json($documents);
    }

}