<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Calcul_gescom_management;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Inventaires_tournants_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
    public function genere($ajax = false) {
        
        // Traitement qu'on effectue lorsque l'utilisateur enregistre ses données

        if(isset(request()->stock_reel)) {

			$donnees = request()->stock_reel;

			foreach($donnees as $article =>  $stock_reel) {

				// Si l'utilisateur n'a rien saisie on ne traite pas l'article
				if($stock_reel == null)
					continue;

				list($osef, $article_id, $entrepot_id) = explode('_', $article);

				$management_article = management('article', $article_id);

                $management_article->mis_a_jour_date_inventaire();

				$stock_theorique = $management_article->stock_actuel($entrepot_id);

				$ecart_stock = $stock_reel - $stock_theorique;

				// Si on a pas d'écart, on ne traite pas l'article
				if($ecart_stock == 0) 
					continue;
				
				$management_mouvement_stock = management('mouvement_de_stock');

				// Si l'écart est positif on ajoute du stock sinon, on le supprime
				$modifications_mouvement_stock = [

					'date' => date('Y-m-d'),
					'entrepot_id' => $entrepot_id,
					'article_id' => $article_id,
					'quantite' => $ecart_stock,
					'type_de_mouvement' => 0
				];

				$management_mouvement_stock->enregistre($modifications_mouvement_stock);
			}
		}
        
        $entrepot = $this->entrepot($this->rapport);

        $fournisseur = $this->fournisseur($this->rapport);

        $famille_choisi = $this->familles_de_produits($this->rapport);

        $this->export_pdf($this->rapport);
        
        $titres = array(
            ucfirst(table_libre('article')->element),
            traduction('rapport.inventaires_tournants.colonnes.stock_theorique'),
            traduction('rapport.inventaires_tournants.colonnes.stock_inventaire_reel')
        );

        $this->rapport->titres($titres);

        $this->enregistrer($this->rapport);

        if($famille_choisi !=[] && $famille_choisi!="false"){
            $familles = modele('famille')
                ->whereIn('id',$famille_choisi)
                ->orderBy('famille.nom')
                ->select('famille.id', 'famille.nom')->get();
        }

        else {
            $familles = modele('famille')
                ->join('article', 'famille.id', 'article.famille_id')
                ->where(['article.stockable' => 1])
                ->zero_ou_null('article.inactif')
                ->orderBy('famille.nom');

            if ($fournisseur != "null" && $fournisseur != "") {
                $familles->join('article_fournisseur', 'article_fournisseur.article_id', 'article.id')
                    ->where('article_fournisseur.fournisseur_id', $fournisseur);
            }

            $familles = $familles->distinct()->select('famille.id', 'famille.nom')
                ->get();

        }

        $articles_famille =[];
        
        foreach($familles as $famille) {

            $management_famille = management('famille',$famille->id);

            $familles_parent = $management_famille->liste_familles_parent($famille->id);

            $nom = '';
            foreach ($familles_parent as $cle => $famille_id){
                if($cle == 0) {
                    $nom = modele('famille',$famille_id)->nom;
                }
                else{
                    $nom = modele('famille',$famille_id)->nom.' > '.$nom;
                }
            }

            // On récupère les articles qui sont stockable et qui appartienent à l'entrepot et au fournisseur
            $requete_initiale = modele('article')
                ->where(['article.stockable' => 1])
                ->where('article.famille_id',$famille->id)
                ->orderBy('article.derniere_date_inventaire');

            if ($fournisseur != "null" && $fournisseur != "") {
                $requete_initiale->join('article_fournisseur', 'article_fournisseur.article_id', 'article.id')
                    ->where('article_fournisseur.fournisseur_id', $fournisseur);
            }

            $articles_famille[$nom] = $requete_initiale->select('article.*')->groupBy('article.id')->get();

        }


        if(!empty($articles_famille)) {
            ksort($articles_famille, SORT_LOCALE_STRING);
        }

        foreach($articles_famille as $famille => $articles) {
			
			$infos_stocks = service('stocks')->details_stocks_par_article($articles->pluck('id')->toArray());
			
			$this->rapport->sous_titre($famille);
            foreach ($articles as $article) {
				
                $ligne = [];
                $management_article = management('article', $article->id);
				
                $ligne['article'] = $management_article->affiche_lien();
                $ligne['stock_theorique'] = $infos_stocks[$article->id]['stock_actuel']['par_entrepot'][$entrepot]['total'];

                //$ligne['stock_reel'] = '<input type="text" name="article_' . $article->id . '_' . $entrepot . '" class="js_stock_reel_' . $this->rapport->id_rapport . '" value="" style="width: 50px;" />';
                $ligne['stock_reel'] = '<span @click="afficher_detail_inventaire_tournant(' . $article->id . ',' . $entrepot . ')" class="fas fa-pencil-alt" style="cursor:pointer;"></span>';
                
				$this->rapport->ligne($ligne);
            }
        }
		

		return $this->rapport->genere($ajax);
    }
	
}