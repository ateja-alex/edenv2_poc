<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Article_controller extends Controller {

    /**
     * 
     * On affiche une page produit
     * 
     * @return Response
     */
    public function affiche($article) {
		
        $article_management = management('article', $article->id);
		
		// on va chercher les données dans le management qui peut être surchargé si nécessaire
		$donnees = $article_management->charge_donnees_pour_commerce();
        
        // On génère le fil d'ariane
        $donnees['ariane'] = management('famille')->fil_ariane (
                                        management('famille')->familles_a_afficher(),
                                        management('famille')->sous_familles_a_afficher()


                                    ) ;


        $donnees['declinaisons_mulitples'] = [];
        $donnees['article_declinaison'] = [];

        // si c'est un article avec une déclinaison multiple 
        if($article->type_declinaison == 2) {

            $article_declinaison = modele('article_declinaison')->where('article_id', $article->id)->get();


            foreach($article_declinaison as $declinaison) {

                if($declinaison->famille_declinaison_1 != null) {

                    $nom_declinaison = modele('famille_declinaison', $declinaison->famille_declinaison_1)->nom;
                    
                    if(!isset($donnees['declinaisons_mulitples'][$nom_declinaison])) {
                        $donnees['declinaisons_mulitples'][$nom_declinaison] = [];
                    }

                    if(!in_array($declinaison->valeur_declinaison_1, $donnees['declinaisons_mulitples'][$nom_declinaison])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison][] = $declinaison->valeur_declinaison_1;
                        $declinaison->famille_nom_declinaison_1 =  $nom_declinaison;
    
                    }
                }

                if($declinaison->famille_declinaison_2 != null) {

                    $nom_declinaison_2 = modele('famille_declinaison', $declinaison->famille_declinaison_2)->nom;

                    if(!isset($donnees['declinaisons_mulitples'][$nom_declinaison_2])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_2] = [];
                    }

                    if(!in_array($declinaison->valeur_declinaison_2, $donnees['declinaisons_mulitples'][$nom_declinaison_2])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_2][] = $declinaison->valeur_declinaison_2;
                        $declinaison->famille_nom_declinaison_2 =  $nom_declinaison_2;
    
                    }

                }

                if($declinaison->famille_declinaison_3 != null) {


                    $nom_declinaison_3 = modele('famille_declinaison', $declinaison->famille_declinaison_3)->nom;

                    if(!isset($donnees['declinaisons_mulitples'][$nom_declinaison_3])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_3] = [];
                    }

                    if(!in_array($declinaison->valeur_declinaison_3, $donnees['declinaisons_mulitples'][$nom_declinaison_3])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_3][] = $declinaison->valeur_declinaison_3;
                        $declinaison->famille_nom_declinaison_3 =  $nom_declinaison_3;
                    }

                }

                if($declinaison->famille_declinaison_4 != null) {


                    $nom_declinaison_4 = modele('famille_declinaison', $declinaison->famille_declinaison_4)->nom;

                    if(!isset($donnees['declinaisons_mulitples'][$nom_declinaison_4])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_4] = [];
                    }

                    if(!in_array($declinaison->valeur_declinaison_4, $donnees['declinaisons_mulitples'][$nom_declinaison_4])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_4][] = $declinaison->valeur_declinaison_4;
                        $declinaison->famille_nom_declinaison_4 =  $nom_declinaison_4;
                    }

                }

                if($declinaison->famille_declinaison_5 != null) {


                    $nom_declinaison_5 = modele('famille_declinaison', $declinaison->famille_declinaison_5)->nom;

                    if(!isset($donnees['declinaisons_mulitples'][$nom_declinaison_5])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_5] = [];
                    }

                    if(!in_array($declinaison->valeur_declinaison_5, $donnees['declinaisons_mulitples'][$nom_declinaison_5])) {

                        $donnees['declinaisons_mulitples'][$nom_declinaison_5][] = $declinaison->valeur_declinaison_5;
                        $declinaison->famille_nom_declinaison_5 =  $nom_declinaison_5;
                    }

                }
            }

            $donnees['article_declinaison'] = $article_declinaison;
        }

		// On affiche la page de l'article
        return view('eden::ecommerce.article', $donnees);
    }

    public function retourne_prix_en_fonction_declinaison(Request $formulaire) {

        $declinaisons_mulitple = $formulaire->article['declinaison_mulitple'];
        $article_declinaison = $formulaire->article_declinaison;

        $where_parametres = [];
        
        foreach($declinaisons_mulitple as $index => $declinaison) {

            $index = ($index + 1);
            $where_parametres['valeur_declinaison_'.$index] =  $declinaison;
        }

        $where_parametres['article_id'] = $formulaire->article_id;

        $retour = modele('article_declinaison')->where($where_parametres)->first();

        return response()->json(['succes' => true, 'retour' => $retour]);
        
    }

   
}
