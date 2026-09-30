<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Models\Champ_libre;

use Illuminate\Http\Request;

class Autocomplete_controller extends Controller {


    /**
     * @param Request $request
     * @param $type_element
     * @return mixed
     *
     * Permet d'aller récupérer les données nécessaires pour la recherche
     */
	function recuperation_donnees(Request $request,$type_element,$type_recherche = null) {

        $methode = '';

        if($type_recherche != null)
            $methode = 'recuperation_pour_module_'.$type_recherche;

        $liste_renvoye = [];

        if($request->has('q')){

            $termes_a_remplacer = array(
                '+','-','<','>','(',')','~','*','"','&','|',':','@'
            );

            $request->q = str_replace($termes_a_remplacer,' ',$request->q);
        }

        if(method_exists($this,$methode)) {

            $liste_renvoye = $this->$methode($request);
        }
		else{

            if($request->has('q')){
                $search = $request->q;

                $data = modele($type_element)
                    ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                    ->get();
            }

            foreach ($data as $element){

                $valeur =  strip_tags(str_replace(array('<br/>', '<br>'), ', ', management($type_element, $element->id)->affiche()));

                $liste_renvoye[$element->id] = $valeur;
            }
        }
        return response()->json($liste_renvoye);
    }

    /**
     * @param Request $request
     * @param $type_element
     *
     * Permet d'aller récupérer les clients
     */
	function recuperation_pour_module_email($request) {
		

        $donnees = [];

        if($request->has('q')){

            $search = $request->q;

            $requete_utilisateur = modele('utilisateur')
                ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                ->where(function($r) {
                    $r->whereNull('type_utilisateur')->orWhereIn('type_utilisateur', array(0,1,2));
                })
                ->selectRaw("id, email as adresse_email, prenom, nom, 'utilisateur' as source_modele")
                ->where('email','!=','')
                ->whereNotNull('email');

            $requete_contact = modele('contact')
                ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                ->selectRaw("id, adresse_email, prenom, nom, 'contact' as source_modele")
                ->where('adresse_email','!=','')
                ->whereNotNull('adresse_email');

            $requete_client = modele('client')
                ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                ->selectRaw("id, adresse_email, prenom, nom, 'client' as source_modele")
                ->where('adresse_email','!=','')
                ->whereNotNull('adresse_email');

            $donnees = \DB::query()
                ->selectRaw(\DB::raw('id,adresse_email,MAX(prenom) as prenom, MAX(nom) as nom, MAX(source_modele) as source_modele'))
                ->from($requete_client->union($requete_utilisateur)->union($requete_contact), 'tmp')
                ->groupBy('adresse_email')
                ->get();

            foreach($donnees as $donnee) {
                $management = management($donnee->source_modele, $donnee->id);
                $table_libre = table_libre($management->_type_element);

                $genere_affichage = function($fonction_affichage, $type_affichage) use ($management, $table_libre) {
                    $variables = $management->recupere_variable('#', '#', $table_libre->$type_affichage);
                    $email_dans_chaine = in_array('adresse_email', $variables);
                    return [
                        'affichage' => $management->$fonction_affichage(),
                        'email_dans_chaine' => $email_dans_chaine
                    ];
                };

                $donnee->affichage_recherche = $genere_affichage('affiche_lien_pour_recherche', 'affichage_recherche');
                $donnee->affichage_select = $genere_affichage('affichage_pour_select', 'affichage_pour_select');
            }
        }

        return $donnees;
    }

    /**
     * @param Request $request
     * @param $type_element
     *
     * Permet d'aller récupérer les fournisseurs
     */
	function recuperation_fournisseur($request) {

        $donnees = [];

        if($request->has('q')){

            $search = $request->q;

            $requete_utilisateur = modele('utilisateur')
                ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                ->where('type_utilisateur', 1)
                ->select('id','email as adresse_email','prenom','nom')
                ->where('email','!=','')
                ->whereNotNull('email');

            $donnees = modele('fournisseur')
                ->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . $search . "*\" In BOOLEAN MODE)")
                ->select('id','adresse_email','nom')
                ->where('adresse_email','!=','')
                ->whereNotNull('adresse_email')
                ->union($requete_utilisateur)
                ->get();
        }

        return $donnees;
    }
}
