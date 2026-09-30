<?php

namespace App\Eden\Managements;

use App\Eden\Managements\Parametrage\Table_libre_management;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Formulaire_valeur_par_defaut;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Rapport_parametre;
use App\Eden\Models\Table_libre;
use App\Eden\Variables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Script_management{

    /**
     * @param $dossier
     *
     * Permet de lancer les scripts
     *
     */
    public static function lancer_scripts($dossier){

        // on va chercher les scripts
        $scripts = [];

        $repertoire_scripts = scandir(app_path('Eden/Migrations/Scripts/'.$dossier));

        foreach($repertoire_scripts as $fichier) {

            if(in_array($fichier, array('.', '..', 'Script.php')))
                continue;

            $classe = str_replace('.php', '', $fichier);

            $classe_a_instancier = "\\App\\Eden\\Migrations\\Scripts\\".$dossier."\\".ucfirst($classe);

            $classe_instanciee = new $classe_a_instancier();

            $scripts[$classe] = $classe_instanciee;
        }

        if(is_dir(app_path('Migrations/Scripts/'.$dossier))) {

            $repertoire_scripts_spe = scandir(app_path('Migrations/Scripts/'.$dossier));

            foreach ($repertoire_scripts_spe as $fichier) {

                if (in_array($fichier, array('.', '..', 'Script.php')))
                    continue;

                $classe = str_replace('.php', '', $fichier);

                $classe_a_instancier = "\\App\\Migrations\\Scripts\\" . $dossier . "\\" . ucfirst($classe);

                $classe_instanciee = new $classe_a_instancier();

                $scripts[$classe] = $classe_instanciee;
            }
        }

        ksort($scripts);

        // on traite chaque script
        foreach($scripts as $nom => $classe) {

            if(parametre($nom) == 1) {

                continue;
            }

            $retour = $classe->execute();

            if($retour === true)
                parametre($nom, 1);
        }
    }

    /**
     *
     * Permet de reprendre les fonctionnalités splitté sur plusieurs tooogle en 1 seule fonctionnalité
     *
     */
    public static function regrouper_fonctionnalite($fonctionnalites_a_regrouper) {

        $toutes_les_fonctionnalites = config('fonctionnalites');

        foreach ($fonctionnalites_a_regrouper as $nom_nouvelle_fonctionnalite => $correspondances){

            if (!array_key_exists($nom_nouvelle_fonctionnalite, $toutes_les_fonctionnalites))
                continue;

            foreach ($correspondances as $nouvelle_fonctionnalite => $ancienne_fonctionnalite) {

                // Si la config n'existe pas, pas de valeur à récupérer, on skip
                if (array_key_exists($ancienne_fonctionnalite, $toutes_les_fonctionnalites))
                    $toutes_les_fonctionnalites[$nom_nouvelle_fonctionnalite][$nouvelle_fonctionnalite] = $toutes_les_fonctionnalites[$ancienne_fonctionnalite];
            }
        }

        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

        $fonctionnalites_password = $management_fonctionnalites_generales->toutes_fonctionnalites_par_type('password');

        foreach ($fonctionnalites_password as $fonctionnalite_password) {

            if (isset($toutes_les_fonctionnalites[$fonctionnalite_password]))
                $toutes_les_fonctionnalites[$fonctionnalite_password] = base64_encode($toutes_les_fonctionnalites[$fonctionnalite_password]);
        }

        // Les valeurs sont reprises, on peut enregistrer les fonctionnalités

        $verfication_conditionnement = false;

        // On va activer automatique "unite" en colonne si conditionnement activé
        foreach($toutes_les_fonctionnalites as $nom => $valeur) {

            if ($nom === "utiliser_conditionnement" && $valeur == "true")
                $verfication_conditionnement = true;

        }

        if($verfication_conditionnement){
            $toutes_les_fonctionnalites['documents_colonnes_a_afficher_vente']["unite"] = "true";
            $toutes_les_fonctionnalites['documents_colonnes_a_afficher_achat']["unite"] = "true";
        }

        self::ecrit_fichier_fonctionnalites($toutes_les_fonctionnalites);

        return true;
    }

	/**
	 *
	 * Permet de modifier une ou plusieurs fonctionnalités en les forçants
	 *
	 */
	public static function modifier_fonctionnalites($fonctionnalites_a_remplacer = array()) {

		$toutes_les_fonctionnalites = config('fonctionnalites');

        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

		foreach($fonctionnalites_a_remplacer as $nom => $valeur) {

            if(is_array($valeur) && is_array($toutes_les_fonctionnalites[$nom]))
                $toutes_les_fonctionnalites[$nom] = array_merge($toutes_les_fonctionnalites[$nom], $valeur);
            else
                $toutes_les_fonctionnalites[$nom] = $valeur;

            $fonctionnalite_par_profils = $toutes_les_fonctionnalites['fonctionnalites_par_profil'][$nom] ?? null;
            
            if(!isset($fonctionnalite_par_profils))
                continue;
            
            foreach($fonctionnalite_par_profils as $profil => $valeur_profil){

                if(is_array($valeur) && is_array($toutes_les_fonctionnalites['fonctionnalites_par_profil'][$nom][$profil]))
                    $toutes_les_fonctionnalites['fonctionnalites_par_profil'][$nom][$profil] = 
                        array_merge($toutes_les_fonctionnalites['fonctionnalites_par_profil'][$nom][$profil], $valeur);
                else
                    $toutes_les_fonctionnalites['fonctionnalites_par_profil'][$nom][$profil] = $valeur;
            }
		}
        
        $fonctionnalites_password = $management_fonctionnalites_generales->toutes_fonctionnalites_par_type('password');

        foreach ($fonctionnalites_password as $fonctionnalite_password) {

            if (isset($toutes_les_fonctionnalites[$fonctionnalite_password]))
                $toutes_les_fonctionnalites[$fonctionnalite_password] = base64_encode($toutes_les_fonctionnalites[$fonctionnalite_password]);
        }

		self::ecrit_fichier_fonctionnalites($toutes_les_fonctionnalites);
	}

	/**
	 *
	 * Permet de remplacer la valeur d'une fonctionnalité
	 *
	 */
	public static function ecrit_fichier_fonctionnalites($toutes_les_fonctionnalites) {

		$contenu_fichier = "<?php\n\nreturn [\n";

        foreach($toutes_les_fonctionnalites as $nom => $valeur) {

            if($valeur === true || $valeur === 'true')
                $contenu_fichier .= "\t'".$nom."' => true,\n";
            elseif($valeur === false || $valeur === 'false')
                $contenu_fichier .= "\t'".$nom."' => false,\n";
            elseif(is_array($valeur)){

                $contenu_fichier .= "\t'".$nom."' => [\n";

                foreach($valeur as $nom_element => $element){

                    if($element == 'true'){
                        $contenu_fichier .= "\t\t'".$nom_element."' => true,\n";
                    }

                    else{
                        $contenu_fichier .= "\t\t'".$nom_element."' => false,\n";
                    }
                }

                $contenu_fichier .= "\t ], \n";
            }
            else {

                $contenu_fichier .= "\t'".$nom."' => \"".$valeur."\",\n";
            }
        }

        $contenu_fichier .= "];";

		// on stocke dans un fichier
        \Storage::put('eden_fonctionnalites.php', $contenu_fichier);
	}

    /**
     *
     * Permet de transformer une liste formatee en n'importe quel type de champ
     *
     */
    public static function liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, $changement_bdd = false){

        $cles_etrangeres = (array) DB::select('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = "'.env('DB_DATABASE').'"');
        $cles_etrangeres = array_column($cles_etrangeres,'CONSTRAINT_NAME');

        $types_elements = array();
        foreach($champs_libres as $champ_libre){

            $types_elements[] = $champ_libre->type_element;

            foreach($nouvelles_informations as $nom_information => $nouvelle_information){

                if(array_key_exists($nom_information, $champ_libre->toArray()))
                    $champ_libre->{$nom_information} = $nouvelle_information;
            }

            $champ_libre->save();

            if($changement_bdd){
                $types_champs = Variables::types_champs_libres_bdd();
                $colonne_pour_type = $champ_libre->type == 10 ? $champ_libre->type_reference : $champ_libre->type;

                $nouveau_type = is_array($types_champs[$colonne_pour_type]) 
                    ? $types_champs[$colonne_pour_type]['type'].($types_champs[$colonne_pour_type]['unsigned'] ? ' UNSIGNED' : '') 
                    : $types_champs[$colonne_pour_type];

                self::change_type_de_colonne_sur_table(($champ_libre->type == 10 ? $champ_libre->table_pivot : $champ_libre->type_element),
                    ($champ_libre->type == 10 ? 'valeur' : $champ_libre->nom_sql),
                    $nouveau_type);
            }

            if(!empty($champ_libre->id_cl) && ($champ_libre->type == 42 || $champ_libre->type_reference == 42)) {

                $nom_contrainte = 'CE_champ_libre_' . $champ_libre->id_cl;

                $nom_table = $champ_libre->type == 10 ? $champ_libre->table_pivot : $champ_libre->type_element;

                if (in_array($nom_contrainte, $cles_etrangeres)) {

                    try {
                        DB::select('ALTER TABLE `' . $nom_table . '` DROP CONSTRAINT `' . $nom_contrainte . '`;');
                    } catch (\Exception $e) {}
                }

                try{
                    DB::select('ALTER TABLE `' . $nom_table . '` ADD CONSTRAINT `' . $nom_contrainte . '` FOREIGN KEY (`' . ($champ_libre->type == 10 ? 'valeur' : $champ_libre->nom_sql) . '`) REFERENCES `' . $champ_libre->type_element_ajax . '`(`id`);');
                } catch (\Exception $e) {}
            }
        }

        $types_elements = array_unique($types_elements);

        foreach($types_elements as $type_element) {

            $chemin_avec_nom_document = app_path() . '/Migrations/' . $type_element . '.php';

            // Si le fichier existe, on regénére le fichier de migrations
            if (file_exists($chemin_avec_nom_document) == true)
                Table_libre_management::generer_fichier_migration($type_element,true);
        }

        return true;

    }

	/**
	 *
	 * Change le type d'une colonne SQL (varcher => longtext par exemple)
	 *
	 */
	public static function change_type_de_colonne_sur_table($table, $colonne, $nouveau_type){

		DB::select('ALTER TABLE '.$table.' Modify column '.$colonne.' '.$nouveau_type.';');

    }

    public static function generer_fichier_fonctionnalite($fonctionnalites){

        $contenu_fichier = "<?php\n\nreturn [\n";

        foreach($fonctionnalites as $nom => $valeur) {

            if($valeur === true)
                $contenu_fichier .= "\t'".$nom."' => true,\n";
            elseif($valeur === false)
                $contenu_fichier .= "\t'".$nom."' => false,\n";
            elseif(is_array($valeur)){

                $contenu_fichier .= "\t'".$nom."' => [\n";

                foreach($valeur as $nom_element => $element){

                    if($element == 'true')
                        $contenu_fichier .= "\t\t'".$nom_element."' => true,\n";
                    elseif($element == 'false')
                        $contenu_fichier .= "\t\t'".$nom_element."' => false,\n";
                    elseif(is_array($element)){

                        $contenu_fichier .= "\t\t'".$nom_element."' => [\n";

                        foreach($element as $nom_sous_element => $sous_element){

                            if($sous_element == 'true')
                                $contenu_fichier .= "\t\t\t'".$nom_sous_element."' => true,\n";
                            elseif($sous_element == 'false')
                                $contenu_fichier .= "\t\t\t'".$nom_sous_element."' => false,\n";
                            elseif(is_array($sous_element)){

                                 $contenu_fichier .= "\t\t\t'".$nom_sous_element."' => [\n";
                                foreach($sous_element as $nom_sous_sous_element => $sous_sous_element){

                                    if($sous_sous_element == 'true')
                                        $contenu_fichier .= "\t\t\t\t'".$nom_sous_sous_element."' => true,\n";
                                    elseif($sous_sous_element == 'false')
                                        $contenu_fichier .= "\t\t\t\t'".$nom_sous_sous_element."' => false,\n";
                                    else
                                        $contenu_fichier .= "\t\t\t\t'".$nom_sous_sous_element."' => \"".$sous_sous_element."\",\n";
                                }
                                $contenu_fichier .= "\t\t\t],\n";
                            }
                        }

                        $contenu_fichier .= "\t\t],\n";
                    }
                }

                $contenu_fichier .= "\t ], \n";
            }
            else
                $contenu_fichier .= "\t'".$nom."' => \"".$valeur."\",\n";
        }

        $contenu_fichier .= "];";

        // on stocke dans un fichier
        \Storage::put('eden_fonctionnalites.php', $contenu_fichier);

        return true;
    }


    public static function ajout_champ_formulaire_libre($nom_formulaire, $formulaire) {

        $nouveau_champ = new Formulaires_champs;

        $champ_avec_plus_grand_ordre = Formulaires_champs::where('nom_formulaire', $nom_formulaire)->orderBy('ordre', 'desc')->first();

        $nouveau_champ->nom_formulaire  = $formulaire['nom_formulaire'];
        $nouveau_champ->type_element    = $formulaire['type_element'];
        $nouveau_champ->nom_sql         = $formulaire['nom_sql'];
        $nouveau_champ->taille_avant    = $formulaire['taille_avant'];
        $nouveau_champ->taille_libelle  = $formulaire['taille_libelle'];
        $nouveau_champ->taille_champ    = $formulaire['taille_champ'];
        $nouveau_champ->taille_apres    = $formulaire['taille_apres'];
        $nouveau_champ->ordre           = $champ_avec_plus_grand_ordre['ordre'] + 1;

        $nouveau_champ->save();

        // On crée le fichier de migration spécifique
        Maintenance_management::generer_fichier_migration_formulaire($nom_formulaire);
     }


    /*
    **
    *
    * Change un champ type element / id element en type element et element id dynamique
	 *
	 */
	public static function passage_champ_type_element_dynamique($array_elements_a_modifier){

        // On boucle sur tous les éléments à modifier
        foreach ($array_elements_a_modifier as $element_a_modifier){

            // on récupère les 2 champs libres a modifier
            $champ_type_element = Champ_libre::where('type_element',$element_a_modifier['type_element'])->where('nom_sql', $element_a_modifier['nom_sql'])->first();
            $champ_element_id = Champ_libre::where('type_element',$element_a_modifier['type_element'])->where('nom_sql', $element_a_modifier['element_id'])->first();

            if(!$champ_type_element || !$champ_element_id)
                continue;

            // On met en forme les type elements disponibles
            $array_type_elements = array();

            foreach ($element_a_modifier['types_disponibles'] as $type){

                $array_type_elements[] = array(
                    'type_element' => $type,
                    'valeur' => true,
                );
            }

            $champ_type_element->type = 21;
            $champ_type_element->contenu = json_encode($array_type_elements);

            $champ_element_id->type = 22;
            $champ_element_id->contenu = $element_a_modifier['nom_sql'];

            $champ_type_element->save();
            $champ_element_id->save();

            $chemin_avec_nom_document = app_path() . '/Migrations/' . $element_a_modifier['type_element'] . '.php';

            // Si le fichier existe, on regénére le fichier de migrations
            if (file_exists($chemin_avec_nom_document) == true)
                Table_libre_management::generer_fichier_migration($element_a_modifier['type_element'],true);
        }
    }

    /**
     *
     * Supprime les fichier de migrations spé
     *
     */
    public static function supprime_fichier_spe($fichier)
    {
        $route = app_path() . '/' . $fichier;

        if(!file_exists($route))
            return true;

        unlink($route);
    }

    /*
     * 
     * Supprime tout ce qui est relatif à un élément (suppression des entrées en bdd, migrations et drop de la table)
     * Avant la suppression, on vérifie tous les champs liés à ce type_element(type 10 reference 42, 20 liste choix 71,21, 42 et nom_sql type_element) 
     * et l'utilisation de sous-formulaires, rapports et listes libres sur fiches. Si un des champs contient des données, ou que l'un des éléments est utilisé,
     * on retourne à l'utilisateur tout ce qui est encore utilisé pour qu'il trie les données
     * 
     */
    public static function supprimer_type_element($type_element){

        $elements_bdd_par_type = self::elements_bdd_type_element($type_element);
        $id_table_type = $elements_bdd_par_type['Table_libre']->first(fn($table) => $table->type_element === $type_element)->id ?? null;
        $champs_a_verifier = self::champs_pointant_vers_type_element($type_element, $id_table_type);

        $elements_bloquants = array();

        foreach($champs_a_verifier as $champ) {

            if(!Schema::hasTable($champ->type_element))
                continue;
            
            if($champ->type === 42)
                $nombre_donnees = modele($champ->type_element)->whereNotNull($champ->nom_sql)->count();
            else if($champ->type === 20)
                $nombre_donnees = modele($champ->type_element)->where($champ->nom_sql, $id_table_type)->count();
            else if($champ->type === 10)
                $nombre_donnees = DB::table($champ->table_pivot)->count();
            else
                $nombre_donnees = modele($champ->type_element)->where($champ->nom_sql, $type_element)->count();

            if($nombre_donnees > 0)
                $elements_bloquants[] = 'Le champ ' . $champ->nom_sql . ' (type_element : ' . $champ->type_element . ') contient des données pointant vers ce type_element';
        }

        self::verification_utilisation_sous_formulaires($elements_bloquants, $elements_bdd_par_type['Sous_formulaire']);
        self::verification_utilisation_rapports($elements_bloquants, $elements_bdd_par_type['Rapports']);
        self::verification_utilisation_vues_sql($elements_bloquants, $type_element);

        if(!empty($elements_bloquants))
            throw new \Exception(nl2br("Impossible de supprimer le type d'élément " . $type_element . ". Des éléments contiennent des données dépendantes de celui-ci : \n" . implode(",\n", $elements_bloquants)), 500);
        
        $dossiers_migrations_possibles = ['Formulaires_libres','Rapports','Sous_formulaire','Listes_libres', 'Table_libre'];
        
        foreach($elements_bdd_par_type as $nom_dossier => $elements){

            foreach($elements as $element) {

                if(in_array($nom_dossier, $dossiers_migrations_possibles)){

                    $nom_dossier_definitif = $nom_dossier;
                    
                    if($nom_dossier === "Listes_libres")
                        $nom_dossier_definitif .= $element->export ? "_export" : (str_contains($element->id_rapport, 'fiche_') ? "_fiches" : "");
                    else if($nom_dossier === 'Table_libre')
                        $nom_dossier_definitif = null;
                    
                    if($nom_dossier === 'Formulaires_libres')
                        $nom_fichier = $element->nom_formulaire;
                    else if($nom_dossier === 'Sous_formulaire')
                        $nom_fichier = $element->nom_sous_formulaire;
                    else
                        $nom_fichier = !empty($element->id_rapport) ? $element->id_rapport : $element->type_element;

                    self::supprimer_fichier_migration($nom_fichier, $nom_dossier_definitif);
                }

                if(!empty($element->table_pivot) && Schema::hasTable($element->table_pivot))
                    Schema::drop($element->table_pivot);

                if($nom_dossier === 'recherche_avancee')
                    management($nom_dossier, $element->id, $element)->supprime();
                else
                    $element->delete();
            }
        }

        foreach($champs_a_verifier->filter(fn($c) => !empty($c->type_element_ajax)) as $champ){
            
            $type_element = $champ->type_element;

            Schema::dropColumns($type_element, $champ->nom_sql);
            $champ->delete();

            Table_libre_management::generer_fichier_migration($type_element, true);
        }

        if(file_exists(storage_path('app/eden_fiche_' . $type_element)))
            unlink(storage_path('app/eden_fiche_' . $type_element));

        if(Schema::hasTable($type_element))
            Schema::drop($type_element);

        return true;
    }

    /*
     *
     * Supprime un fichier spécifique de migration
     * Si $nom_dossier est null ou n'est pas passé en paramètre, on cherche $nom_fichier directement dans app/Migrations
     * 
     */
    public static function supprimer_fichier_migration($nom_fichier, $nom_dossier = null){

        $chemin = app_path('Migrations');

        if(isset($nom_dossier))
            $chemin .= '/' . $nom_dossier;

        $chemin .= '/' . $nom_fichier . '.php';

        if(file_exists($chemin))
            unlink($chemin);
    }

    /*
     *
     * Récupère tous les champs pointant vers un type_element, cela inclut les types 10 reference 42, 20 liste choix 71,
     * 21 avec le type activé dans son contenu, 42 et dont le nom_sql est type_element
     * 
     */
    public static function champs_pointant_vers_type_element($type_element, $id_table_type = null) {

        return Champ_libre::where(function($requete) use ($type_element) {

            $requete->where('type_element_ajax', $type_element)
                ->orWhere(function($sous_requete) {

                    $sous_requete->where('nom_sql', 'type_element')->whereNotIn('type', ['42', '21', '10']);
                })
                ->orWhere(function($sous_requete) use ($type_element) {

                    $sous_requete->where('type', 21)->where('contenu', 'LIKE', '%"'. $type_element .'","valeur":true%');
                })
                ->orWhere('type_element_origine', $type_element);

                if(!empty($id_table_type))
                    $requete->orWhere(function($sous_requete) {

                        $sous_requete->where('type', 20)->where('liste_choix', 71);
                    });
        })
        ->whereNotIn('type_element', ['recherche_avancee', 'recherche_avancee_bloc', 'recherche_avancee_filtre'])
        ->get();
    }

    /*
     *
     * Récupère toutes les entrées de bdd liées à un type_element, cela inclut :
     * - les champs libres
     * - les formulaires libres, leurs champs et leurs valeurs par défaut
     * - les sous-formulaires, leurs champs et leurs valeurs par défaut
     * - les listes libres, leurs colonnes, leurs couleurs et leurs calculs
     * - les rapports libres et leurs paramètres
     * - la table libre
     * - les recherches avancées
     * - les triggers
     * - les vues sql
     * 
     */
    public static function elements_bdd_type_element($type_element) {
        
        $elements_bdd_par_type = [
            'champs' => Champ_libre::where('type_element', $type_element)->get(),
            'Formulaires_libres' => Formulaire::where(function($requete) use ($type_element){
                    $requete->where('type_element', $type_element)->orWhere('nom_formulaire', 'LIKE', '%' . $type_element . '%');
                })->get(),
            'Rapports' => Rapport_libre::select('eden_rapports.*', 'eden_listeslibres.id as id_liste')
                ->leftJoin('eden_listeslibres', 'eden_listeslibres.id_rapport', 'eden_rapports.id_rapport')
                ->where(function($requete) use ($type_element){
                    $requete->where('eden_listeslibres.type_element', $type_element)
                        ->orWhere('eden_rapports.type_element', $type_element)
                        ->orWhere('eden_rapports.type_element_fiche', $type_element);
                })
                ->get(),
            'recherche_avancee' => modele('recherche_avancee')->where('type_element', $type_element)->get(),
            'Sous_formulaire' => modele('eden_sous_formulaire')->where('type_element_enfant', $type_element)->get(),
            'Table_libre' => Table_libre::where('type_element', $type_element)->get(),
        ];
        
        $id_table_type = $elements_bdd_par_type['Table_libre']->first(fn($table) => $table->type_element === $type_element)->id ?? null;

        if(!empty($id_table_type))
            $elements_bdd_par_type['trigger_eden'] = modele('trigger_eden')->where('type_element_id', $id_table_type)->get();

        $elements_bdd_par_type['Listes_libres'] = Liste_libre::where(function($requete) use($type_element, $elements_bdd_par_type) {

            $requete->where('type_element', $type_element);

            if($elements_bdd_par_type['Rapports']->isNotEmpty())
                $requete->orWhereIn('id_rapport', $elements_bdd_par_type['Rapports']->pluck('id_rapport')->toArray());
        })->get();

        if($elements_bdd_par_type['Formulaires_libres']->isNotEmpty()){

            $noms_formulaires = $elements_bdd_par_type['Formulaires_libres']->pluck('nom_formulaire')->toArray();

            $elements_bdd_par_type['Formulaires_libres_champs'] = Formulaires_champs::whereIn('nom_formulaire', $noms_formulaires)->get();
            $elements_bdd_par_type['Formulaires_libres_valeurs_defaut'] = Formulaire_valeur_par_defaut::whereIn('nom_formulaire', $noms_formulaires)->get();
        }

        if($elements_bdd_par_type['Sous_formulaire']->isNotEmpty()){

            $noms_sous_formulaires = $elements_bdd_par_type['Sous_formulaire']->pluck('nom_sous_formulaire')->toArray();

            $elements_bdd_par_type['Sous_formulaire_champs'] = Formulaires_champs::whereIn('nom_sous_formulaire', $noms_sous_formulaires)->get();
            $elements_bdd_par_type['Sous_formulaire_valeurs_defaut'] = Formulaires_champs::whereIn('nom_formulaire', $noms_sous_formulaires)->get();
        }

        if($elements_bdd_par_type['Listes_libres']->isNotEmpty()){

            $ids_listes = $elements_bdd_par_type['Listes_libres']->pluck('id')->toArray();

            $elements_bdd_par_type['listes_colonnes'] = Colonne::whereIn('liste_libre_id', $ids_listes)->get();
            $elements_bdd_par_type['listes_couleurs'] = Liste_libre_couleur::whereIn('liste_libre_id', $ids_listes)->get();
            $elements_bdd_par_type['listes_calculs'] = Liste_libre_calcul::whereIn('liste_libre_id', $ids_listes)->get();
            $elements_bdd_par_type['listes_filtres'] = Liste_libre_filtre::whereIn('liste_libre_id', $ids_listes)->get();
        }

        if($elements_bdd_par_type['Rapports']->isNotEmpty()){

            $ids_rapports = $elements_bdd_par_type['Rapports']
                ->pluck(fn($rapport) => 
                    isset($rapport->id_liste) ? 'liste_' . $rapport->id_liste : $rapport->id_rapport
                )
                ->toArray();

            $elements_bdd_par_type['Rapports_parametres'] = Rapport_parametre::whereIn('id_rapport', $ids_rapports)->get();
        }

        return $elements_bdd_par_type;
    }

    /*
     *
     * Vérifie si les sous-formulaires passés en second paramètre sont utilisés sur des formulaires
     * Retourne les sous-formulaires utilisés et sur quoi en mettant à jour le tableau passé en premier paramètre
     * 
     */
    public static function verification_utilisation_sous_formulaires(&$elements_utilises, $elements_a_verifier){

        foreach($elements_a_verifier as $element){

            $formulaires = Formulaires_champs::select('eden_formulaireslibres.nom_formulaire')
                ->join('eden_formulaireslibres', 'eden_formulaireslibres.nom_formulaire', 'eden_formulaireslibres_champs.nom_formulaire')
                ->where('nom_sous_formulaire', $element->nom_sous_formulaire)
                ->get()
            ->pluck('nom_formulaire');

            if($formulaires->isNotEmpty())
                $elements_utilises[] = "Le sous-formulaire " . $element->nom_sous_formulaire . " est utilisé dans les formulaires suivants : " . $formulaires->join(', ');
        }
    }

    /*
     *
     * Vérifie si les rapports passés en second paramètre sont utilisés sur des tableaux de bord et des fiches
     * Retourne les rapports utilisés et sur quoi en mettant à jour le tableau passé en premier paramètre
     * 
     */
    public static function verification_utilisation_rapports(&$elements_utilises, $elements_a_verifier){

        foreach($elements_a_verifier as $element){

            $type_element_fiche = $element->fiche ?? $element->type_element_fiche;
            $chaine_utilisation = "";

            $tableaux_de_bord = modele('tableau_de_bord_contenu')
                ->select('tdb.nom')
                ->join('tableau_de_bord as tdb', 'tdb.id', 'tableau_de_bord_contenu.tableau_de_bord')
                ->where('tableau_de_bord_contenu.type', 1)
                ->where('tableau_de_bord_contenu.element', $element->id_rapport)
                ->get()
                ->pluck('nom');

            $modules_fiches = fiche($type_element_fiche)->modules_utilises();

            if(in_array($element->id_rapport, $modules_fiches) || $tableaux_de_bord->isNotEmpty())
                $chaine_utilisation .= "Le rapport " . $element->id_rapport . " est utilisé ";

            if(in_array($element->id_rapport, $modules_fiches))
                $chaine_utilisation .= "sur la fiche " . $type_element_fiche;

            if(in_array($element->id_rapport, $modules_fiches) && $tableaux_de_bord->isNotEmpty())
                $chaine_utilisation .= " et ";

            if($tableaux_de_bord->isNotEmpty())
                $chaine_utilisation[] = "dans les tableaux de bord suivants : " . $tableaux_de_bord->join(', ');

            if($chaine_utilisation !== "")
                $elements_utilises[] = $chaine_utilisation;
        }
    }

    /*
     *
     * Vérifie s'il y a des vues SQL utilisant le type_element passé en second paramètre
     * Retourne les vues utilisées en mettant à jour le tableau passé en premier paramètre
     * 
     */
    public static function verification_utilisation_vues_sql(&$elements_utilises, $type_element){

        $vues_sql = modele('vue_sql')->where(function($requete) use ($type_element) {

                $requete->where('tables', 'like', '%' . $type_element . '%')
                    ->orWhereIn('requete', [
                        '%FROM ' . $type_element . '%',
                        '%FROM `' . $type_element . '`%',
                        '%JOIN ' . $type_element . '%',
                        '%JOIN `' . $type_element . '`%',
                    ]);
            })
            ->get();

        foreach ($vues_sql as $vue){

            $elements_utilises[] = "La vue SQL " . $vue->nom . " (nom SQL : " . $vue->nom_sql . ") utilise le type_element à supprimer dans sa requête.";
        }
    }
}
