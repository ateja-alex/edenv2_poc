<?php

namespace App\Eden\Managements\Services;



class Document_service {

    /**
     * 
     * Retourne le taux de TVA à utiliser pour une catégorie comptablet et un article donnés
     * 
     */
    public function recupere_taux_tva_pour_article_et_categorie_comptable($article, $categorie_comptable_id, $type = 'vente') {
		
        if(empty($categorie_comptable_id) || empty($article))
			return false;

        $parametrage = management('article', $article->id, $article)->recupere_categorie_comptable($categorie_comptable_id);

        if (empty($parametrage))
            return false;

        return ['taux' => $this->recupere_taux_article_categorie_comptable($parametrage,$type), 'article_categorie_comptable' => $parametrage];
    }

    public function recupere_taux_article_categorie_comptable($article_categorie_comptable,$type){

        if(($type == 'vente' && $article_categorie_comptable->code_tva_id == -1) ||
            ($type == 'achat' && $article_categorie_comptable->code_tva_achat_id == -1))
            return 0;

        $parametrage_tva = modele('code_tva',
            $type == 'vente' ? $article_categorie_comptable->code_tva_id :
                $article_categorie_comptable->code_tva_achat_id);

        if($parametrage_tva->sens == 2)
            return 0;

        return $parametrage_tva->taux;
    }
	
    /**
     *
     * Ajout de restriction de certains blocs pour des types éléments
     *
     */
    public function compatibilites_utilisations_blocs(){

        return array(
            'paiement' => array('facture_vente', 'facture_achat',
                'avoir_vente', 'avoir_achat',
                'acompte_vente', 'acompte_achat',
                'devis_vente', 'commande_vente'),
            'echeances' => array('facture_vente', 'commande_vente', 'devis_vente'),
        );
    }

    /**
     *
     * Défini si un module est obligatoire ou disponible qu'en modification
     *
     */
    public function disponibilites_modules(){

        $disponibilites_modules = array(
            'obligatoire' => array(
                'saisie_des_articles'
            ),
            'modification' => array(
                'paiement',
                'echeances',
                'recurrence',
                'documents_lies',
                'historique',
                'commentaire_fiche',
                'gescom_pj_sur_document',
                'gescom_commentaires_sur_document',
                'messages'
            ),
            'validation' => array(
                'mise_a_jour_prix'
            )
        );

        $disponibilites_modules_tries = array();

        foreach($disponibilites_modules as $restriction => $blocs){
            foreach($blocs as $bloc){
                $disponibilites_modules_tries[$bloc] = $restriction;
            }
        }

        return $disponibilites_modules_tries;
    }
    
}