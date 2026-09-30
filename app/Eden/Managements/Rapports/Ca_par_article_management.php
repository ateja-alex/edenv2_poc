<?php

namespace App\Eden\Managements\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_par_article_management extends Rapports_management {
	
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		
		$rapport = new Rapport_liste_management('ca_par_article', 'CA par article');
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($rapport);
		$entites = $this->entites($rapport);
		
		$rapport->titre = traduction('rapport.ca_par_article.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);
		
		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$ca_par_article = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->plus('facture_vente')
						->groupe_par('article')
						->resultat();
						
		$rapport->titres(array(ucfirst(table_libre('article')->element), array(traduction('rapport.ca_par_article.ca_ht_periode'), 'css_montant')));

		$familles = modele('famille')->where('parent_id', 0)->orderBy('nom')->get();
		
		$famille_management = management('famille');
		
		$articles_par_famille = $famille_management->articles_par_famille();
			
		foreach($familles as $famille) {
			
			$this->contenu_famille($famille, $famille_management, $ca_par_article, $rapport, $articles_par_famille);
		}
		
		return $rapport->genere($ajax);
    }
	
	protected function contenu_famille($famille, $famille_management, $ca_par_article, $rapport, $articles_par_famille, $rang = 0) {
		
		// on crée le sous titre
		$espaces = '';
		for($i=0; $i<= $rang; $i++) {
			
			$espaces .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		$rapport->sous_titre($espaces.$famille->nom);
			
		$contenu = $famille_management->contenu_famille($famille, $articles_par_famille, true);
		
		// les sous familles
		if(isset($contenu['sous_familles']) && !empty($contenu['sous_familles'])) {
			
			foreach($contenu['sous_familles'] as $sous_famille) {
				
				$rang++;
				
				$this->contenu_famille($sous_famille, $famille_management, $ca_par_article, $rapport, $articles_par_famille, $rang);
				
				$rang--;
			}
		}
		
		// les articles directs
		if(isset($contenu['articles']) && !empty($contenu['articles'])) {
			
			foreach($contenu['articles'] as $article) {
				
				if(isset($ca_par_article[$article->id])) {
					
					$ligne = array(
						'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$espaces.management('article', $article->id)->affiche_lien(),
						array(montant($ca_par_article[$article->id]), ' css_montant'),
					);
					
					$rapport->ligne($ligne);
				}
			}
		}
	}
	
	
	
}