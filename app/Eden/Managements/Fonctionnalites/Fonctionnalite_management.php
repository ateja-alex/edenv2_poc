<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

use Illuminate\Support\Str;

class Fonctionnalite_management {

    public function recupere_management_fonctionnalite($type_element){

        $tests = array(
            "\\App\\Managements\\Fonctionnalites\\".ucfirst($type_element)."_management",
            "\\App\\Eden\\Managements\\Fonctionnalites\\".ucfirst($type_element)."_management",
        );

        $classe = null;

        foreach($tests as $test)
            if(class_exists($test)) {

                $classe = $test;
                break;
            }

        if(empty($classe))
            return null;

        return new $classe($type_element);
    }

    public function enfants(){

        $enfants = array();

        $repertoire_fonctionnalites = scandir(base_path('app/Eden/Managements/Fonctionnalites'));

        foreach ($repertoire_fonctionnalites as $fichier){

            if($fichier == "." || $fichier == "..")
                continue;

            $fichier = str_replace(".php", "", $fichier);
            $fichier_sans_management = str_replace("_management", "", $fichier);

            $tests = array(
                "\\App\\Managements\\Fonctionnalites\\".ucfirst($fichier),
                "\\App\\Eden\\Managements\\Fonctionnalites\\".ucfirst($fichier),
            );

            foreach($tests as $classe) {

                if(class_exists($classe) && $fichier_sans_management != 'Fonctionnalite') {

                    $enfants[] = strtolower($fichier_sans_management);
                }
            }
        }

        return $enfants;
    }

    public function enfants_avec_informations_sans_fonctionnalites(){

        $enfants = array();

        $repertoire_fonctionnalites = scandir(base_path('app/Eden/Managements/Fonctionnalites'));

        foreach ($repertoire_fonctionnalites as $fichier){

            if($fichier == "." || $fichier == "..")
                continue;

            // On affiche le module spécifique que si on en a au moins une
            if(empty(config('fonctionnalites_specifiques')) && $fichier === 'Fonctionnalites_specifiques_management.php')
                continue;

            $fichier = str_replace(".php", "", $fichier);
            $fichier_sans_management = str_replace("_management", "", $fichier);

            $tests = array(
                "\\App\\Managements\\Fonctionnalites\\".ucfirst($fichier),
                "\\App\\Eden\\Managements\\Fonctionnalites\\".ucfirst($fichier),
            );

            foreach($tests as $classe) {

                if(class_exists($classe) && $fichier_sans_management != 'Fonctionnalite') {

                    $informations = $this->recupere_management_fonctionnalite(strtolower($fichier_sans_management))->informations_sans_fonctionnalites();
                    $informations['nom_module_lien'] = strtolower($fichier_sans_management);

                    $enfants[] = $informations;
                }
            }
        }

        return $enfants;
    }

    public function toutes_les_fonctionnalites($avec_valeurs = true){

        $fonctionnalites = array();

        $enfants = $this->enfants();

        foreach ($enfants as $enfant){

            $fonctionnalites[$enfant] = $this->recupere_management_fonctionnalite($enfant)->liste_fonctionnalites($avec_valeurs);
        }

        return $fonctionnalites;
    }

    public function toutes_les_fonctionnalites_avec_categories(){

        $fonctionnalites = array();

        $enfants = $this->enfants();

        foreach ($enfants as $enfant){

            $fonctionnalites[$enfant] = $this->recupere_management_fonctionnalite($enfant)->liste_fonctionnalites_avec_categories();
        }

        return $fonctionnalites;
    }

    public function liste_fonctionnalites($avec_valeurs = true){

        $liste_fonctionnalites = array();

        if($avec_valeurs)
            $fonctionnalites_module = $this->fonctionnalites_avec_valeurs();

        else
            $fonctionnalites_module = $this->fonctionnalites();

        foreach ($fonctionnalites_module as $categorie){

            if (is_array($categorie)){

                foreach ($categorie as $fonctionnalite_categorie){

                    if (isset($fonctionnalite_categorie['fonctionnalite']))
                        $liste_fonctionnalites[] = $fonctionnalite_categorie;
                }
            }
        }

        return $liste_fonctionnalites;

    }

    public function liste_fonctionnalites_avec_categories(){

        $liste_fonctionnalites = array();

        $fonctionnalites_module = $this->fonctionnalites_avec_valeurs();

        foreach ($fonctionnalites_module as $nom_categorie => $categorie){

            if (is_array($categorie)){

                foreach ($categorie as $fonctionnalite_categorie){

                    if (isset($fonctionnalite_categorie['fonctionnalite']))
                        $liste_fonctionnalites[$nom_categorie][] = $fonctionnalite_categorie;
                }
            }
        }

        return $liste_fonctionnalites;

    }

    public function recherche_fonctionnalite($chaine_recherche){

        $fonctionnalites = array();

        $liste_fonctionnalites = $this->liste_fonctionnalites();

        foreach ($liste_fonctionnalites as $nom_fonctionnalite){

            if (Str::contains(strtolower($nom_fonctionnalite['nom']),strtolower($chaine_recherche)) ||
                Str::contains(strtolower($nom_fonctionnalite['fonctionnalite']),strtolower($chaine_recherche)))    
                $fonctionnalites[] = $nom_fonctionnalite;
        }

        return $fonctionnalites;
    }

    public function recherche_fonctionnalites($chaine_recherche){

        $fonctionnalites = array();

        $enfants = $this->enfants();

        foreach ($enfants as $enfant){

            $management_enfant = $this->recupere_management_fonctionnalite($enfant);

            $resultats = $management_enfant->recherche_fonctionnalite($chaine_recherche);

            if(!empty($resultats)){

                $fonctionnalites[$enfant]['resultats'] = $resultats;
                $fonctionnalites[$enfant]['nom'] = $management_enfant->obtenir_nom_module();
            }
        }

        return $fonctionnalites;
    }

    public function recuperer_difference_vue_config(){

        $fonctionnalites_config = config('fonctionnalites');
        $fonctionnalites_vue = $this->toutes_les_fonctionnalites();
        $fonctionnalites_manquantes = array();

        foreach ($fonctionnalites_vue as $categorie => $fonctionnalites){

            foreach ($fonctionnalites as $fonctionnalite){

                if (!array_key_exists($fonctionnalite['fonctionnalite'],$fonctionnalites_config))
                    $fonctionnalites_manquantes[$categorie][] = $fonctionnalite['fonctionnalite'];
            }
        }

        return $fonctionnalites_manquantes;
    }

    public function fonctionnalites($valeurs = array()){

        return array();
    }

    public function fonctionnalites_avec_valeurs(){

        return array();
    }

    public function obtenir_type_module(){

        return 'Module par défaut';
    }

    public function obtenir_nom_module(){

        return 'Module par défaut';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/functionnalities.png';
    }

    public function informations(){

        $retour = array(
            'fonctionnalites' => $this->fonctionnalites_avec_valeurs(),
            'type_module' => $this->obtenir_type_module(),
            'nom_module' => $this->obtenir_nom_module(),
            'lien_icone' => $this->obtenir_icone_module(),
        );

        if($this->obtenir_nom_module() === 'Module de fonctionnalités spécifiques' && empty($retour['fonctionnalites']))
            $retour['fonctionnalites'] = $this->fonctionnalites_mise_en_page_par_defaut();

        return $retour;
    }

    public function informations_sans_fonctionnalites(){

        return array(
            'type_module' => $this->obtenir_type_module(),
            'nom_module' => $this->obtenir_nom_module(),
            'lien_icone' => $this->obtenir_icone_module(),
        );
    }

    /*
     *
     * Récupère toutes les fonctionnalités soit pour un type soit classé par type
     * Attention la forme est la suivante :
     * 'type_champ' => [
     *      0 => 'id_fonction1',
     *      1 => 'id_fonction2',
     * ],
     * OU
     * [
     *      'type_champ' => [
     *          0 => 'id_fonction1',
     *          1 => 'id_fonction2',
     *      ],
     *      'type_champ2' => [
     *          0 => 'id_fonction3',
     *          1 => 'id_fonction4',
     *      ],
     * ]
     *
     */
    public function toutes_fonctionnalites_par_type($type = ""){

        $toutes_les_fonctionnalites = $this->toutes_les_fonctionnalites(false);

        $toutes_les_fonctionnalites_formate = array();

        foreach ($toutes_les_fonctionnalites as $fichier_fonctionnalite => $categories) {

            foreach ($categories as $fonctionnalite) {

                $toutes_les_fonctionnalites_formate[$fonctionnalite['type']][] = $fonctionnalite['fonctionnalite'];
            }
        }

        if($type !== "" && isset($type, $toutes_les_fonctionnalites_formate))
            return !empty($toutes_les_fonctionnalites_formate[$type]) ? $toutes_les_fonctionnalites_formate[$type] : array();
        else
            return $toutes_les_fonctionnalites_formate;

    }

    public function methode_post_enregistrement_fonctionnalites($nouvelles_valeurs){

        if(!file_exists(storage_path('app/eden_fonctionnalites.php')))
            $anciennes_valeurs = [];
        else
            $anciennes_valeurs = include(storage_path('app/eden_fonctionnalites.php'));
        
        if(array_key_exists('activer_gestion_stock', $nouvelles_valeurs)) {

            if (($nouvelles_valeurs['activer_gestion_stock'] === true || $nouvelles_valeurs['activer_gestion_stock'] == "true") && (!array_key_exists('activer_gestion_stock', $anciennes_valeurs) || $anciennes_valeurs['activer_gestion_stock'] === false || $anciennes_valeurs['activer_gestion_stock'] === "false")) {

                $champs_libres = Champ_libre::where('nom_sql', 'entrepot_id')->whereIn('type_element', array('bl_vente', 'bl_achat', 'commande_vente', 'commande_achat', 'bon_preparation_vente', 'bon_retour_vente', 'bon_retour_achat'))->get();

                $nouvelles_informations = array(
                    'obligatoire' => 1,
                );

                if (modele('entrepot')->get()->count() == 1) {
                    $nouvelles_informations['valeur_defaut'] = modele('entrepot')->first()->id;
                }

                $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

            } else if (($nouvelles_valeurs['activer_gestion_stock'] === false || $nouvelles_valeurs['activer_gestion_stock'] == "false") && (!array_key_exists('activer_gestion_stock', $anciennes_valeurs) || $anciennes_valeurs['activer_gestion_stock'] === true || $anciennes_valeurs['activer_gestion_stock'] === "true")) {

                $champs_libres = Champ_libre::where('nom_sql', 'entrepot_id')->whereIn('type_element', array('bl_vente', 'bl_achat', 'commande_vente', 'commande_achat', 'bon_preparation_vente', 'bon_retour_vente', 'bon_retour_achat'))->get();

                $nouvelles_informations = array(
                    'obligatoire' => 0,
                );

                $retour = Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

            }
        }
    }

    public function methode_pre_enregistrement($nouvelle_valeurs){

        foreach ($nouvelle_valeurs as $nom => $valeur){

            if ($nom === "utiliser_conditionnement" && $valeur == "true"){
                $nouvelle_valeurs['documents_colonnes_a_afficher_vente']["unite"] = "true";
                $nouvelle_valeurs['documents_colonnes_a_afficher_achat']["unite"] = "true";
            }

            if($nom === "gescom_commande_vente_annulable_non_supprimable" && !empty($valeur))
                $nouvelle_valeurs['modification_document_valide']['commande_vente'] = "true";
        }

        return $nouvelle_valeurs;
    }
}
