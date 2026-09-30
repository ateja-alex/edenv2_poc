<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Saisie_des_temps_regie_controller extends Controller{

    /**
	 *
	 * Saisie des temps regie
	 *
	 */
	public function index() {

        return view('eden::saisie_des_temps_regie');
	}

    /**
	 *
	 * On récupère le tableau récap des temps de la semaine par projet
	 *
	 */
	public function initialisation() {

        $feuille_de_temps_par_type_element = modele('feuille_de_temps')
            ->where('date', '>=', date('Y-m-01', strtotime('last month')))
            ->where('date', '<=', date('Y-m-d'))
            ->get()->groupBy('type_element');

        foreach($feuille_de_temps_par_type_element as $type_element => $feuilles_de_temps){

            $feuilles_de_temps_par_element = $feuilles_de_temps->groupBy('element_id');

            $elements = modele($type_element)
                ->whereIn('id',$feuilles_de_temps->pluck('element_id')->toArray())
                ->get()->keyBy('id');

            $feuille_de_temps_formatees = array();

            foreach($feuilles_de_temps_par_element as $element_id => $feuille_de_temps_element){

                if(!isset($elements[$element_id]))
                    continue;

                $feuille_de_temps = clone modele_par_defaut('feuille_de_temps');

                $feuille_de_temps->type_element = $type_element;
                $feuille_de_temps->element_id = $element_id;

                if(!isset($feuille_de_temps_formatees[$element_id])){
                    $feuille_de_temps_formatees[$element_id] = array(
                        'chaine_affichage' => management($type_element,$element_id,$elements[$element_id])->affiche(),
                        'element' => $elements[$element_id],
                        'ce_mois_ci' => round(modele('feuille_de_temps')
                                    ->where('type_element', $type_element)
                                    ->where('element_id', $element_id)
                                    ->where('date', '>=', date('Y-m-01'))
                                    ->orderBy('date')
                                    ->orderBy('utilisateur_id')
                                    ->sum('duree') / 7, 2).' jours',
                        'mois_dernier' => round(modele('feuille_de_temps')
                                    ->where('type_element', $type_element)
                                    ->where('element_id', $element_id)
                                    ->where('date', '>=', date('Y-m-01', strtotime('last month')))
                                    ->where('date', '<=', date('Y-m-t', strtotime('last month')))
                                    ->orderBy('date')
                                    ->orderBy('utilisateur_id')
                                    ->sum('duree') / 7, 2).' jours',
                        'feuilles_de_temps' => [],
                        'feuille_de_temps' => $feuille_de_temps,
                    );
                }

                $feuille_de_temps_formatees[$element_id]['feuilles_de_temps'] = $feuille_de_temps_element;
            }

            $feuille_de_temps_par_type_element[$type_element] = array_values($feuille_de_temps_formatees);
        }

		return response()->json(array(
			'feuilles_de_temps_par_type_element' => $feuille_de_temps_par_type_element,
			'feuille_de_temps' => modele_par_defaut('feuille_de_temps'),
		));
    }
    /**
	 *
	 * Génère une facture pour le mois passé pour le temps régie
	 *
 	 */
	public function generer_facture($type_element,$element_id) {

		if(empty(request()->date_debut))
			$date_debut = date('Y-m-01', strtotime('last month'));
		else
			$date_debut = request()->date_debut;

		$date_fin = date('Y-m-t', strtotime($date_debut));

		// on va chercher les temps
		$feuilles_de_temps = modele('feuille_de_temps')
								->where('type_element', $type_element)
								->where('element_id', $element_id)
								->where('date', '>=', $date_debut)
								->where('date', '<=', $date_fin)
								->orderBy('date')
								->orderBy('utilisateur_id')
								->get();

		$facture = management('facture_vente');

		$infos = array(

			'date' => $date_fin,
			'date_de_reglement' => date('Y-m-t', strtotime('last month')),
			'objet' => 'Facture régie '.date('m/Y', strtotime($date_fin)).' ('.management($type_element,$element_id)->affiche().')',
		);

        $champ_libre = Champ_libre::where('type_element','facture_vente')->where('type',42)->where('type_element_ajax',$type_element)->first();

        if(!empty($champ_libre))
            $infos[$champ_libre->nom_sql] = $element_id;

		$articles = array();

		$article = modele('article', 100001);

		foreach($feuilles_de_temps as $feuille_de_temps) {

			$utilisateur = modele('utilisateur', $feuille_de_temps->utilisateur_id);

			$articles[] = array(

				'article_id' => $article->id,
				'tva' => $article->taux_de_tva,
				'tarif' => round($article->tarif / 7),
				'quantite' => $feuille_de_temps->duree,
				'designation' => $feuille_de_temps->tache.' ('.$utilisateur->prenom.' '.$utilisateur->nom.', '.formate_date('d/m/Y', $feuille_de_temps->date).')',
			);
		}

		$infos['articles'] = $articles;

		$facture->enregistre($infos);

		// on redirige
		return redirect()->back();
	}

    /**
	 *
	 * Saisie des temps regie : on récupère les infos d'un élément
	 *
	 */
	public function recupere_element($type_element,$element_id) {

		$element = modele($type_element, $element_id);
		$feuilles_de_temps = modele('feuille_de_temps')
								->where('type_element', $type_element)
								->where('element_id', $element_id)
								->where('date', '>=', date('Y-m-01', strtotime('last month')))
								->orderBy('date')
								->get();

        $feuille_de_temps = clone modele_par_defaut('feuille_de_temps');

        $feuille_de_temps->type_element = $type_element;
        $feuille_de_temps->element_id = $element_id;

		return response()->json(array(
            'chaine_affichage' => management($type_element,$element_id,$element)->affiche(),
            'element' => $element,
			'feuilles_de_temps' => $feuilles_de_temps,
			'feuille_de_temps' => $feuille_de_temps,
			'ce_mois_ci' => round(modele('feuille_de_temps')
                                ->where('type_element', $type_element)
                                ->where('element_id', $element_id)
								->where('date', '>=', date('Y-m-01'))
								->orderBy('date')
								->orderBy('utilisateur_id')
								->sum('duree') / 7, 2).' jours',
			'mois_dernier' => round(modele('feuille_de_temps')
                                ->where('type_element', $type_element)
                                ->where('element_id', $element_id)
								->where('date', '>=', date('Y-m-01', strtotime('last month')))
								->where('date', '<=', date('Y-m-t', strtotime('last month')))
								->orderBy('date')
								->orderBy('utilisateur_id')
								->sum('duree') / 7, 2).' jours',
		));
	}
}