<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;
use DB;
use Log;

/**
* Gestion des rapports
*/
class Gestion_des_stocks_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management('gestion_des_stocks', "Gestion des Stocks");
    }
    
	/**
	 *
	 * On calcule le CA sur une période définie et comparaison au N-1
	 *
	 */
    public function genere($ajax = false) {
		$stocks_articles = array();
        
        $filtres = $this->recupere_filtre_pour_rapport($this->rapport);

        $this->export_excel($this->rapport);

		$titres = $this->recupere_titres_rapport([
			ucfirst(table_libre('article')->element),
			traduction('rapport.gestion_des_stocks.colonnes.stock_actuel'),
			traduction('rapport.gestion_des_stocks.colonnes.stock_reserve'),
			traduction('rapport.gestion_des_stocks.colonnes.stock_disponible'),
			traduction('rapport.gestion_des_stocks.colonnes.stock_achete'),
			traduction('rapport.gestion_des_stocks.colonnes.stock_a_terme'),
			traduction('rapport.gestion_des_stocks.colonnes.consommation_periode'),
			traduction('rapport.gestion_des_stocks.colonnes.valorisation_stock_actuel'),
			traduction('rapport.gestion_des_stocks.colonnes.adressage'),
			traduction('rapport.gestion_des_stocks.colonnes.options'),
		]);

		$this->rapport->titres($titres);

		// on va chercher les prix d'achat des articles
		$prix_achats_moyens_par_article = $this->prix_achats_moyens_par_article();

        $articles = modele('article');

        $articles = $this->applique_filtres_sur_requete($articles, $filtres);

		//$pagination = $this->pagination($this->rapport, $articles->get()->count());

		//$articles = $articles->take($pagination['take'])->skip($pagination['skip'])->get();
		$articles = $articles->get();

		// $liste_article_debug = array();
		// on met à jour les articles
		foreach($articles as $article) {

			// stock initial
			if(request()->has('stock_initial_'.$filtres['entrepot_id'].'_'.$article->id)) {

				$stock_initial = modele('stock_initial')->where('entrepot_id', $filtres['entrepot_id'])->where('article_id', $article->id)->first();

				if($stock_initial === null) {

					$stock_initial = modele('stock_initial');
				}

				$stock_initial->entrepot_id = $filtres['entrepot_id'];
				$stock_initial->article_id = $article->id;
				$stock_initial->stock_initial = request()->get('stock_initial_'.$filtres['entrepot_id'].'_'.$article->id);

				$stock_initial->save();
			}

			//Si on trie par stock, on remplit un tableau à part avec la liste des articles classée par familles
			if(!empty($filtres['tri_par_stock'])){

				$articles_tries_par_stock[$article->famille_id][$article->id] = $article;
			}
		}

		// On filtre par article_id, pour limiter les mouvements de stocks à charger
		$articles_id = $articles->pluck('id');

		// la consommation sur la période
		$consommation_stocks = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->where('date', '>=', $filtres['dates']['date_debut'])
				->where('date', '<=', $filtres['dates']['date_fin'])
				->where('entrepot_id', $filtres['entrepot_id'])
				->whereIn('article_id', $articles_id)
				->where('quantite', '<', 0)
				->zero_ou_null('reserve')
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id')
				->toArray();

		$famille_id = false;

		$total_valorisation = 0;

        $adressage = modele('stock_initial')
            ->select('adressage', 'article_id')
            ->where('entrepot_id', $filtres['entrepot_id'])
            ->whereIn('article_id', $articles_id)
            ->zero_ou_null('conditionnement_id')
            ->groupBy('article_id')
            ->get()
            ->pluck('adressage', 'article_id');

		// on va chercher les infos de stocks
		$infos_stocks = service('stocks')->details_stocks_par_article($articles_id->toArray());

		// on créé les lignes de la liste du rapport
		foreach($articles as $article) {

			//On vérifie si la famille a changé et si on ne trie pas par stock (géré à part si trié par stock)
			if($famille_id != $article->famille_id && empty($filtres['tri_par_stock'])) {

				$famille = management('famille', $article->famille_id);

				$this->rapport->sous_titre($famille->affiche());

				$famille_id = $article->famille_id;
			}

			$stock_mini = $infos_stocks[$article->id]['seuils']['total']['minimum'];
			$stock_alerte = $infos_stocks[$article->id]['seuils']['total']['alerte'];

			$stock_actuel = $infos_stocks[$article->id]['stock_actuel']['par_entrepot'][$filtres['entrepot_id']]['total'];
			$stock_reserve = $infos_stocks[$article->id]['stock_reserve']['par_entrepot'][$filtres['entrepot_id']]['total'];
			$stock_disponible = $infos_stocks[$article->id]['stock_disponible']['par_entrepot'][$filtres['entrepot_id']]['total'];
			$stock_achete = $infos_stocks[$article->id]['stock_achete']['par_entrepot'][$filtres['entrepot_id']]['total'];
			$stock_a_terme = $infos_stocks[$article->id]['stock_a_terme']['par_entrepot'][$filtres['entrepot_id']]['total'];

			//on détermine les badges d'état des stocks actuels et prévisionels
			$type_badge = "badge-success";
			$badge = '<i class="fa fa-check" aria-hidden="true"></i>';

			if($stock_actuel <= $stock_mini) {

				$type_badge = 'badge-danger';
				$badge = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>';
			} elseif ($stock_actuel <= $stock_alerte) {

				$type_badge = 'badge-warning';
				$badge = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>';
			}

			$html_badge_actuel = '<span class="badge ' . $type_badge .  '">' . $badge . '</span> ';


			$type_badge = "badge-success";
			$badge = '<i class="fa fa-check" aria-hidden="true"></i>';

			if($stock_a_terme <= $stock_mini) {

				$type_badge = 'badge-danger';
				$badge = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>';
			} elseif ($stock_a_terme <= $stock_alerte) {

				$type_badge = 'badge-warning';
				$badge = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>';
			}

			$html_badge_previsionel = '<span class="badge ' . $type_badge .  '">' . $badge . '</span> ';

			$valorisation_stock = "N/A";

			// On ajoute l'attribut stock actuel si on trie par stock pour pouvoir trier ensuite et on ne crée pas la ligne si on veut trier par stock
			$consommation = 0;
			if(isset($consommation_stocks[$article->id]))
				$consommation = $consommation_stocks[$article->id];

			//On récupére le prix moyens de l'article
			if(isset($prix_achats_moyens_par_article[$article->id]) && !empty($prix_achats_moyens_par_article[$article->id])){

			    $valorisation_stock = $prix_achats_moyens_par_article[$article->id] * $stock_actuel;
                $total_valorisation += $prix_achats_moyens_par_article[$article->id] * $stock_actuel;
            }
			else {
                // on calcule la valorisation du stock actuel
                if (isset($article->prix_d_achat) && !empty($article->prix_d_achat)) {

                    $valorisation_stock = $article->prix_d_achat * $stock_actuel;
                    $total_valorisation += $article->prix_d_achat * $stock_actuel;
                }
            }

			// on créé une nouvelle ligne dans le rapport
			$ligne = $this->recupere_colonnes_ligne(array(
				management('article', $article->id)->affiche_lien(),
				'<div class="d-flex justify-content-between"><span>' . $stock_actuel . '</span>' . $html_badge_actuel . '</div>',
				$stock_reserve,
				$stock_disponible,
				$stock_achete,
				'<div class="d-flex justify-content-between"><span>' . $stock_a_terme . '</span>' . $html_badge_previsionel . '</div>',
				$consommation,
				$valorisation_stock,
                $adressage[$article->id] ?? 'N/A',
				'<span onClick="affiche_detail_gestion_stock(' . $article->id . ', ' . $filtres['entrepot_id'] . ')" class="far fa-eye" style="cursor:pointer;"></span>'
            ), $article->id);

			// On stocke comme attribut le stock actuel et la ligne à écrire sur le rapport si on trie par stock
			if(!empty($filtres['tri_par_stock'])){

				$articles_tries_par_stock[$article->famille_id][$article->id]->stock_actuel = $stock_actuel;
				$articles_tries_par_stock[$article->famille_id][$article->id]->ligne = $ligne;
				continue;
			}

			// on créé une nouvelle ligne dans le rapport
			$this->rapport->ligne($ligne);
		}

		if(empty($filtres['tri_par_stock'])){

			$this->rapport->sous_titre(traduction('rapport.gestion_des_stocks.total_valorisation_articles')." : ".montant($total_valorisation, 2));

			return $this->rapport->genere($ajax);
		}

		foreach($articles_tries_par_stock as $famille => $articles){

			//On modifie la fonction usort() si on trie par ordre croissant ou décroissant
			// (pas moyen de passer la valeur de tri_par_stock en paramètre de la fonction utilisée dans le usort())
			if($filtres['tri_par_stock'] == 'croissant'){

				usort($articles, function($a, $b){

					$stock_a = $a->stock_actuel;
					$stock_b = $b->stock_actuel;

					if($stock_a == $stock_b)
						return 0;

					return ($stock_a < $stock_b) ? -1 : 1;
				});
			}
			elseif($filtres['tri_par_stock'] == 'decroissant'){

				usort($articles, function($a, $b){

					$stock_a = $a->stock_actuel;
					$stock_b = $b->stock_actuel;

					if($stock_a == $stock_b)
						return 0;

					return ($stock_a > $stock_b) ? -1 : 1;
				});
			}

			// On parcourt la liste des articles triée
			foreach($articles as $article){

				if($famille_id != $article->famille_id) {

					$famille = management('famille', $article->famille_id);

					$this->rapport->sous_titre($famille->affiche());

					$famille_id = $article->famille_id;
				}
				$this->rapport->ligne($article->ligne);
			}
		}

		$this->rapport->sous_titre(traduction('rapport.gestion_des_stocks.total_valorisation_stock_actuel')." : ".montant($total_valorisation, 2));

		return $this->rapport->genere($ajax);
    }

	/**
	 *
	 * Retourne les prix d'achat moyen sur les articles
	 *
	 * Amélioration possible niveau perf : on récupère l'ensemble des prix d'achat de tous les articles
	 * Mais on a mis en place la pagination, du coup on pourrait peut être voir pour ne récupérer que lse articles concernés ?
	 * A voir si c'est nécessaire d'afficher un grand total ou pas...
	 *
	 */
	public function prix_achats_moyens_par_article() {

		// $prix_achats_moyens_par_article = modele('facture_achat')
        //     ->join('facture_achat_lignes', 'facture_achat_lignes.document_id', 'facture_achat.id')
        //     ->whereBetween('date',[date('Y-m-d',strtotime('now -1 year')),date('Y-m-d')])
        //     ->select(\DB::raw("avg(tarif * (100 - remise) / 100) as prix_achat_moyen, article_id"))
        //     ->groupBy('article_id')
        //     ->get()
        //     ->pluck('prix_achat_moyen','article_id')
        //     ->toArray();

		$derniere_date_achat = modele('facture_achat')
			->join('facture_achat_lignes', 'facture_achat_lignes.document_id', 'facture_achat.id')
			->selectRaw('max(date) as date,article_id')
			->groupBy('article_id')
			->get()
			->pluck('date','article_id')
			->toArray();

		$prix_achats_moyens_par_article = [];

		foreach($derniere_date_achat as $article_id => $date){

			if($date > date('Y-m-d',strtotime('now -1 year'))){

				$prix_achats_moyens = modele('facture_achat')
					->join('facture_achat_lignes', 'facture_achat_lignes.document_id', 'facture_achat.id')
					->whereBetween('date',[date('Y-m-d',strtotime('now -1 year')),date('Y-m-d')])
					->where('article_id',$article_id)
					->select(\DB::raw("SUM(tarif * (100 - remise) / 100 * quantite) as prix_achat_moyen, article_id, SUM(quantite) as quantite_totale"))
					->groupBy('article_id')
					->get()
					->keyBy('article_id')
					->toArray();

				if($prix_achats_moyens[$article_id]['quantite_totale'] > 0)
					$prix_achats_moyens[$article_id] = $prix_achats_moyens[$article_id]['prix_achat_moyen'] / $prix_achats_moyens[$article_id]['quantite_totale'];
				else
					$prix_achats_moyens[$article_id] = 0;

				$prix_achats_moyens_par_article = array_merge($prix_achats_moyens_par_article,$prix_achats_moyens);
			}

			else{

				$prix_achats_moyens = modele('facture_achat')
					->join('facture_achat_lignes', 'facture_achat_lignes.document_id', 'facture_achat.id')
					->where('article_id',$article_id)
					->select(\DB::raw("SUM(tarif * (100 - remise) / 100 * quantite) as prix_achat_moyen, article_id, SUM(quantite) as quantite_totale"))
					->groupBy('article_id')
					->get()
					->keyBy('article_id')
					->toArray();

				if($prix_achats_moyens[$article_id]['quantite_totale'] > 0)
					$prix_achats_moyens[$article_id] = $prix_achats_moyens[$article_id]['prix_achat_moyen'] / $prix_achats_moyens[$article_id]['quantite_totale'];
				else
					$prix_achats_moyens[$article_id] = 0;

				$prix_achats_moyens_par_article = array_merge($prix_achats_moyens_par_article,$prix_achats_moyens);

			}

		}

		return $prix_achats_moyens_par_article;
	}

	public function filtres_sur_articles($rapport){

		return [];
	}

    /**
     * @param $titres array
     * @return array
     *
     * On retourne la ligne à ajouté, utile pour la spécification
     *
     */
    public function recupere_titres_rapport($titres){

        return $titres;

    }

    /**
     * @param $colonnes array
     * @param $element_id int
     * @return array
     *
     * On retourne la ligne à ajouté, utile pour la spécification
     *
     */
    public function recupere_colonnes_ligne($colonnes, $element_id){

        return $colonnes;

    }

    /**
     * @param $this->rapport
     * @return array
     *
     * On retourne les valeurs des filtres dans un array
     *
     */
    public function recupere_filtre_pour_rapport($rapport){

        $filtres = [];

        $filtres['recherche'] = $this->recherche($this->rapport);
        $filtres['entrepot_id'] = $this->entrepot($this->rapport);
        $filtres['familles_id'] = $this->familles_de_produits($this->rapport);
        $filtres['tri_par_stock'] = $this->stock_actuel($this->rapport);
        $filtres['filtres_sur_articles'] = $this->filtres_sur_articles($this->rapport);
        $filtres['fournisseur'] = $this->fournisseur($this->rapport);
        $filtres['dates'] = $this->dates_mensuelles($this->rapport);

        return $filtres;

    }

    /**
     * @param $requete
     * @return array
     *
     * On retourne la requête avec les filtres appliqués
     *
     */
    public function applique_filtres_sur_requete($requete, $filtres){

        $requete = $requete
            ->select('article.*')
            ->where('stockable', 1)
            ->where('article.chaine_tags_recherche','like', '%' . $filtres['recherche'] . '%')
            ->orderBy('famille_id');
        
        if(!empty($filtres['fournisseur']) && $filtres['fournisseur'] !== "null") {

            $requete = $requete
                ->join('article_fournisseur', 'article_fournisseur.article_id', 'article.id')
                ->where('article_fournisseur.fournisseur_id', $filtres['fournisseur']);
            
        }

        if(!empty($filtres['familles_id']))
            $requete = $requete->whereIn('famille_id', $filtres['familles_id']);

        foreach($filtres['filtres_sur_articles'] as $key => $filtre){

            if($filtre != null)
                $requete = $requete->orWhere($key, $filtre);
        }

        return $requete;

    }

}
