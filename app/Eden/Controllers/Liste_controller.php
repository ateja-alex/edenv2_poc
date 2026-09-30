<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Managements\Rapports\Rapports_management;

use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre_filtre_enregistre;

use Illuminate\Http\Request;

class Liste_controller extends Controller
{

    /**
     *
     * Affiche la liste via un type element, en allant chercher la liste libre standard
     *
     */
    public function afficher($type_element, $id_filtre = false, $kanban = false, $parametres_calculs_kanban = array()) {

        temps_execution("debut controleur liste");


		if (!empty(moi_extranet()))
            if (Table_libre::where('type_element', $type_element)->where('acces_extranet', 1)->first() == null)
                return redirect()->route('extranet.acces_restreint');

		$nom_page = '<strong>' . table_libre($type_element)->element . '</strong> (Liste)';

        $url = 'eden/liste/' . $type_element;

        enregistrer_log_historique($url, $nom_page);

        // on va chercher la liste libre
        $liste = Liste_libre::where('type_element', $type_element)->where(function ($requete) {
            $requete->where('id_rapport', '')->orWhereNull('id_rapport');
        })->first();

        // on n'a pas trouvé la liste libre
        if ($liste === null) {

            // Si on est éditeur, on est redirigé vers la page Zoom
            if(editeur()) {
                return redirect()->route('parametrage.table_libre.zoom', [$type_element])->with('error', traduction('messages.php.liste.liste_introuvable',null,[$type_element]));

            // Sinon, vers la page d'accueil
            } else {

                // Si la page précédente n'est pas la page de login, et qu'on vient d'Eden, on redirige vers la page précédente
                $url_precedente = \URL::previous();
                if (strpos($url_precedente, 'eden/login') === false && strpos($url_precedente, '/eden/') !== false)
                    return redirect()->back()->with('error', traduction('messages.php.liste.liste_introuvable_utilisateur'));

                // Sinon, on redirige vers la page d'accueil
                return redirect()->route('base_eden.accueil.index')->with('error', traduction('messages.php.liste.liste_introuvable_utilisateur'));
            }
        }

        $parametres['kanban'] = $kanban;

        if (!empty($kanban)){

            // On vérifie si on est en mode corbeille
            if (isset($this->mode_corbeille) && $this->mode_corbeille == true) {

                $donnees['options_liste']['seulement_inactif'] = true;
            } else {

                $this->mode_corbeille = false;
                $donnees['options_liste']['seulement_inactif'] = false;
            }

            if (!empty($this->indicateur_source))
                $donnees['options_liste']['indicateur_source'] = $this->indicateur_source;

            if(isset($parametres_calculs_kanban['unite']))
                $donnees['options_liste']['kanban_unite'] = $parametres_calculs_kanban['unite'];

            if(($parametres_calculs_kanban['type'] ?? null) == 'somme')
                $donnees['options_liste']['kanban_colonne_somme'] = $parametres_calculs_kanban['colonne'];

            if(($parametres_calculs_kanban['type'] ?? null) == 'nombre')
                $donnees['options_liste']['kanban_colonne_count'] = true;

            $donnees['type_element'] = $liste->type_element;

            $donnees['id_liste'] = $liste->id;

            $donnees['kanban'] = $kanban;
		}

        else{

            // On vérifie si on est en mode corbeille
            if (isset($this->mode_corbeille) && $this->mode_corbeille == true) {

                $donnees['options_liste']['seulement_inactif'] = true;
            } else {

                $this->mode_corbeille = false;
                $donnees['options_liste']['seulement_inactif'] = false;
            }

            if (!empty($this->indicateur_source))
                $donnees['options_liste']['indicateur_source'] = $this->indicateur_source;

            $donnees['type_element'] = $liste->type_element;

            $donnees['id_liste'] = $liste->id;

            $donnees['modele_par_defaut'] = service('modele_par_defaut')->recupere($type_element);
        }

		// on retourne la vue générique
        return view('eden::listes', $donnees);
    }

    /**
     *
     * Affiche la liste via un type element, en allant chercher la liste libre standard
     *
     * Le type d'affichage ici est kanban (comme trello)
     *
     */
    public function afficher_kanban($type_element, $kanban = false, $id_filtre = false)
    {

        return $this->afficher($type_element, $id_filtre, $kanban);

    }

    /**
     *
     * Affiche la liste via un type element, en allant chercher la liste libre standard
     *
     * Le type d'affichage ici est kanban (comme trello) + une somme dans chaque entête de colonne
     *
     */
    public function afficher_kanban_avec_somme($type_element, $kanban, $colonne_somme, $kanban_unite = false)
    {

		$parametres_calculs_kanban = array(

			'type' => 'somme',
			'colonne' => $colonne_somme,
			'unite' => $kanban_unite,
		);

        return $this->afficher($type_element, false, $kanban, $parametres_calculs_kanban);
    }

    /**
     *
     * Affiche la liste via un type element, en allant chercher la liste libre standard
     *
     * Le type d'affichage ici est kanban (comme trello) + une somme dans chaque entête de colonne
     *
     */
    public function afficher_kanban_avec_nombre($type_element, $kanban, $kanban_unite = false)
    {

		$parametres_calculs_kanban = array(

			'type' => 'nombre',
			'unite' => $kanban_unite,
		);

		return $this->afficher($type_element, false, $kanban, $parametres_calculs_kanban);
    }

    /**
     *
     * Affiche une liste depuis un indicateur, donc avec des filtres préenregistrés
     *
     */
    public function afficher_avec_indicateur($id_rapport_indicateur){
        
        $rapport = Rapport_libre::where('id_rapport',$id_rapport_indicateur)->first();

        $filtres_pour_fiche = [];

        if(isset(request()->filtres_pour_fiche))
            $filtres_pour_fiche = unserialize(base64_decode(request()->filtres_pour_fiche));

        $this->indicateur_source = json_encode([
            'id_rapport' => $id_rapport_indicateur,
            'filtres_pour_fiche' => $filtres_pour_fiche
        ]);

        return $this->afficher($rapport->type_element);
    }

    /**
     *
     * Retourne la liste d'ids pour une liste donnée
     *
     * On passe par la même méthode "afficher" que pour l'affichage, mais on récupère juste les ids
     * Avant les ids étaient récupérés en JS via la méthode afficher(),
     * Mais sur des listes de dizaines de milliers de lignes, ça crée des problèmes de mémoire
     * Sachant qu'en utilisation normale, on ne travaille jamais avec 10K+ lignes (pour envoyer un mail par exemple)
     *
     */
    public function recupere_ids(Request $options_liste,$id_liste)
    {
        // on va chercher les paramètres enregistrés
        $parametres = $options_liste->all();

        $liste = Liste_libre::find($id_liste);

        $management = liste($liste->type_element);

        $parametres['recupere_ids'] = true;

        if(isset($parametres['seulement_inactif']) && $parametres['seulement_inactif'] == 'false')
            unset($parametres['seulement_inactif']);

        // on récupère la liste
        $ids = $management->recupere_liste($id_liste, $parametres);

        return response()->json($ids);

    }

    /**
     *
     * Exporte la liste au format Excel (POST)
     *
     */
    public function exporter(Request $options_liste, $id_liste, $type_export = 'basique') {
        define('export_en_cours', 'excel');

        $parametres = $options_liste->all();
        $limite_export = fonctionnalite('limite_export_differe');

        if(isset($parametres['seulement_inactif']) && $parametres['seulement_inactif'] == 'false')
            unset($parametres['seulement_inactif']);

        if(empty($parametres['avec_inactifs']))
            $parametres['avec_inactifs'] = 0;

        $liste = Liste_libre::find($id_liste);

        $management = liste($liste->type_element, $liste->id_rapport ?? false);

        $rapport = rapport($liste->id_rapport, false);

        if ($rapport !== false)
            $parametres = $rapport->retouche_parametres($parametres);

        $management->retourne_seulement_le_nombre_de_lignes = true;
        $nombres_elements = $management->recupere_liste($id_liste, $parametres, 'export', $type_export);
        $management->retourne_seulement_le_nombre_de_lignes = false;

        if(!empty(moi())) {
            $type_element_createur = 'utilisateur';
            $element_id_createur = moi()->id;
        }
        else if(!empty(moi_extranet())){
            $type_element_createur = 'contact';
            $element_id_createur = moi_extranet()->contact_selectionne->id;
        }

        $nom_fichier = 'export_' . $liste->type_element . '_' . $id_liste . '_' . $type_element_createur . '_' . $element_id_createur . '_' . date('Ymd_His', time());
        
        if(in_array($type_export,['sur_mesure_pdf', 'pdf']))
            $extension = 'pdf';
        else if(in_array($type_export,['sur_mesure_csv', 'csv']))
            $extension = 'csv';
        else
            $extension = 'xlsx';

        if(file_exists(storage_path('app/public/exports/' . $nom_fichier . '.' . $extension)))
            unlink(storage_path('app/public/exports/' . $nom_fichier . '.' . $extension));
        
        $service = service('export');
        $taille_chunk = $service->taille_chunk[$extension];
        $parametres['nombre_par_page'] = $taille_chunk;

        // on vérifie si on doit un réaliser un export différé ou pas, on place la condition ici pour récupérer les paramètres à jour
        if ($nombres_elements > $limite_export) {

            // on enregistre les différents paramètres
            $modifications = [
                'type_export' => $type_export,
                'id_liste' => $id_liste,
                'fichier' => $nom_fichier,
                'termine' => 0,
                'parametres' => json_encode($parametres),
                'page' => 1,
                'nombre_elements' => $nombres_elements,
                'type_element_createur' => $type_element_createur,
                'element_id_createur' => $element_id_createur,
            ];

            $management_export = management('export');
            $management_export->enregistre($modifications);

            $nombre_par_fichier = $extension === 'pdf' ? 10000 : 100000;
            $nombre_fichiers = 1;
            $page_en_cours = 1;
            $classe_export = queue('export');
            $chaines_exports = [];

            if($nombres_elements > $nombre_par_fichier){

                $nombre_fichiers = intval(ceil($nombres_elements / $nombre_par_fichier));
                $reste_nombre_elements = $nombres_elements - $taille_chunk;
                $suffixe_nom_fichier = '_partie_1';
                $extension = 'zip';

                for($i = 1; $i <= $nombre_fichiers; $i++){

                    $suffixe_nom_fichier = '_partie_' . $i;

                    if(file_exists(storage_path('app/public/exports/' . $nom_fichier . $suffixe_nom_fichier . '.' . $extension)))
                        unlink(storage_path('app/public/exports/' . $nom_fichier . $suffixe_nom_fichier . '.' . $extension));

                    if($i === 1)
                        $reste_nombre_fichier = $nombre_par_fichier - $taille_chunk;
                    else
                        $reste_nombre_fichier = $nombre_par_fichier;

                    while($reste_nombre_fichier > 0 && $reste_nombre_elements > 0){

                        $page_en_cours++;
                        $chaines_exports[] = new $classe_export($management_export->modele->id,$page_en_cours, $suffixe_nom_fichier);
                        $reste_nombre_fichier -= $taille_chunk;
                        $reste_nombre_elements -= $taille_chunk;
                    }
                }
            }
            else if($nombres_elements > $taille_chunk){

                $reste_nombre_elements = $nombres_elements - $taille_chunk;

                while($reste_nombre_elements > 0){

                    $page_en_cours++;
                    $chaines_exports[] = new $classe_export($management_export->modele->id,$page_en_cours);
                    $reste_nombre_elements -= $taille_chunk;
                }
            }

            $classe_export_mail = queue('envoi_mail_fin_export');
            $chaines_exports[] = new $classe_export_mail($management_export->modele->id, $nom_fichier . '.' . $extension, $nombre_fichiers);
            
            if(!empty($chaines_exports))
                queue('export')::withChain($chaines_exports)->dispatch($management_export->modele->id,1, $nombres_elements > $nombre_par_fichier ? '_partie_1' : '');
            else
                queue('export')::dispatch($management_export->modele->id,1);

            return response()->json(true);
        }
        
        $nombre_boucles = intval(ceil($nombres_elements / $taille_chunk));
        
        for($i = 1; $i <= $nombre_boucles;$i++){

            $parametres['page'] = $i;

            if(isset($management->resultat_cree_requete))
                unset($management->resultat_cree_requete);

            $donnees = $management->recupere_liste($id_liste, $parametres, $taille_chunk, $type_export);
            
            if(in_array($extension,['csv','xlsx']))
                $service->exporter_xlsx_csv($donnees, $nom_fichier . '.' . $extension);
            else
                $service->exporter_pdf($donnees, $nom_fichier);
        }

        return response()->json(['url_fichier' => asset('storage/exports/'.$nom_fichier . '.' . $extension)]);
    }

    /**
     *
     * Exporte la liste au format Excel (POST)
     *
     */
    public function exporter_modele(Request $options_liste, $id_liste, $id_modele = null)
    {
        define('export_en_cours', 'excel');

        // @note Frédéric : je remplace le recupere_parametres par $options_liste->all(); sinon
        // certain paramètres n'étaient pas récupérés comme les filtres pour fiches
        // $parametres = Rapports_management::recupere_parametres('liste_'.$id_liste);
        $parametres = $options_liste->all();

        if (isset($parametres['seulement_inactif']) && $parametres['seulement_inactif'] == 'false')
            unset($parametres['seulement_inactif']);

        if (empty($parametres['avec_inactifs']))
            $parametres['avec_inactifs'] = 0;

        $liste = Liste_libre::find($id_liste);

        $parametres['uniquement_requete'] = true;

        $requete = liste($liste->type_element)->recupere_liste($id_liste, $parametres);

        $management_export = management('export_compta_modele', $id_modele);

        return $management_export->export($requete);
    }

	/**
	 *
	 * Actualise les données de la liste en fonction des filtres, pagination, recherche...
	 *
	 */
	public function actualiser(Request $options_liste, $id_liste) {

		$liste = Liste_libre::find($id_liste);

		$management = liste($liste->type_element, $liste->id_rapport);

		// on enregistre les paramètres
		Rapports_management::enregistre_parametres('liste_'.$id_liste, $options_liste->only(
            'page', 
            'filtres', 
            'tri',
            'direction_tri'));

		list($parametres, $nombre_par_page) = $this->parametres_recuperation_liste($liste, $options_liste->all());

		$donnees = $management->recupere_liste($id_liste, $parametres, $nombre_par_page);

		return response()->json($donnees);
    }

	/**
	 *
	 * Construit les paramètres et le nombre par page attendus par recupere_liste
	 *
	 */
	private function parametres_recuperation_liste($liste, $parametres) {

		$nombre_par_page = false;

		if(array_key_exists('nombre_par_page', $parametres))
			$nombre_par_page = $parametres['nombre_par_page'];

		$kanban_demande = $parametres['kanban'] ?? null;

        if(isset($parametres['seulement_inactif']) && $parametres['seulement_inactif'] == 'false')
            unset($parametres['seulement_inactif']);

		// on écrase les filtres si nécessaire
		if(!empty($parametres['filtre_a_appliquer'])) {

			$parametres['filtres'] = json_decode(Liste_libre_filtre_enregistre::find($parametres['filtre_a_appliquer'])->filtres, true);
		}

		$infos_rapport = Rapport_libre::where('id_rapport', $liste->id_rapport)->first();

		// on gère le cas de la carte gmap
		if($infos_rapport !== null) {

			if(!empty($infos_rapport->visualisation_carte)) {

				$parametres['visualisation_carte'] = true;
				$nombre_par_page = 1000;
			}

			if(!empty($infos_rapport->kanban)) {

				$parametres['kanban'] = $infos_rapport->kanban;
                if(empty($parametres['kanban_colonnes']))
				    $parametres['kanban_colonnes'] = $infos_rapport->kanban_colonnes;
				$nombre_par_page = 1000;

                // le kanban vertical a un tri cliquable sur ses colonnes, on ne l'écrase pas avec le tri_kanban statique
                if(empty($liste->affichage_kanban_vertical)) {
                    if(!empty($liste->tri_kanban)) {
                        $json = json_decode($liste->tri_kanban);

                        if(!empty($json))
                            $parametres['tri'] = $json;
                    }
                    else
                        $parametres['tri'] = null;
                }
			}
		}

        if(!empty($liste->lignes_par_page)) {

            $nombre_par_page = $liste->lignes_par_page;
        }

		if(!empty($kanban_demande)) {

			$parametres['kanban'] = $kanban_demande;
			$parametres['kanban_colonne_somme'] = $parametres['kanban_colonne_somme'] ?? null;
			$parametres['kanban_unite'] = $parametres['kanban_unite'] ?? null;
			$parametres['nombre_colonnes_garder'] = $parametres['nombre_colonnes_garder'] ?? null;

			if(empty($liste->lignes_par_page))
				$nombre_par_page = 50;
		}

		if(!empty($parametres['recuperer_les_ids_uniquement']))
            $parametres['recupere_ids'] = true;

		return [$parametres, $nombre_par_page];
	}

	/**
	 *
	 * Récupère les lignes d'une liste directement depuis son initialisation, pour éviter un second appel
	 *
	 */
	private function recuperation_lignes_initialisation($liste, $liste_libre, $retour) {

		$parametres = $retour['options_liste'] ?? [];

		$parametres['filtres_pour_fiche'] = $liste['filtres_pour_fiche'] ?? null;

		$parametres['filtres_affichage'] = collect($retour['filtres'] ?? [])->map(function($filtre){

			$filtre = (object) $filtre;

			return [
				'id' => $filtre->id ?? null,
				'nom_sql' => $filtre->nom_sql ?? null,
				'type_element' => $filtre->type_element ?? null,
				'champ_de_liaison' => $filtre->champ_de_liaison ?? null,
			];

		})->values()->toArray();

		Rapports_management::enregistre_parametres('liste_'.$liste_libre->id, collect($parametres)->except('enregistrement_filtre_nom', 'enregistrement_filtre_id', 'enregistrement_filtre_liste_id', 'indicateur_source', 'recherche_avancee', 'filtres_appliques')->toArray());

		list($parametres, $nombre_par_page) = $this->parametres_recuperation_liste($liste_libre, $parametres);

		$id_rapport = !empty($liste_libre->id_rapport) ? $liste_libre->id_rapport : null;

		return liste($liste_libre->type_element, $id_rapport)->recupere_liste($liste_libre->id, $parametres, $nombre_par_page);
	}

    /**
     *
     * Permet d'enregistrer les filtres sur une liste
     *
     */
    public function enregistrer_filtres()
    {

        if (!empty(request()->enregistrement_filtre_id)) {

            $filtre = Liste_libre_filtre_enregistre::find(request()->enregistrement_filtre_id);
        } else {

            $filtre = new Liste_libre_filtre_enregistre;
        }


        $filtre->liste_id = request()->enregistrement_filtre_liste_id;
        $filtre->utilisateur_id = id_utilisateur;
        $filtre->nom = request()->enregistrement_filtre_nom;
        $filtre->filtres = json_encode(request()->filtres);

        $filtre->save();

        // on va chercher la liste des filtres, et on les retournes
        return response()->json(Liste_libre_filtre_enregistre::where('liste_id', request()->enregistrement_filtre_liste_id)->where('utilisateur_id', id_utilisateur)->get());
    }

    /**
     *
     * Permet de supprimer un filtre enregistré sur une liste
     *
     */
    public function supprimer_filtre() {

        Liste_libre_filtre_enregistre::find(request()->id)->delete();

        // on va chercher la liste des filtres, et on les retournes
        return response()->json(Liste_libre_filtre_enregistre::where('liste_id', request()->enregistrement_filtre_liste_id)->where('utilisateur_id', id_utilisateur)->get());
    }

    /**
     *
     * Permet d'afficher la liste d'un type_element mis dans la corbeille
     *
     */
    public function afficher_corbeille($type_element) {

        $this->mode_corbeille = true;

        return $this->afficher($type_element);

    }

    /**
     *
     * Permet de récupérer les filtres en passant par un appel ajax
     *
     */
    public function recuperer_filtres(Request $formulaire, $type_element) {

        $formulaire = $formulaire->all();

        $id_liste = $formulaire['id_liste'];

        $liste_libre = Liste_libre::where('id',$id_liste)->first();

        $id_rapport = !empty($liste_libre->id_rapport) ? $liste_libre->id_rapport : null;

        $liste_management = liste($liste_libre->type_element,$id_rapport);

        $options_liste = [];

        if(isset($formulaire['options_liste']))
            $options_liste = $formulaire['options_liste'];

        $champs_libres = Champ_libre::where(function($where){
             $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
         })->get()->groupBy('type_element');

        $liste_management->champs_libres = $champs_libres;

        $retour = $liste_management->initialisation_liste($type_element,$id_liste,$options_liste,$liste_libre);

        return response()->json($retour);
    }

    /**
     * @param Request $formulaire
     *
     * Fonction permettant d'inialiser plusieurs avec un appel
     *
     */
    public function initialisation_listes(Request $formulaire){

        $formulaire = $formulaire->all();

        $listes = $formulaire['donnees'];

        $retours = [];

        $listes_libres = Liste_libre::whereIn('id', collect($listes)->pluck('id'))->get()->keyBy('id');

        $champs_libres = Champ_libre::where(function($where){
             $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
         })->get()->groupBy('type_element');

        foreach($listes as $liste){

            $id_liste = $liste['id'];

            $liste_libre = $listes_libres[$id_liste];

            $type_element = $liste_libre->type_element;

            $id_rapport = !empty($liste_libre->id_rapport) ? $liste_libre->id_rapport : null;

            $liste_management = liste($liste_libre->type_element,$id_rapport);
            $liste_management->champs_libres = $champs_libres;

            $options_liste = [];

            if(!empty($liste['options_liste']))
                $options_liste = $liste['options_liste'];

            $retour = $liste_management->initialisation_liste($type_element,$id_liste,$options_liste,$liste_libre);

            if(!empty($liste['recuperer_lignes'])) {

                try {
                    $retour['lignes_liste'] = $this->recuperation_lignes_initialisation($liste, $liste_libre, $retour);
                }
                catch(\Throwable $erreur) {
                    unset($retour['lignes_liste']);
                }
            }

            $retours[$liste['id']] = $retour;

        }

        return response()->json($retours);
    }

    public function calculs_elements_selectionnes($id_liste){

        $liste = Liste_libre::find($id_liste);

        $management = liste($liste->type_element, $liste->id_rapport);

        $parametres = request()->all();

        $calculs = $management->recupere_informations_calculs($id_liste, $liste->type_element, $parametres);
        
        return response()->json($calculs);
    }
}
