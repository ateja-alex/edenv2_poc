<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;
use App\Eden\Models\Article_famille;
use App\Eden\Models\Article_declinaison;
use App\Eden\Models\Element_image;
use App\Eden\Models\Article_filtre;

use DB;

class Article_controller extends Fiche_controller {

    /**
	 *
	 * Synchronise les filtres sélectionnés sur les articles
	 *
	 */
    public function enregistre_filtres(Request $formulaire) {

		$article = modele('article')->find($this->id_element);

		$article->filtres()->sync($formulaire->filtres);

		// on supprime tous les filtres de l'article
		DB::table('article_filtre')->where('article_id', $this->id_element)->delete();

		// on enregistre les bons
		if(is_array(request()->filtres)) {

			foreach(request()->filtres as $filtre_id) {

				$filtre = new Article_filtre;

				$filtre->article_id = $this->id_element;
				$filtre->filtre_id = $filtre_id;

				$filtre->save();
			}
		}

		return response()->json(true);
	}

	/**
	 *
	 *
	 * Retourne la liste des familles pour un article
	 *
	 */
	public function articles_par_familles($type_element, $id_element) {

		$articles_par_familles = Article_famille::where('article_id', $id_element)->join('famille', 'famille.id', 'famille_id')->select('article_famille.*', 'famille.nom')->get();

		foreach ($articles_par_familles as $clef => &$article_par_famille) {

			$article_par_famille->famille = ['nom' => $article_par_famille->nom];
		}

		return $articles_par_familles;
	}

	/**
	 *
	 * Supprime une famille pour un article
	 *
	 */
	public function supprimer_article_par_famille($formulaire, $type_element, $id_element) {

		$tarif = Article_famille::find($formulaire->id);

		$tarif->delete();

		return response()->json([

			'retour' => true,
		]);
	}

	/**
	 *
	 * Enregistre les catégories d'un article
	 *
	 */
	public function enregistrer_article_par_famille($formulaire, $type_element, $id_element) {

		$article_famille = new Article_famille;

		if(!empty($formulaire->id))
			$article_famille = Article_famille::find($formulaire->id);

		$article_famille->article_id = $formulaire->article_id;
		$article_famille->famille_id = $formulaire->famille_id;
		$article_famille->save();

		return response()->json([

			'retour' => true,
		]);

	}


	/**
	 *
	 * Retourne la liste articles qui composent le pack
	 *
	 */
	public function articles_contenu_pack($type_element, $id_element) {

		$tarifs = modele('article_contenu_pack')->where('article_id', $id_element)->orderBy('ordre')->get();

		$modeles_articles = modele('article')->whereIn('id', $tarifs->pluck('article_enfant_id'))->get()->keyBy('id');

		foreach($tarifs as $tarif) {

			if(empty($modeles_articles[$tarif->article_enfant_id]))
				continue;

			$tarif->article_enfant_id_affichage = management('article', $tarif->article_enfant_id, $modeles_articles[$tarif->article_enfant_id])->affiche();
		}

		return $tarifs;
	}

	/**
	 *
	 * Copier le pack d'un article
	 *
	 */
	public function copier_article_contenu_pack($formulaire, $type_element, $id_element) {

		// on supprime le pack actuel
		modele('article_contenu_pack')->where('article_id', $id_element)->delete();

		// on recupère le pack qu'on doit copier
		$contenu_pack_a_copier = modele('article_contenu_pack')->where('article_id', $formulaire->id_article_copier)->get();

		// on copie le pack
		foreach($contenu_pack_a_copier as $pack_a_copier) {

            management('article_contenu_pack')->enregistre(array(
                'article_id' => $id_element,
                'article_enfant_id' => $pack_a_copier->article_enfant_id,
                'quantite' => $pack_a_copier->quantite,
                'ordre' => $pack_a_copier->ordre,
            ));
		}

		return response()->json([

			'retour' => true,
		]);
	}

    /**
	 *
	 * Copier la composition d'un article
	 *
	 */
	public function copier_article_composants($formulaire, $type_element, $id_element) {

		// on supprime les compositions
		modele('composition_article')->where('article_id', $id_element)->delete();

		// on recupère la composition qu'on doit copier
		$compositions_article_a_copier = modele('composition_article')->where('article_id', $formulaire->id_article_copier)->get();

		// on copie le
		foreach($compositions_article_a_copier as $composition_article_a_copier) {

            if($composition_article_a_copier->article_enfant_id == $id_element)
                continue;

            management('composition_article')->enregistre(array(
                'article_id' => $id_element,
                'article_enfant_id' => $composition_article_a_copier->article_enfant_id,
                'quantite' => $composition_article_a_copier->quantite,
                'tarif' => $composition_article_a_copier->tarif,
                'prix_achat' => $composition_article_a_copier->prix_achat,
                'conditionnement' => $composition_article_a_copier->conditionnement,
            ));
		}

		return response()->json([

			'retour' => true,
		]);
	}

	/**
	 *
	 * Retourne la liste des déclinaisons
	 *
	 */
	public function articles_declinaisons($type_element, $id_element) {

		$articles = Article_declinaison::where('article_id', $id_element)->get();

		return $articles;
	}

	/**
	 *
	 * Supprime une déclinaison
	 *
	 */
	public function supprimer_article_declinaison($formulaire, $type_element, $id_element) {

		$tarif = Article_declinaison::find($formulaire->id);

		$tarif->delete();

		return response()->json([

			'retour' => true,
		]);
	}

	/**
	 *
	 * Enregistre une déclinaison
	 *
	 */
	public function enregistrer_article_declinaison($formulaire, $type_element, $id_element) {

		$tarif = new Article_declinaison;

		if(!empty($formulaire->id))
			$tarif = Article_declinaison::find($formulaire->id);


		$tarif->article_id = $formulaire->article_id;
		$tarif->designation = $formulaire->designation;
		$tarif->tarif = $formulaire->tarif;
		$tarif->code_article = $formulaire->code_article;
		$tarif->cout_de_revient = $formulaire->cout_de_revient;
		$tarif->reference = $formulaire->reference;
		$tarif->image = $formulaire->image;
		$tarif->stock = $formulaire->stock;
		$tarif->article_declinaison_id = $formulaire->article_declinaison_id;

		$tarif->valeur_declinaison_1 = $formulaire->valeur_declinaison_1;
		$tarif->valeur_declinaison_2 = $formulaire->valeur_declinaison_2;
		$tarif->valeur_declinaison_3 = $formulaire->valeur_declinaison_3;
		$tarif->valeur_declinaison_4 = $formulaire->valeur_declinaison_4;
		$tarif->valeur_declinaison_5 = $formulaire->valeur_declinaison_5;


		if($formulaire->famille_declinaison_1 !== null)
			$tarif->famille_declinaison_1 = $formulaire->famille_declinaison_1;

		if($formulaire->famille_declinaison_2 !== null)
			$tarif->famille_declinaison_2 = $formulaire->famille_declinaison_2;

		if($formulaire->famille_declinaison_3 !== null)
			$tarif->famille_declinaison_3 = $formulaire->famille_declinaison_3;

		if($formulaire->famille_declinaison_4 !== null)
			$tarif->famille_declinaison_4 = $formulaire->famille_declinaison_4;

		if($formulaire->famille_declinaison_5 !== null)
			$tarif->famille_declinaison_5 = $formulaire->famille_declinaison_5;

		// si pas de valeur, on annule la famille de déclinaison
		for($i=1; $i<=5; $i++) {

			if(empty($formulaire->{'valeur_declinaison_'.$i})) {

				$tarif->{'valeur_declinaison_'.$i} = null;
				$tarif->{'famille_declinaison_'.$i} = null;
			}
		}


		$tarif->save();

		return response()->json([

			'retour' => true,
		]);

	}

    /**
    *
    * Gestion des images de tri
    *
    */
    public function tri_image(Request $request) {

        // on récupère l'article
        $article = modele('article')->find($this->id_element);

        // on récupère les images
        $images = $request->ordre_images;

		foreach($images as $key => $image) {

            $ordre = $key + 1;
            $element_image = Element_image::find($image)->update(['ordre' => $ordre]);
        }

        return response()->json([$element_image]);
	}

	/**
	 *
	 * Retourne l'historique des prix d'achats
	 *
	 */
	public function historique_prix_achat($type_element, $id_element) {

		$historique = fiche('article', $id_element)->historique_prix_d_achat_de_l_article();

		foreach($historique as $h) {
			$h->fournisseur = modele('fournisseur', $h->fournisseur_id_ligne)->nom;
			$h->date 		= formate_date('d/m/Y', $h->date);
			$h->montant 	= montant($h->tarif);
			$h->ref_facture	= modele('facture_achat', $h->id)->reference_document;
		}

		return $historique;
	}

	/**
	 *
	 * Retourne le html du champ demandé
	 *
	 */
	public function cree_champ($formulaire,$type_element,$id){

		$html = management($type_element,$id)->champ($formulaire->champ)->cree();

		return response()->json(['html' => $html]);

	}

    public function recupere_indicateur_stock_actuel($type_element, $id_element){

        $article_management = management($type_element, $id_element);

        $detail_stock_actuel = service('stocks')->details_stocks_par_article($id_element)[$id_element]['stock_actuel'];
        $stock_actuel = $detail_stock_actuel['total'];

        $seuil = modele('seuil_article')->where('article_id', $id_element)->zero_ou_null('conditionnement_id')->first();

        $conditionnements = modele('conditionnement')->where('article_id', $id_element)->get()->keyBy('id');

        $seuil_par_conditionnement = modele('seuil_article')
            ->where('article_id', $id_element)
            ->get()->keyBy('conditionnement_id');

        $stock_actuel_par_conditionnement = [];

        foreach ($detail_stock_actuel['par_conditionnement'] as $conditionnement_id => $stock_actuel_tmp){

            if($conditionnement_id !== 0) {
                $stock_actuel_par_conditionnement[$conditionnement_id]['nom'] = $conditionnements[$conditionnement_id]['nom'] . " (" . management('conditionnement')->champ('quantite')->affiche($conditionnements[$conditionnement_id]['quantite']) . " " . $article_management->champ('unite')->affiche() .")";

                if(!empty($seuil_par_conditionnement[$conditionnement_id]))
                    $stock_actuel_par_conditionnement[$conditionnement_id]['seuils'] = $seuil_par_conditionnement[$conditionnement_id];
                else {
                    $stock_actuel_par_conditionnement[$conditionnement_id]['seuils'] = [
                        'seuil_mini' => 0,
                        'seuil_alerte' => 0,
                    ];
                }

            }
            else {
                $stock_actuel_par_conditionnement[$conditionnement_id]['nom'] = traduction('messages.php.article.piece');

                if(!empty($seuil))
                    $stock_actuel_par_conditionnement[$conditionnement_id]['seuils'] = $seuil;
                else {
                    $stock_actuel_par_conditionnement[$conditionnement_id]['seuils'] = [
                        'seuil_mini' => 0,
                        'seuil_alerte' => 0,
                    ];
                }

            }

            $stock_actuel_par_conditionnement[$conditionnement_id]['stock_actuel'] = $stock_actuel_tmp['total'];

        }

        if(!empty($seuil))
            return response()->json(['stock_actuel' => $stock_actuel, 'seuil_minimum' => $seuil->seuil_mini, 'seuil_alerte' => $seuil->seuil_alerte, 'stock_actuel_par_conditionnement' => $stock_actuel_par_conditionnement]);

        return response()->json(['stock_actuel' => $stock_actuel, 'seuil_minimum' => 0, 'seuil_alerte' => 0, 'stock_actuel_par_conditionnement' => $stock_actuel_par_conditionnement]);

    }

}
