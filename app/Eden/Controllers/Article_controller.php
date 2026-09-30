<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

use App\Eden\Models\Champs_liste_formatee;
use Illuminate\Http\Request;


class Article_controller extends Controller {

/**
	 *
	 * Affiche les articles trié par rapport
	 *
	 */
	public function index_articles(){

		$listes = array();

		$modele_article_classique = Champs_liste_formatee::where('id_valeur',0)->where('id_liste_choix',62)->first();
		$modele_article_nomenclature = Champs_liste_formatee::where('id_valeur',1)->where('id_liste_choix',62)->first();
		$modele_article_fdp = Champs_liste_formatee::where('id_valeur',2)->where('id_liste_choix',62)->first();
		$modele_article_assemble = Champs_liste_formatee::where('id_valeur',3)->where('id_liste_choix',62)->first();

		// Note : on utilise le service page_adv mais cela ne sert qu'a récupérer le rapport
		$listes['articles_classique'] = service('page_adv')->informations_pour_liste('article', 'articles_classique');
		$listes['articles_nomenclature'] = service('page_adv')->informations_pour_liste('article', 'articles_nomenclature');
		$listes['articles_fdp'] = service('page_adv')->informations_pour_liste('article', 'articles_fdp');
		$listes['articles_assemble'] = service('page_adv')->informations_pour_liste('article', 'articles_assemble');

		if($modele_article_classique != null && $modele_article_classique->desactivee === 1)
			$listes['articles_classique'] = false;

		if($modele_article_nomenclature != null && $modele_article_nomenclature->desactivee === 1)
			$listes['articles_nomenclature'] = false;

		if($modele_article_fdp != null && $modele_article_fdp->desactivee === 1)
			$listes['articles_fdp'] = false;

		if($modele_article_assemble != null && $modele_article_assemble->desactivee === 1)
			$listes['articles_assemble'] = false;

		return view('eden::articles', array('listes' => $listes));
	}

	/**
	 *
	 * On recharge les données du rapport voulu
	 *
	 */
    public function recuperer_rapport_article($nom_liste) {

		$rapport = array();

		// Note : on utilise le service page_adv mais cela ne sert qu'a récupérer le rapport
		if ($nom_liste == "articles_classique")
			$rapport = service('page_adv')->informations_pour_liste('article', 'articles_classique');

		if ($nom_liste == "articles_fdp")
			$rapport = service('page_adv')->informations_pour_liste('article', 'articles_fdp');

		if ($nom_liste == "articles_nomenclature")
			$rapport = service('page_adv')->informations_pour_liste('article', 'articles_nomenclature');

		if ($nom_liste == "articles_assemble")
			$rapport = service('page_adv')->informations_pour_liste('article', 'articles_assemble');

		return response()->json(['rapport' => $rapport]);
    }

    public function creer_conditionnement_en_masse(Request $donnees){

        $ids_elements = $donnees->post('ids');
        $conditionnement = $donnees->post('conditionnement');

        $articles = modele('article_fournisseur')->whereIn('id', $ids_elements)->whereNotNull('article_id')->get()->pluck('article_id');

        $erreurs = [];

        foreach ($articles as $article){

            $conditionnement_meme_quantite = modele('conditionnement')->where('article_id', $article)->where('quantite', $conditionnement['quantite'])->first();

            if(!empty($conditionnement_meme_quantite)){
                $erreurs[] = traduction('message.php.article.conditionnement_deja_existant', null, [$article]);
                continue;
            }

            $conditionnement_article = $conditionnement;

            $conditionnement_article['article_id'] = $article;

            $management_conditionnement = management('conditionnement');

            $retour_enregistrement = $management_conditionnement->enregistre($conditionnement_article);

            if($retour_enregistrement !== true)
                return response()->json(['retour' => false, 'message' => $retour_enregistrement]);

        }

        if(count($erreurs) > 0)
            return response()->json(['retour' => false, 'message' => implode("\n", $erreurs)]);

        return response()->json(['retour' => true]);

    }

    /**
	 *
	 * Copier les catégories comptables d'un article
	 *
	 */
	public function copier_categories_comptables_article(Request $formulaire) {

        $ids_articles = $formulaire->ids_articles;

		if(in_array($formulaire->id_article_a_copier,$ids_articles))
			unset($ids_articles[array_search($formulaire->id_article_a_copier,$ids_articles)]);

		// on supprime les catégories actuelles
		modele('article_categorie_comptable')->whereIn('article_id', $ids_articles)->delete();

		// on récupère les catégories qu'on doit copier
		$categories_comptables = modele('article_categorie_comptable')->where('article_id', $formulaire->id_article_a_copier)->get();

		// on copie chaque catégorie
		foreach($categories_comptables as $categorie_comptable) {

            foreach($ids_articles as $id_article) {

                management('article_categorie_comptable')->enregistre(array(
                    'article_id' => $id_article,
                    'compte_charge' => $categorie_comptable->compte_charge,
                    'compte_produit' => $categorie_comptable->compte_produit,
                    'code_tva_id' => $categorie_comptable->code_tva_id,
                    'code_tva_achat_id' => $categorie_comptable->code_tva_achat_id,
                    'categorie_comptable_id' => $categorie_comptable->categorie_comptable_id,
                ));
            }
		}

		return response()->json(['retour' => true,]);
	}

    /**
	 *
	 * On récupère tous les conditionnements regroupé par article_id
	 *
	 */
	public function recupere_conditionnement(){

		$conditionnements_par_article = modele('conditionnement')->get()->groupBy('article_id')->toArray();

        $articles = modele('article')->whereIn('id',array_keys($conditionnements_par_article))->get()->pluck('article_unite','id')->toArray();

        $unites = modele('article_unite')->whereIn('id', $articles)->get()->pluck('nom','id')->toArray();

		foreach($conditionnements_par_article as $id => &$conditionnements){

            if(!array_key_exists($id,$articles))
                continue;

            $unite = $articles[$id];

			foreach($conditionnements as &$conditionnement){

                if(isset($unites[$unite]))
                    $conditionnement['nom_unite'] = $unites[$unite];
                else
                    $conditionnement['nom_unite'] = 'unités';

			}

		}

		return response()->json($conditionnements_par_article);

	}

    /**
     *
     * Retourne la valeur d'éco-contribution actuel d'un article
     *
     */
    public function eco_contribution($article_id){

        $eco_contribution = management('article',$article_id)->eco_contribution();

        return response()->json(array('eco_contribution' => $eco_contribution));
    }
}
