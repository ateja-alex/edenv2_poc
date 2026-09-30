<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
* Gestion des rapports
*/
class Budget_management extends Rapports_management {

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
	 * On créé le tableau du budget
	 * 
	 */	
    public function genere($ajax = false) {

		if(!empty(request()->all())){
			self::enregistrement_valeurs_budget($this->rapport);
		}
		
		// on récupère les différents filtres
		$nouvelle_ligne = $this->ajouter_ligne_budget($this->rapport);
		$suppression = $this->suppression_ligne_budget($this->rapport);
		$dates = $this->dates_mensuelles($this->rapport);
		$enregistrer = $this->enregistrer($this->rapport);
		
		$espaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		//On récupère les mois affin de les afficher dans le titre du tableau

		$mois = [traduction('rapport.budget.rubrique')];
		foreach($dates['dates'] as $date){
			$mois[] = $date['nom'];
		}
		$mois[] = traduction('rapport.budget.total_annuel');

        $this->rapport->titre = traduction('rapport.budget.titre_affichage',null,[formate_date('d/m/Y', $dates['date_debut']),formate_date('d/m/Y', $dates['date_fin'])]);

		$this->rapport->titres($mois);
		
		$rubriques = modele('budget_rubrique')->orderBy('ordre')->get();
		
		$rubrique_management = management('budget_rubrique');
			
		foreach($rubriques as $rubrique) {
			
			$this->contenu_table($rubrique, $this->rapport, $dates);
		}

		$sous_titre = ['<b>'.traduction('rapport.budget.total').'</b>'];
		
		for($i = 0 ; $i < count($dates['dates']) ; $i++){
			
			$sous_titre[] = "<p class='js_total_total_" . $dates['dates'][$i]['periode'] . "'></p>";
		}
		
		$sous_titre[] = "<p class='js_total_annuel'></p>";
		$this->rapport->titres($sous_titre);
		
		return $this->rapport->genere($ajax);
    }

	/*
	 * 
	 * On intègre le contenu au tableau
	 * 
	 */
	
	protected function contenu_table($rubrique, $rapport, $dates, $rang = 0) {
		// on crée les rubriques

		//Si le rang est égal à zéro on créé une rubrique
		if($rang == 0){

			$this->creation_rubrique($rubrique, $this->rapport, $dates);

		}

		//Si le rang est différent de 0 c'est que nous traitons un poste ou une sous-catégorie
		else{

			$this->creation_poste($rubrique, $this->rapport, $dates);
			
		}

		$postes = modele('budget_poste')->where('rubrique_id',$rubrique->id)->orderBy('ordre')->get();
		
		// les postes
		if(!empty($postes)) {
			
			foreach($postes as $poste) {
				
				$rang++;
				
				$this->contenu_table($poste, $this->rapport,$dates, $rang);
				
				$rang--;
			}
		}
	}

	/**
	 * 
	 * Création des rubriques
	 * 
	 */

	protected function creation_rubrique($rubrique, $rapport, $dates){

		$sous_titre = ["<div style='line-height:30px;'><input type='checkbox' name='rubrique." . $rubrique->id . "' value='rubrique." . $rubrique->id . "' class='js_champ_{$this->rapport->id_rapport}'> <label for='rubrique." . $rubrique->id . "'>" .$rubrique->nom. "</label><span style='float: right;cursor:pointer; margin-right:2px;' class='css_action_icon secondaire fa fa-plus js_ajout_post' data-toggle='modal' id=" . $rubrique->id . " data-target='#ajouter_postebudget'></span><span style='float: right;cursor:pointer; margin-right:2px;' class='css_action_icon secondaire fa fa-pen js_edit_post' data-toggle='modal' id=" . $rubrique->id . " data-target='#edit_postebudget'></span></div>"];
			
			for($i = 0 ; $i < count($dates['dates']) ; $i++){
				$sous_titre[] = "<p class='js_total_" . $dates['dates'][$i]['periode']. "_" .$rubrique->id . " js_". $dates['dates'][$i]['periode'] ." js_" . explode('-',$dates['dates'][1]['periode'])[0] . "_" . $rubrique->id . "'></p>";
			}

			$sous_titre[] = "<p class='js_total_ligne_" . explode('-',$dates['dates'][1]['periode'])[0] . "_" . $rubrique->id . "'></p>";
			$this->rapport->sous_titre($sous_titre);

			//On regarde quel type de rubrique nous traitons
			//Si c'est un type 1 alors la rubrique est un type salaire
			if($rubrique->type_de_rubrique == 1){

				$this->creation_postes_salaries($rubrique, $this->rapport, $dates);
				
			}
	}

	/*
	*
	* Création des postes(lignes) liés à une rubrique
	*
	*/

	protected function creation_poste($rubrique, $rapport, $dates){
		
		//On créé la ligne dédié au poste
		$sous_titre = ["<div style='line-height:30px;'><input type='checkbox' name='poste." . $rubrique->id . "' value='poste." . $rubrique->id . "' class='js_champ_{$this->rapport->id_rapport}'> <label for='poste." . $rubrique->id . "'>" .$rubrique->nom. "</label><span style='float: right;cursor:pointer;margin-right:2px' class='css_action_icon secondaire fa fa-pen js_edit_post' data-toggle='modal' id=" . $rubrique->id . " data-target='#edit_postebudget'></span></div>"];
				
		$value = modele('budget_valeur')->where('id_budget_poste',$rubrique->id)->get()->keyBy('date');

		for($i = 0 ; $i < count($dates['dates']) ; $i++){
			
			if(isset($value[$dates['dates'][$i]['periode'].'-00'])){
				
				$sous_titre[] = "<input style='max-width:70px;' type='number' class='text js_champ_{$this->rapport->id_rapport}' value='" . $value[$dates['dates'][$i]['periode'].'-00']['valeur'] . "' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->rubrique_id . "_" . $rubrique->id . "_" . $value[$dates['dates'][$i]['periode'].'-00']['id'] . "' @wheel.prevent @keydown.up.prevent @keydown.down.prevent>";
			}
			
			else{
				
				$sous_titre[] = "<input style='max-width:70px;' type='number' class='text js_champ_{$this->rapport->id_rapport}' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->rubrique_id . "_" . $rubrique->id . "' @wheel.prevent @keydown.up.prevent @keydown.down.prevent>";
			}
		}
		
		$sous_titre[] = "<p class='js_total_ligne_" . $rubrique->rubrique_id . "_" . $rubrique->id . "'></p>";
		$this->rapport->ligne($sous_titre);
		
		//Si le poste est lié à un article alors on créé une deuxième ligne qui permettra la comparaison entre le réalisé et le previsionnel
		if(isset($rubrique->article_id) && $rubrique->article_id !== null && $rubrique->article_id !== "0"){

			$this->creation_realise_poste($rubrique, $this->rapport, $dates);
			
		}

	}

	/*
	 * 
	 * Créations des lignes liés aux salariés sous la rubrique de type salaire(1)
	 * 
	 */

	protected function creation_postes_salaries($rubrique, $rapport, $dates){
		
		//On récupère donc le tableau des couts des salariés liés par leur id
		$salaries = modele('cout_rh')->get();
		$ligne_rapport = [];

		for($j = 0 ; $j < count($salaries) ; $j++){
			$utilisateur = modele('utilisateur')->where('id',$salaries[$j]->utilisateur_id)->first()->toArray();

			//Si le tableau lignes est vide alors on créé la première ligne dans le else
			//ATTENTION le tableau lignes est différent du tableau ligne_rapport, le tableau lignes contient les nom des salariés déjà traités, il permet d'éviter de trop nombreuses boucles pour chaque test
			if(!empty($lignes)){

				//Si le tableau ne possède pas déjà une ligne correspondant au salarié on la créée
				if(!in_array($utilisateur['nom'] . ' ' . $utilisateur['prenom'],$lignes)){

					$sous_titre = [$utilisateur['nom'] . ' ' . $utilisateur['prenom']];
					$lignes[] = $utilisateur['nom'] . ' ' . $utilisateur['prenom'];

					for($i = 0 ; $i < count($dates['dates']) ; $i++){
						$sous_titre[] = "<p class='text' value='-" . $salaries[$j]->salaire_brut_charge . "' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->id . "_" . $rubrique->id . "_" . $salaries[$j]->id . "'>".montant($salaries[$j]->salaire_brut_charge,0)."</p>";
					}

					$sous_titre[] = "<p class='js_total_ligne_" . $rubrique->id . "_" . $rubrique->id . "_" . $salaries[$j]->id . "'></p>";
					$ligne_rapport[] = $sous_titre;
				}

				//Sinon c'est qu'il faut éditer la ligne car il y a un changement de salaire dans l'année
				else{

					foreach($ligne_rapport as $ligne){

						if($ligne[0] == $utilisateur['nom'] . ' ' . $utilisateur['prenom']){

							$key = array_search($ligne,$ligne_rapport);

							for($k = 1 ; $k <= count($dates['dates']) ; $k++){

								$date = $dates['dates'][$k-1]['periode'];

								if(date($date) >= date($salaries[$j]->date_de_prise_en_compte)){

									$ligne[$k] = "<p class='text' value='-" . $salaries[$j]->salaire_brut_charge . "' name='" . $dates['dates'][$k-1]['periode'] . '_' . $rubrique->id . "_" . $rubrique->id . "_" . $salaries[$j]->id . "'>".montant($salaries[$j]->salaire_brut_charge,0)."</p>";
								}
							}

							$ligne_rapport[$key] = $ligne;
						}
					}
				}
			}

			else{

				$sous_titre = [$utilisateur['nom'] . ' ' . $utilisateur['prenom']];
				$lignes[] = $utilisateur['nom'] . ' ' . $utilisateur['prenom'];

				for($i = 0 ; $i < count($dates['dates']) ; $i++){

					$sous_titre[] = "<p class='text' value='-" . $salaries[$j]->salaire_brut_charge . "' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->id . "_" . $rubrique->id . "_" . $salaries[$j]->id . "'>".montant($salaries[$j]->salaire_brut_charge,0)."</p>";
				}

				$sous_titre[] = "<p class='js_total_ligne_" . $rubrique->id . "_" . $rubrique->id . "_" . $salaries[$j]->id . "'></p>";
				$ligne_rapport[] = $sous_titre;						
			}
		}

		//Une fois tous les salariés traités on les ajoute un par un au tableau
		foreach($ligne_rapport as $ligne){

			$this->rapport->ligne($ligne);
		}
	}

	/*
	*
	* Création des postes contenant les chiffres réalisés d'un article
	*
	*/

	protected function creation_realise_poste($rubrique, $rapport, $dates){
		
		//Si l'article lié est une famille il faut récupérer tous les articles liés à cette famille
		if(Str::contains($rubrique->article_id,'famille_')){
				
			$id = explode('_',$rubrique->article_id)[1];
			$articles = modele('article')->select('id')->where('famille_id', $id)->get()->pluck('id')->toArray();
		}

		//Sinon nous n'avons qu'un article à traiter, on le traite en tableau pour éviter les multiples condition et n'utiliser qu'un whereIn lors de la requête
		else{
			
			$articles = [$rubrique->article_id];
		}
		
		$article_affiche = [""];

		$date_debut = $dates['date_debut'];
		$date_fin = $dates['date_fin'];

		$vente = modele('facture_vente_lignes')->selectRaw('article_id,LEFT(date,7) as date , sum(tarif * quantite * (100 - remise) / 100 * remise_globale_ligne) as total_ligne_ht')->join('facture_vente', 'facture_vente_lignes.document_id', '=', 'facture_vente.id')->whereIn('facture_vente_lignes.article_id', $articles)->whereBetween(\DB::raw('left(date, 7)'), [$date_debut,$date_fin])->groupBy(\DB::raw('left(date, 7)'))->get()->pluck('total_ligne_ht', 'date')->toArray();
		$achat = \DB::table('facture_achat_lignes')->selectRaw('LEFT(date,7) as date , sum(tarif * quantite * (100 - remise) / 100 * remise_globale_ligne) as total_ligne_ht')->join('facture_achat', 'facture_achat_lignes.document_id', '=', 'facture_achat.id')->whereIn('facture_achat_lignes.article_id', $articles)->whereBetween(\DB::raw('left(date, 7)'), [$date_debut,$date_fin])->groupBy(\DB::raw('left(date, 7)'))->get()->pluck('total_ligne_ht', 'date')->toArray();
		$avoir_vente = \DB::table('avoir_vente_lignes')->selectRaw('LEFT(date,7) as date , sum(tarif * quantite * (100 - remise) / 100 * remise_globale_ligne) as total_ligne_ht')->join('avoir_vente', 'avoir_vente_lignes.document_id', '=', 'avoir_vente.id')->whereIn('avoir_vente_lignes.article_id', $articles)->whereBetween(\DB::raw('left(date, 7)'), [$date_debut,$date_fin])->groupBy(\DB::raw('left(date, 7)'))->get()->pluck('total_ligne_ht', 'date')->toArray();
		$avoir_achat = \DB::table('avoir_achat_lignes')->selectRaw('LEFT(date,7) as date , sum(tarif * quantite * (100 - remise) / 100 * remise_globale_ligne) as total_ligne_ht')->join('avoir_achat', 'avoir_achat_lignes.document_id', '=', 'avoir_achat.id')->whereIn('avoir_achat_lignes.article_id', $articles)->whereBetween(\DB::raw('left(date, 7)'), [$date_debut,$date_fin])->groupBy(\DB::raw('left(date, 7)'))->get()->pluck('total_ligne_ht', 'date')->toArray();
		
		$value = modele('budget_valeur')->where('id_budget_poste',$rubrique->id)->get()->keyBy('date');

		for($i = 0 ; $i < count($dates['dates']) ; $i++){
			
			$table = 1;
			
			if(!isset($vente[$dates['dates'][$i]['periode']])){
				
				$vente[$dates['dates'][$i]['periode']] = 0;
			}
			if(!isset($achat[$dates['dates'][$i]['periode']])){
				
				$achat[$dates['dates'][$i]['periode']] = 0;
			}
			if(!isset($avoir_vente[$dates['dates'][$i]['periode']])){
				
				$avoir_vente[$dates['dates'][$i]['periode']] = 0;
			}
			if(!isset($avoir_achat[$dates['dates'][$i]['periode']])){
				
				$avoir_achat[$dates['dates'][$i]['periode']] = 0;
			}
			
			$valeur = $vente[$dates['dates'][$i]['periode']] + $avoir_achat[$dates['dates'][$i]['periode']] - $avoir_vente[$dates['dates'][$i]['periode']] - $achat[$dates['dates'][$i]['periode']];
			
			if(isset($value[$dates['dates'][$i]['periode'].'-00'])){
				
				$article_affiche[] = "<p class='text' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->rubrique_id . "_" . $rubrique->id . "_" . $value[$dates['dates'][$i]['periode'].'-00']['id'] . "_article' value='" . $valeur . "'>". $valeur ."</p>";
			}

			else{

				$article_affiche[] = "<p class='text' name='" . $dates['dates'][$i]['periode'] . '_' . $rubrique->rubrique_id . "_" . $rubrique->id . "_article' value='" . $valeur . "'>". $valeur ."</p>";
			}
			
		}
		
		$article_affiche[] = "<p name='article' class='js_total_ligne_" . $rubrique->rubrique_id . "_" . $rubrique->id . "_article'></p>";
		$this->rapport->ligne($article_affiche);
	}

	/*
	 * 
	 * Enregistrement des valeurs de chaques inputs dans la base de donnée
	 * 
	 */

	protected static function enregistrement_valeurs_budget($rapport){
		
		foreach(request()->all() as $request=>$value){
			
			if(preg_match('/\d{4}-\d{2}_\d+_\d+/',$request)){
				
				if($value !== null){
					
					$date = explode('_',$request)[0] . '-00';
					$poste_id = explode('_',$request)[2];
					$id = "";
					
					if(count(explode('_',$request)) >= 4){
						
						$id = explode('_',$request)[3];
					}
					
					$valeur = modele('budget_valeur')->select('valeur')->where('id',$id)->first();
					
					if($value != $valeur['valeur']){
						
						$retour = management('budget_valeur',$id)->enregistre(['id_budget_poste'=>$poste_id , 'valeur' => $value , 'date' => $date]);
					}
				}
			}
			else if(preg_match('/ordre_rubrique_\d+/',$request)){
				
				$ordre = explode('_',$request)[2];
				
				$id = $value;
				
				$retour = management('budget_rubrique',$id)->enregistre(['ordre' => $ordre]);
			}
			else if(preg_match('/ordre_poste_\d+/',$request)){
				
				$ordre = explode('_',$request)[2];
				
				$id = $value;
				
				$retour = management('budget_poste',$id)->enregistre(['ordre' => $ordre]);
			}
		}
	}
}