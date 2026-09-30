<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Models\Panier_detail;
use App\Eden\Models\Article_declinaison;
use App\Eden\Managements\Elements\Erreur_management ;
use App\Eden\Models\Element_piece_jointe;
class Panier_controller extends Controller {


    /**
     * 
     * Affiche le panier
     * 
     * @return Response
     */
    public function panier() {
        
        $panier = management('panier')->recupere();     
        $panier->informations_panier();
		
		return view('eden::ecommerce.commande.panier', ['panier' => $panier]);
    }


    /**
     * 
     * Vide le panier
     * 
     * @return Response
     */
    public function vider_panier() {
        
        $panier = management('panier');
        $panier = $panier->recupere();
        $panier->desactive_panier();
        
        return redirect()->route('ecommerce.panier');
    }

	/**
	 * 
	 * 
	 * 
	 */
    public function retrouver_panier($client_id, $id) {
		
		$panier_enregistre = modele('panier_enregistre')->where('client_id', $client_id)->where('id', $id)->first();
		
		if($panier_enregistre === null)
			exit;
		
		// on logue le client
		if(session()->get('utilisateur_eden_ecommerce') !== $client_id) {
			
			// on connecte l'utilisateur automatiquement
			session()->put('utilisateur_eden_ecommerce', $client_id);
		}
		
		// on va chercher le management
		$management = management('panier_enregistre', $id);
		
		$management->recupere_panier();
		
		return redirect()->route('panier');
	}

	/** 
	 *
	 * 
	 * 
	 */
	public function affiche_page_adresse() {

		$panier = management('panier')->recupere();     
        $panier->informations_panier();

		$client_id = session()->get('utilisateur_eden_ecommerce');
        $client = management('client', $client_id);

		$retour = $panier->verification_avant_validation();
		
        if ( $retour !== true ) return $panier->redirection_en_cas_d_erreur($retour); 

        $adresses = modele('adresse')->where('client_id', session('utilisateur_eden_ecommerce'))->get();

        return view('eden::ecommerce.commande.adresse', ['panier' => $panier, 'client' => $client, 'adresses' => $adresses]);
    }
    
    public function enregistre_adresse(Request $request) {

        $data = $request->all();
        $panier = management('panier')->recupere();     
        $retour = $panier->enregistre_adresse($data);
        if ( $retour !== true ) return redirect()->route('ecommerce.adresse')->with('erreur', $retour);
        
        return redirect()->route('ecommerce.paiement');
    }

	/** 
	*
	* Affiche pas de paiement
	*/
	public function affiche_page_paiement() {

        $panier = management('panier')->recupere();     
        $panier->informations_panier();

        
        return view('eden::ecommerce.commande.paiement', ['panier' => $panier,'frais_de_livraison' => $panier->frais_de_livraison]);
	}

	public function paiement(Request $request) {


		$panier = management('panier');

        $panier = $panier->recupere();
        $panier_a_afficher = $panier->informations_panier();
        $articles_dans_le_panier = $panier->articles_dans_panier;


        // à déplacer dans un management

        $documents_par_facture = [];

        /*foreach($liste_des_documents as $liste) {

            $document = explode('_', $liste);

            if($document[0] == $panier->modele->id) {

                if($document[2] == 'pdfprod') {

                    $documents_par_facture[$document[1]]['pdfprod'] = $liste;
                }

                if($document[2] == 'xmlprod') {

                    $documents_par_facture[$document[1]]['xmlprod'] = $liste;
                }
            }
        }*/

        /*foreach($documents_par_facture as $document_par_facture) {

            $donnees = [

                'facture_id' => $panier->modele->id,//changer par la facture id (en local stripe bloque)
                'prod_pdf' => $document_par_facture['pdfprod'],
                'prod_xml' => $document_par_facture['xmlprod'],
            ];

            management('document_pour_facture_vente')->enregistre($donnees);
        }*/


        $fichiers = scandir ('storage');
        $pj_a_enregistrer = [];

        foreach($fichiers as $fichier) {

            $image = explode('_', $fichier);


            if($image[0] == 'pj'.$panier->modele->id) {

                $pj_a_enregistrer[] = 'public/'.$fichier;
            }
        }

        
        $client_id = session()->get('utilisateur_eden_ecommerce');
        $client = management('client', $client_id);

		$donnees = $client->donnees_pour_ecommerce_panier_valide();
		
		list($retour, $charge) = $panier->effectue_paiement($panier, $request->all());

        if ( $retour !== true ) return redirect()->route('ecommerce.panier')->with('erreur', $retour);
		

		$facture = $panier->enregistre_dans_erp();
		
		if ( is_string($facture) ) management('erreur')->enregistre(['titre' => traduction('messages.php.ecommerce.panier.erreur_creation_commande'), 'message_erreur' => $facture, 'app_env' => env('APP_ENV'), 'url' => $_SERVER['APP_URL'].$_SERVER['REQUEST_URI'], 'origine' => '2']);

        $facture->valide();

        foreach($articles_dans_le_panier as $article_dans_le_panier) {

            $donnees = [

                'facture_id' => $facture->modele->id,//changer par la facture id (en local stripe bloque)
                'prod_pdf' => $article_dans_le_panier->pdf_prod_recto.'%'.$article_dans_le_panier->pdf_prod_verso,
                'prod_xml' => $article_dans_le_panier->xml,
                'prod_bat' => $article_dans_le_panier->pdf_recto.'%'.$article_dans_le_panier->pdf_verso,
                'texte_recto' => $article_dans_le_panier->texte_recto,
                'texte_verso' => $article_dans_le_panier->texte_verso,
                'article_id' => $article_dans_le_panier->article_id,
            ];

            management('document_pour_facture_vente')->enregistre($donnees);

        }


        foreach($pj_a_enregistrer as $pj){

            $donnees = [
                'type_element' => 'facture_vente',
                'element_id' => $facture->modele->id,
                'chemin' => $pj,
                'titre' => 'Image'
            ];

            Element_piece_jointe::create($donnees);


        }
		
		
        
        $panier->enregistre_paiement($facture, management('client', session()->get('utilisateur_eden_ecommerce')), $charge);

        $panier->mail_validation_commande($facture);

        // Une erreur au retour ne devrait pas arriver ?
        $panier->informations_panier();

        $panier->desactive_panier();

        return view('eden::ecommerce.commande.valide', [ 'donnees' => $donnees, 'panier' => $panier, 'frais_de_livraison' => $panier->frais_de_livraison]);
	}


    /**
     * 
     * Ajoute un produit au panier via ajax
     * 
     * @return Response
     */
    public function ajax_ajoute_produit_au_panier(Request $formulaire) {
		
		$panier = management('panier')->recupere();

		if(empty($formulaire->get('declinaison_id'))) {
			
			$tarif = management('article', $formulaire->article_id)->modele->tarif;
		}
		else {
			
			$declinaison = Article_declinaison::find($formulaire->get('declinaison_id'));
			
			$tarif = $declinaison->tarif;
		}
		
		$panier->ajoute_produit($formulaire, $tarif);
		
		$panier->informations_panier();
		
		return response()->json($panier);
    }

    /**
     * 
     * Ajoute un produit au panier et redirige vers la fiche article
     * 
     * @return Response
     */
    public function ajoute_produit_au_panier(Request $formulaire) {
		
        $panier = management('panier')->recupere();
        
        $article = modele('article', $formulaire->article_id);
        $taille_pour_article = false;


        if($article->taille = 1) 
            $taille_pour_article = true;
        
		if(empty($formulaire->get('declinaison_id'))) {
			
			$tarif = management('article', $formulaire->article_id)->modele->tarif;
		}
		else {
            
            // si c'est un tableau, c'est que c'est une déclinaison multiple
            if(is_array($formulaire->get('declinaison_id'))) {

                $where_parametres = [];

                foreach($formulaire->get('declinaison_id') as $index => $declinaison) {
        
                    $index = ($index + 1);
                    $where_parametres['valeur_declinaison_'.$index] =  $declinaison;
                }

                $where_parametres['article_id'] = $formulaire->article_id;

                $declinaison = modele('article_declinaison')->where($where_parametres)->first();
                
                $tarif = $declinaison->tarif;

            }
            else {
                $declinaison = Article_declinaison::find($formulaire->get('declinaison_id'));
			
                $tarif = $declinaison->tarif;
    
            }
        }
        
        

        $panier->ajoute_produit($formulaire, $tarif, $taille_pour_article);
		
		
		$panier->informations_panier();
		
		if(empty($formulaire->get('url_retour'))) {
			
			return redirect()->route('ecommerce.url_ecommerce', $article->url)->with('information', traduction('messages.php.ecommerce.panier.article_ajoute'));
		}
		
		return redirect()->route('ecommerce.url_ecommerce', $formulaire->get('url_retour'))->with('information', traduction('messages.php.ecommerce.panier.article_ajoute'));
    }
    
    /**
     * 
     * Ajoute un produit au panier via ajax
     * 
     * @return Response
     */
    public function ajax_modifie_produit_au_panier(Request $formulaire) {
		
		$panier = management('panier')->recupere();
		
		$panier->modifie_produit($formulaire);
		
		$panier->informations_panier();
		
		return response()->json($panier);
    }

    /**
     * 
     * Valide un panier en commande
     *
     */
    public function valide() {

        $panier = management('panier');

        $panier = $panier->recupere();
		$panier->informations_panier();

        $client_id = session()->get('utilisateur_eden_ecommerce');
        $client = management('client', $client_id);

        $donnees = $client->donnees_pour_ecommerce_panier_valide();


        $retour = $panier->verification_avant_validation();
        if ( $retour !== true ) return redirect()->route('ecommerce.panier')->with('erreur', $retour);

        $retour = $panier->effectue_paiement($panier);
        if ( $retour !== true ) return redirect()->route('ecommerce.panier')->with('erreur', $retour);

		$facture = $panier->enregistre_dans_erp();

		// on enregistre l'erreur
        if(is_string($facture)) {

            $erreur = array(
                
                'titre' => 'Erreur à la création de la commande',
                'message_erreur' => $facture,
                'app_env' => env('APP_ENV'),
                'url' => $_SERVER['APP_URL'].$_SERVER['REQUEST_URI'],
                'origine' => 2,
            );

            management('erreur')->enregistre($erreur);
        }

        $panier->enregistre_paiement($facture);

        // TODO : Envoyer un mail
        $panier->mail_validation_commande($facture);

        // Une erreur au retour ne devrait pas arriver ?

		return view('eden::ecommerce.commande.valide', ['facture' => $facture, 'donnees' => $donnees]);
    }
	
	/**
	 * 
	 * Affiche la page de validation de commande
	 * 
	 */
	public function confirmation_commande() {
		
		return view('eden::ecommerce.commande.valide');
	}


    public function modifie_code_postal_pays(Request $formulaire) {

        if ( isset($formulaire->pays) )         session()->put('pays_actif', $formulaire->pays) ;
        if ( isset($formulaire->code_postal) )  session()->put('code_postal_actif', $formulaire->code_postal) ;
        if ( isset($formulaire->estimation_frais_livraison) )         session()->put('estimation_frais_livraison', $formulaire->estimation_frais_livraison) ;

        $panier = management('panier')->recupere();

		$panier->informations_panier();

		return json_encode(['tarif' => montant(management('panier')->calcule_tarif_livraison()->min('tarif')),'panier' => $panier]) ;
    }
	
}
