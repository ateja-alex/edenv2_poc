<?php

namespace App\Eden\Managements\Rapports\Rapports;


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
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {

		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		$this->rapport->titre = traduction('rapport.ca_par_article.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);
		
		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$ca_par_article = $calcul->documents_valides_uniquement(false)
						->groupe_par('article')
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
						
		$this->rapport->titres(array(ucfirst(table_libre('article')->element), array(traduction('rapport.ca_par_article.ca_ht_periode'), 'css_montant')));
		
		$familles = modele('famille')->zero_ou_null('parent_id')->orderBy('nom')->get();
		
		$famille_management = management('famille');
		
		$articles_par_famille = $famille_management->articles_par_famille();
			
		foreach($familles as $famille) {
			
			$this->contenu_famille($famille, $famille_management, $ca_par_article, $this->rapport, $articles_par_famille);
		}
		
		return $this->rapport->genere($ajax);
    }
	
	protected function contenu_famille($famille, $famille_management, $ca_par_article, $rapport, $articles_par_famille, $rang = 0) {
		
		// on crée le sous titre
		$espaces = '';
		for($i=0; $i<= $rang; $i++) {
			
			$espaces .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		$this->rapport->sous_titre($espaces.$famille->nom);
			
		$contenu = $famille_management->contenu_famille($famille, $articles_par_famille, true);
		
		// les sous familles
		if(isset($contenu['sous_familles']) && !empty($contenu['sous_familles'])) {
			
			foreach($contenu['sous_familles'] as $sous_famille) {
				
				$rang++;
				
				$this->contenu_famille($sous_famille['modele_famille'], $famille_management, $ca_par_article, $this->rapport, $articles_par_famille, $rang);
				
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
					
					$this->rapport->ligne($ligne);
				}
			}
		}
	}
	
	
	
}