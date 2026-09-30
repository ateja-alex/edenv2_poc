<?php

namespace App\Eden\Managements\Services;

use DB;

class Stocks_service {

    /**
	 *
	 * Retourne les informations suivantes :
	 *
	 * Stock actuel
	 * Stock réservé
	 * Stock disponible (actuel - réservé)
	 * Stock acheté (à recevoir)
	 * Stock à terme (actuel - reservé + acheté)
	 *
	 * @param $articles_ids array avec les id d'articles (on peut éventuellement passer un id_article, qui sera transformé automatiquement en array)
	 * @param $id_entrepot, permet éventuellement de filtrer par entrepôt
	 *
	 */
	public function details_stocks_par_article($articles_ids = array()) {

		if(!is_array($articles_ids))
			$articles_ids = array($articles_ids);
        else
            $articles_ids = array_unique($articles_ids);

        $stocks_par_conditionnement = modele('stocks_par_conditionnement')
            ->whereIn('article_id',$articles_ids)
            ->get()->groupBy(['article_id','entrepot_id','conditionnement_id']);

        $colonnes = array(
            'stock_actuel',
            'stock_reserve',
            'stock_disponible',
            'stock_achete',
            'stock_a_terme'
        );

        $details_stocks_par_article = [];

        $conditionnements_article = modele('conditionnement')->whereIn('article_id', $articles_ids)->get()->groupBy('article_id')->toArray();
        $entrepots = modele('entrepot')->get()->keyBy('id')->toArray();
        $seuils = $this->recupere_seuils_alerte($articles_ids, $conditionnements_article);

        foreach($colonnes as $colonne) {

            $stocks = $stocks_par_conditionnement
                ->map(function ($article) use ($colonne) {
                    return $article->map(function ($entrepot) use ($colonne) {
                        return $entrepot->map(function ($conditionnement) use ($colonne) {
                            return $conditionnement[0]->{$colonne};
                        });
                    });
                })->toArray();

            foreach($articles_ids as $article_id){
                $details_stocks_par_article[$article_id][$colonne] = $this->met_en_forme_tableau_final($stocks[$article_id] ?? [], $conditionnements_article, $article_id, $entrepots);
            }

        }

        foreach($articles_ids as $article_id){
            $details_stocks_par_article[$article_id]['seuils'] = $seuils[$article_id];
        }

		return $details_stocks_par_article;
	}


	/**
	 *
	 * Récupère les seuils d'alerte par article
	 *
	 */
	protected function recupere_seuils_alerte($articles_id, $conditionnements) {

		$seuils_par_article = array();

		// on prépare le tableau
		foreach($articles_id as $article_id) {

			$seuils_par_article[$article_id] = array(

				'par_conditionnement' => array(array('alerte' => 0, 'minimum' => 0)),
				'total' => array('alerte' => 0, 'minimum' => 0),
			);

			if(isset($conditionnements[$article_id])) {

				foreach($conditionnements[$article_id] as $conditionnement) {

					$seuils_par_article[$article_id]['par_conditionnement'][$conditionnement['id']] = array('alerte' => 0, 'minimum' => 0);
				}
			}
		}

		$seuils = modele('seuil_article')->whereIn('article_id', $articles_id)->get();

		foreach($seuils as $seuil) {

			$conditionnement_id = (int) $seuil->conditionnement_id;
			$article_id = (int) $seuil->article_id;

			$seuils_par_article[$article_id]['par_conditionnement'][$conditionnement_id]['alerte'] = $seuil->seuil_alerte;
			$seuils_par_article[$article_id]['par_conditionnement'][$conditionnement_id]['minimum'] = $seuil->seuil_mini;

			if(empty($conditionnement_id)) {

				$seuils_par_article[$article_id]['total']['alerte'] += $seuil->seuil_alerte;
				$seuils_par_article[$article_id]['total']['minimum'] += $seuil->seuil_mini;
			}
			else {

				$quantite = $this->quantite_par_conditionnement($conditionnements, $conditionnement_id, $article_id);

				$seuils_par_article[$article_id]['total']['alerte'] += $seuil->seuil_alerte * $quantite;
				$seuils_par_article[$article_id]['total']['minimum'] += $seuil->seuil_mini * $quantite;
			}
		}

		return $seuils_par_article;
	}

	/**
	 *
	 * Retourne la quantité par conditionnement
	 *
	 */
	protected function quantite_par_conditionnement($conditionnements_article, $conditionnement_id, $id_article) {

		if(!isset($conditionnements_article[$id_article]))
			return 1;

		$conditionnements = $conditionnements_article[$id_article];

		foreach($conditionnements as $conditionnement) {

			if($conditionnement['id'] == $conditionnement_id)
				return $conditionnement['quantite'];
		}

		return 1;
	}

	/**
	 *
	 * Retourne le nom d'un conditionnement
	 *
	 */
	protected function recupere_info_conditionnement($conditionnements_article, $article_id, $conditionnement_id, $champ) {

		if(!isset($conditionnements_article[$article_id])) {

			return null;
		}

		foreach($conditionnements_article[$article_id] as $conditionnement) {

			if($conditionnement['id'] == $conditionnement_id)
				return $conditionnement[$champ];
		}
	}

	/**
	 *
	 * remet en forme le tableua des résultats finaux pour un article donné
	 *
	 */
	protected function met_en_forme_tableau_final($stocks, $conditionnements_article, $id_article, $entrepots_par_ids) {

		$total = 0;

		$resultat = array(
			'par_entrepot' => array(),
			'par_conditionnement' => array(),
			'total' => 0
		);

		foreach($stocks as $entrepot_id => $stock_par_conditionnement) {

			foreach($stock_par_conditionnement as $conditionnement_id => $quantite) {

				$quantite_par_conditionnement = $this->quantite_par_conditionnement($conditionnements_article, $conditionnement_id, $id_article);

				// par entrepot
				if(!isset($resultat['par_entrepot'][$entrepot_id])) {

					$resultat['par_entrepot'][$entrepot_id] = array(

						'par_conditionnement' => array(),
						'total' => 0
					);

                    if(isset($entrepots_par_ids[$entrepot_id]))
                        $resultat['par_entrepot'][$entrepot_id]['nom'] = $entrepots_par_ids[$entrepot_id]['nom'];

				}

				if(!isset($resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id])) {

					$resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id] = array(
						'unite' => 0,
						'conditionne' => 0,
						'nom_conditionnement' => $this->recupere_info_conditionnement($conditionnements_article, $id_article, $conditionnement_id, 'nom'),
						'quantite_conditionnement' => $this->recupere_info_conditionnement($conditionnements_article, $id_article, $conditionnement_id, 'quantite'),
						'id_conditionnement' => $conditionnement_id,
					);
				}

				$resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['unite'] += $quantite;
				$resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['conditionne'] += $quantite / $quantite_par_conditionnement;
				$resultat['par_entrepot'][$entrepot_id]['total'] += $quantite;

                $resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['unite'] = round($resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['unite'], 3);
                $resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['conditionne'] = round($resultat['par_entrepot'][$entrepot_id]['par_conditionnement'][$conditionnement_id]['conditionne'], 3);
                $resultat['par_entrepot'][$entrepot_id]['total'] = round($resultat['par_entrepot'][$entrepot_id]['total'], 3);

				// par conditionnement
				if(!isset($resultat['par_conditionnement'][$conditionnement_id])) {

					$resultat['par_conditionnement'][$conditionnement_id] = array(

						'par_entrepot' => array(),
						'total' => 0,
                        'nom' => $this->recupere_info_conditionnement($conditionnements_article, $id_article, $conditionnement_id, 'nom'),
					);
				}

				if(!isset($resultat['par_conditionnement'][$conditionnement_id]['par_entrepot'][$entrepot_id])) {

					$resultat['par_conditionnement'][$conditionnement_id]['par_entrepot'][$entrepot_id] = 0;
				}

				$resultat['par_conditionnement'][$conditionnement_id]['par_entrepot'][$entrepot_id] += $quantite;
				$resultat['par_conditionnement'][$conditionnement_id]['total'] += $quantite;

				// total
				$resultat['total'] += $quantite;

                $resultat['par_conditionnement'][$conditionnement_id]['par_entrepot'][$entrepot_id] = round($resultat['par_conditionnement'][$conditionnement_id]['par_entrepot'][$entrepot_id], 3);
                $resultat['par_conditionnement'][$conditionnement_id]['total'] = round($resultat['par_conditionnement'][$conditionnement_id]['total'], 3);
                $resultat['total'] = round($resultat['total'], 3);
			}
		}

		return $resultat;
	}

}
