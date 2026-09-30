<?php

namespace App\Eden\Managements\Services;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Rapport_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Table_libre;
use DB;

/**
 * 
 * Gestion des vues sql
 * 
 */
class Vue_sql_service {

    /**
     *
     * Permet de générer des vues ou une vue
     *
     */
	public function generer_vues_sql($vues_sql = null){

	    if($vues_sql == null)
            $vues_sql = modele('vue_sql')->get();

	    $retours = array();

	    foreach($vues_sql as $vue_sql) {

            if (empty($vue_sql->nom_sql))
                return traduction('messages.php.vue_sql.nom_invalide');

            if($vue_sql->type_de_vue == 1)
                $retours[$vue_sql->nom_sql] = $this->generer_vue_sql_union($vue_sql);

            else
                $retours[$vue_sql->nom_sql] = $this->generer_vue_sql_select($vue_sql);
        }

	    return $retours;
    }

    /**
     *
     * Permet de récupérer les vues d'un élément
     *
     */
    public function recuperer_vues_champs($champ_libre){

        $champs_libres_vues = Champ_libre::where('type_element_origine', $champ_libre->type_element)
                ->where('nom_sql_origine', $champ_libre->nom_sql)
                ->get();

        return $champs_libres_vues;

    }

    /**
     *
     * Permet de modifier/créer un champ libre sur une vue
     *
     */
    public function gestion_champ_libre($type_element_vue,$champ_libre_initiale,$informations = array(),$champ_libre_vue = null){

        $nom_sql = $champ_libre_initiale->type_element.'_'.$champ_libre_initiale->nom_sql;

        $modifications = $champ_libre_initiale->toArray();

        $valeurs_a_eviter = array('id_cl','index_traduction','standard','modification_post_validation','donnee_calculee_depuis','donnee_calculee_requete','donnee_calculee_champ_maj','recalcul_quotidien');

        foreach($valeurs_a_eviter as $valeur){
            if(array_key_exists($valeur,$modifications))
                unset($modifications[$valeur]);
        }

        if($champ_libre_vue == null) {
            $champ_libre_vue = Champ_libre::where('type_element', $type_element_vue)
                ->where('type_element_origine', $champ_libre_initiale->type_element)
                ->where('nom_sql_origine', $champ_libre_initiale->nom_sql);

            if(isset($informations['nom_sql']))
                $champ_libre_vue = $champ_libre_vue->where('nom_sql',$informations['nom_sql']);

            $champ_libre_vue = $champ_libre_vue->first();

        }

        if($champ_libre_vue) {

            $modifications['id_cl'] = $champ_libre_vue->id_cl;
            $modifications['nom_sql'] = $champ_libre_vue->nom_sql;
            unset($modifications['nom']);
            unset($modifications['type_element']);
            unset($modifications['type_element_origine']);
            unset($modifications['nom_sql_origine']);

        }
        else {
            $modifications['nom_sql'] = $nom_sql;

            $modifications['type_element'] = $type_element_vue;

            $modifications['type_element_origine'] = $champ_libre_initiale->type_element;

            $modifications['nom_sql_origine'] = $champ_libre_initiale->nom_sql;
        }

        foreach($informations as $cle=>$information){

            $modifications[$cle] = trim($information);

        }

        if(!empty($modifications['contenu'] && $champ_libre_initiale->type == 22))
            $modifications['contenu'] = $champ_libre_initiale->type_element.'_'.$modifications['contenu'];

        if($champ_libre_initiale->type == 1 && $champ_libre_initiale->liste_choix == null)
            $modifications['liste_choix'] = $champ_libre_initiale->id_cl;

        if($champ_libre_vue) {
            $mise_a_jour_necessaire = false;

            foreach ($modifications as $cle => $valeur) {

                if($champ_libre_vue->{$cle} != $valeur)
                    $mise_a_jour_necessaire = true;
            }

            if(!$mise_a_jour_necessaire)
                return traduction('messages.php.vue_sql.maj_non_necessaire');
        }

        return Champ_libre_management::enregistre($type_element_vue, $modifications);
    }


    /**
     *
     * Permet de récupérer le nom de la colonne id d'un element dans une vue
     *
     */
    public function recuperer_nom_colonne_id($vue_sql,$type_element){

        $tables = explode( ',', $vue_sql->tables);

        if($tables[0] == $type_element){

            $nom_champ =  'id';
        }

        else{

            $nom_champ = $type_element.'_id';
        }

        return $nom_champ;
    }


    /**
     *
     * Supprimer une vue
     *
     */
    public function supprimer_vue($vue_sql){

        $nom = $vue_sql->nom_sql;

        //Suppression de la migration
        $chemin_dossier_migrations = app_path().'/Migrations';
        $chemin_dossier_migrations_vue_sql = app_path().'/Migrations/Vue_sql';
        $chemin_dossier_migrations_listes_libres = app_path().'/Migrations/Listes_libres';

        //suppression dans le dossier Migrations
        if(!\File::isDirectory($chemin_dossier_migrations))
            \File::makeDirectory($chemin_dossier_migrations, 0777, true, true) ;

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
            return false;

        if(is_file($chemin_dossier_migrations."/".$nom.".php")){

            unlink($chemin_dossier_migrations."/".$nom.".php");
        }

        //suppression dans le dossier Migrations/Vue_sql
        if(!\File::isDirectory($chemin_dossier_migrations_vue_sql))
            \File::makeDirectory($chemin_dossier_migrations_vue_sql, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations/Vue_sql
        if(!is_writable($chemin_dossier_migrations_vue_sql))
            return false;

        if(is_file($chemin_dossier_migrations_vue_sql."/".$nom.".php")){

            unlink($chemin_dossier_migrations_vue_sql."/".$nom.".php");
        }

        // On vérifie les droits sur le dossier app/Migrations/Liste_libres_fiches
        if(!is_writable($chemin_dossier_migrations_listes_libres))
            return false;

        if(is_file($chemin_dossier_migrations_listes_libres."/".$nom.".php")){

            unlink($chemin_dossier_migrations_listes_libres."/".$nom.".php");
        }

        //Suppression de la table vue_sql
        $vue = modele('vue_sql')->where('nom_sql', $nom)->first();
        
        if($vue)
            $vue->delete();
        
        //Suppression des champs libres
        $champ_libres = Champ_libre::where('type_element',$nom)->get();

        foreach($champ_libres as $champ_libre){

            $champ_libre->delete();
        }

        //Suppresion de la table
        $table_libre = Table_libre::where('type_element',$nom)->first();

        if($table_libre)
            $table_libre->delete();

        // Suppression de la liste principale
        $liste_principale = Liste_libre::where('type_element', $nom)
            ->where(function($requete) {
                $requete->whereNull('id_rapport')
                ->orWhere('id_rapport','');
            })->first();

        if(!empty($liste_principale)) {

            $rapport_standard = Rapport_management::rapport_standard($liste_principale);

            if ($rapport_standard === true) {

                $liste_principale->inactif = 1;
                $liste_principale->save();

                Liste_libre_management::generer_fichier_migration_liste_libre($liste_principale->id);
            } else {

                // on supprime la ligne en bdd
                $liste_principale->delete();

                $id_liste_libre = $liste_principale->id;

                Colonne::where('liste_libre_id', $id_liste_libre)->delete();
                Liste_libre_filtre::where('liste_libre_id', $id_liste_libre)->delete();
                Liste_libre_calcul::where('liste_libre_id', $id_liste_libre)->delete();
                Liste_libre_couleur::where('liste_libre_id', $id_liste_libre)->delete();
                Liste_libre_autresvues::where('liste_libre_id_1', $id_liste_libre)->delete();
            }
        }

        $retour = true;
        
        //Suppresion de la vue
        try{
            DB::select('DROP VIEW '.$nom);
        }
        catch(\Exception $exception){

            $retour = true;
        }

        return $retour;
    }


    /**
     *
     * Permet de récupérer les champs libres des vues
     *
     */
    public function recuperer_champs_libres_vue($champ_libre){

        $champs_libres = Champ_libre::where('type_element_origine',$champ_libre->type_element)
            ->where('nom_sql_origine',$champ_libre->nom_sql)
            ->get();

        return $champs_libres;

    }

    /**
     *
     * Permet de récupérer le type élément par défaut d'un type élément si celui si est une vue
     *
     */
    public function recupere_type_element($type_element){

        $table_libre = table_libre($type_element);

        if(!empty($table_libre) && $table_libre->vue_sql == 1){

            $vue_sql = modele('vue_sql')->where('nom_sql',$type_element)->first();

            if($vue_sql != null)
                $type_element = $vue_sql->table_par_defaut;
        }

        return $type_element;
    }


    /**
     *
     * Permet de gérer la création des vues pour un select simple
     *
     */
    public function generer_vue_sql_select($vue_sql){

        $type_vue = $vue_sql->nom_sql;

        $table = Table_Libre::where('nom_table_sql', $type_vue)->first();

        $tables = explode(',', $vue_sql->tables);
        $alias = explode(',', $vue_sql->alias_tables);
        $joins = explode(',', $vue_sql->joins);

        $server_ip = $_SERVER['SERVER_ADDR'];

        $requete = "CREATE OR REPLACE SQL SECURITY INVOKER VIEW " . $type_vue . " AS SELECT ";

        $compteur_id = 0;
        $compteur_chaine_tags_recherche = 0;

        $ids = '';
        $chaine_tags_recherche = ",CONCAT(";
        $champs = '';

        foreach ($tables as $type_element) {

            $colonne_chaine_tags_recherche_existe = modele($type_element)->getConnection()
                ->getSchemaBuilder()
                ->hasColumn(modele($type_element)->getTable(),'chaine_tags_recherche');

            //On gére le cas du chaine_tags_recherche et des ids

            if ($compteur_id == 0) {

                if(array_search($type_element,$tables) !== false && isset($alias[array_search($type_element,$tables)]))
                    $ids .= $alias[array_search($type_element,$tables)] . ".id AS id ";
                else
                    $ids .= $type_element . ".id AS id ";

            } else {

                if(array_search($type_element,$tables) !== false && isset($alias[array_search($type_element,$tables)]))
                    $ids .= "," . $alias[array_search($type_element,$tables)] . ".id AS " . $type_element . "_id ";
                else
                    $ids .= "," . $type_element . ".id AS " . $type_element . "_id ";
            }

            if($compteur_chaine_tags_recherche == 0 && $colonne_chaine_tags_recherche_existe) {
                if(array_search($type_element,$tables) !== false && isset($alias[array_search($type_element,$tables)]))
                    $chaine_tags_recherche .= 'COALESCE('.$alias[array_search($type_element,$tables)] . ".chaine_tags_recherche,'')";
                else
                    $chaine_tags_recherche .= 'COALESCE('.$type_element . ".chaine_tags_recherche,'')";

                $compteur_chaine_tags_recherche++;
            }

            else{
                if($colonne_chaine_tags_recherche_existe) {
                    if(array_search($type_element,$tables) !== false && isset($alias[array_search($type_element,$tables)]))
                        $chaine_tags_recherche .= ",' ',COALESCE(" .$alias[array_search($type_element,$tables)] . ".chaine_tags_recherche,'')";
                    else
                        $chaine_tags_recherche .= ",' ',COALESCE(" .$type_element . ".chaine_tags_recherche,'')";

                    $compteur_chaine_tags_recherche++;
                }

            }

            $compteur_id++;

        }

        $chaine_tags_recherche .=") as chaine_tags_recherche";

        if (!$table) {

            Table_libre_management::enregistre($type_vue, array(

                'nom_table' => $vue_sql->nom ?? $vue_sql->nom_sql,
                'element' => $type_vue,
                'element_pluriel' => $type_vue,
                'vue_sql' => 1,

            ), $type_vue);

        }

        // On gére les champs libres en fonction de si on est en création de la vue ou en modification

        if(Champ_libre::where('type_element', $type_vue)->first()){

            $champs_libres = Champ_libre::where('type_element', $type_vue)->where('type', '>=', 0)->get();

            foreach ($champs_libres as $champ_libre) {

                $champ_libre_initial = Champ_libre::where('type_element',$champ_libre->type_element_origine)
                    ->where('nom_sql',$champ_libre->nom_sql_origine)
                    ->first();

                self::gestion_champ_libre($type_vue, $champ_libre_initial);

                if(array_search($champ_libre_initial->type_element,$tables) !== false && isset($alias[array_search($champ_libre_initial->type_element,$tables)]))
                    $element = $alias[array_search($champ_libre_initial->type_element,$tables)] . '.' . $champ_libre_initial->nom_sql;
                else
                    $element = $champ_libre_initial->type_element . '.' . $champ_libre_initial->nom_sql;

                $nom_sql = $champ_libre_initial->type_element . '_' . $champ_libre_initial->nom_sql;

                $champs .= "," . $element . " AS " . $nom_sql;

            }
        }

        else {
            foreach ($tables as $cle => $type_element) {

                $champs_libres = Champ_libre::where('type_element', $type_element)
                    ->whereIn('nom_sql',['cree_le','cree_par','modifie_le','modifie_par'])
                    ->get();

                foreach ($champs_libres as $champ_libre) {

                    if(isset($alias[$cle]))
                        $element = $alias[$cle] . '.' . $champ_libre->nom_sql;
                    else
                        $element = $type_element . '.' . $champ_libre->nom_sql;

                    $nom_sql = $type_element . '_' . $champ_libre->nom_sql;


                    self::gestion_champ_libre($type_vue, $champ_libre);

                    $champs .= "," . $element . " AS " . $nom_sql;

                }
            }
        }

        $requete.= $ids . $chaine_tags_recherche .$champs;
		
		$joins_active = false;
        foreach($tables as $cle => $table) {
            if(!$joins_active) {
                $requete .= " FROM " . $table;

                if (isset($alias[$cle]))
                    $requete .= " " . $alias[$cle];
            }
            else {
                if(isset($joins[$cle-1])) {
                    $requete .= " LEFT JOIN " . $table;

                    if(isset($alias[$cle]))
                        $requete .= " ".$alias[$cle];

                    $requete .= " ON ".$joins[$cle-1];
                }
            }

            $joins_active = true;
        }

        $inactif_where_initialise = false;
		
		foreach($tables as $type_element){

            $colonne_inactif_existe = modele($type_element)->getConnection()
                ->getSchemaBuilder()
                ->hasColumn(modele($type_element)->getTable(),'inactif');

            if($colonne_inactif_existe) {

                if($inactif_where_initialise == true)
                    $requete .=" AND ";
                else
                    $requete .=" WHERE ";

                if(array_search($type_element,$tables) !== false && isset($alias[array_search($type_element,$tables)]))
                    $requete .="(" . $alias[array_search($type_element,$tables)] . ".inactif = 0 OR " . $alias[array_search($type_element,$tables)] . ".inactif IS NULL)";
                else
                    $requete .= "(" . $alias[array_search($type_element,$tables)] . ".inactif = 0 OR " . $alias[array_search($type_element,$tables)] . ".inactif IS NULL)";

                $inactif_where_initialise = true;
            }
        }

        if($inactif_where_initialise == false)
            $requete .=" WHERE ";

        if(!empty($vue_sql->autres_conditions))
            $requete.= " ".$vue_sql->autres_conditions;

        $retour = true;

		try{
            DB::select($requete);
        }
        catch(\Exception $exception){

            $retour = $exception->getMessage();
        }

        return $retour;
    }

    /**
     *
     * Permet de gérer la création des vues pour un union
     *
     */
    public function generer_vue_sql_union($vue_sql){

        $type_vue = $vue_sql->nom_sql;

        if($vue_sql->requete == null || $vue_sql->requete == ''){

            return true;
        }

        $table = Table_Libre::where('nom_table_sql', $type_vue)->first();

        $requete = $vue_sql->requete;
        $server_ip = $_SERVER['SERVER_ADDR'];

        //Injection de la configuration des droits sur la vue : On clean au cas ou il y a déja un truc
        $requete = str_replace(" SQL SECURITY INVOKER ", "",  $requete);
        $requete = preg_replace("/DEFINER\s*=[`']?[\w\-\_\.]+[`']?@[`']?[\w\-\_\.]+[`']?/i", " ", $requete);

        //Puis on ajoute le bon bloc
        $requete = str_replace("VIEW", " SQL SECURITY INVOKER VIEW", $requete);

        if (!$table) {

            Table_libre_management::enregistre($type_vue, array(

                'nom_table' => $vue_sql->nom ?? $vue_sql->nom_sql,
                'element' => $type_vue,
                'element_pluriel' => $type_vue,
                'vue_sql' => 1,

            ), $type_vue);

        }

        if(Champ_libre::where('type_element', $type_vue)->first()){

            $champs_libres = Champ_libre::where('type_element', $type_vue)
                ->whereNotNull('type_element_origine')
                ->whereNotNull('nom_sql_origine')
                ->get();

            $champs_libres_type_element_origine = array_unique($champs_libres->pluck('type_element_origine')->toArray());

            $champs_libres_initial_par_type_element = Champ_libre::select(DB::raw("CONCAT(type_element,'.',nom_sql) as type_element_nom_sql"),'eden_champslibres.*')->whereIn('type_element',$champs_libres_type_element_origine)
                    ->get()->keyBy('type_element_nom_sql');

            foreach($champs_libres as $champ_libre){

                unset($champs_libres_initial_par_type_element[$champ_libre->type_element_origine.'.'.$champ_libre->nom_sql_origine]->type_element_nom_sql);

                self::gestion_champ_libre($champ_libre->type_element, $champs_libres_initial_par_type_element[$champ_libre->type_element_origine.'.'.$champ_libre->nom_sql_origine], array(),$champ_libre);

            }

        }

        $requete = str_replace('#colonnes_eden#','id,chaine_tags_recherche',$requete);

        $this->gestion_liste_libre($vue_sql);

        $retour = true;

        try{
            $requetes = explode(';',$requete);
            foreach($requetes as $requete) {
                if(!empty($requete))
                    DB::select($requete);
            }
        }
        catch(\Exception $exception){

            $retour = $exception->getMessage();
        }

        return $retour;
    }

    public function gestion_liste_libre($vue_sql){

        $liste = Liste_libre::where('type_element',$vue_sql->nom_sql)->first();

        if($liste == null){

            $service_traduction = service('traduction');
            $liste = new Liste_libre();
            $liste->type_element = $vue_sql->nom_sql;
            $liste->export = 0;
            $liste->save();

            $champs_libres = Champ_libre::where('type_element',$vue_sql->nom_sql)->get();

            $compteur = 0;
            
            foreach($champs_libres as $champ_libre) {

                $base_index_traduction = ['liste', $vue_sql->nom_sql, 'colonne', $champ_libre->nom_sql];
                $colonne = new Colonne;

                $colonne->liste_libre_id = $liste->id;
                $colonne->nom = $champ_libre->nom;
                $colonne->valeur = '#'.$champ_libre->nom_sql.'#';
                $colonne->ordre = $compteur;
                $colonne->type = 'standard';
                $colonne->index_traduction = $service_traduction
                    ->calcul_index_traduction(5, $base_index_traduction, ['nom' => $champ_libre->nom]);
                $colonne->save();

                $compteur++;
            }

            // On génère la migration spécifique
            Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);
        }
    }
}

