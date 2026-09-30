<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;

class Vue_sql_management extends Element_management {


    /**
     *
     * On enregistre la table par defaut
     *
     */
    public function enregistre($modifications = array(), $modele = false){
        if($modele === false && isset($this->modele) && $this->modele->exists === true)
            $modele = $this->modele;

        if(empty($modifications['nom_sql']) && empty($modele->nom_sql))
            return traduction('messages.php.vue_sql.nom_invalide');

        if(empty($modele)){

            $table_libre = Table_libre::where('nom_table_sql',$modifications['nom_sql'])->first();

            $vue_sql = modele('vue_sql')->where('nom_sql',$modifications['nom_sql'])->first();

            if((!empty($table_libre) && $table_libre->vue_sql != 1) || !empty($vue_sql))
                return traduction('messages.php.vue_sql.nom_deja_utilise');
        }

        if(!empty($modele) && isset($modifications['nom_sql']) && $modifications['nom_sql'] != $modele->nom_sql)
            return traduction('messages.php.vue_sql.nom_sql_non_modifiable');

        if((!empty($modele) && $modele->type_de_vue != 1 ) || (isset($modifications['type_de_vue']) && $modifications['type_de_vue'] != 1)) {

            if(isset($modifications['tables'])) 
                $tables = $modifications['tables'];
            else if($modele)
                $tables = $modele->tables;

            $tables = explode(',', $tables);

            if (isset($tables[0]))
                $modifications['table_par_defaut'] = $tables[0];
        }

        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true)
            return $retour;

        $retour = service('vue_sql')->generer_vues_sql(array($this->modele));

        return array_values($retour)[0];
    }

    public function supprime($modele = false){
        
        $rapports = Rapport_libre::where('type_element', $this->modele->nom_sql)
            ->first();

        $listes = Liste_libre::where('type_element', $this->modele->nom_sql)
            ->whereNotNull('id_rapport')
            ->where('id_rapport','!=','')
            ->first();

        if($rapports || $listes)
            return traduction('messages.php.fiche_vue_sql.suppression_impossible');
        
        $retour = parent::supprime($modele);

        if($retour !== true)
            return $retour;

        $retour = service('vue_sql')->supprimer_vue($this->modele);

        return $retour;

    }
    
    public function generer_migration($modifications = false){

        if (defined('migration_en_cours'))
            return true;

        // On vérifie que le dossier migration existe bien en spécifique
        $chemin_dossier_vue = app_path().'/Migrations/Vue_sql';

        //s'il n'existe pas, on le crée
        if(!\File::isDirectory($chemin_dossier_vue))
            \File::makeDirectory($chemin_dossier_vue, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations/Vue_sql
        if(!is_writable($chemin_dossier_vue))
            return false;

        $champs_libres = champs_libres('vue_sql');

        $champs_a_eviter = array('modifie_le','cree_le','cree_par','modifie_par','cle_externe');

        $texte =
            '<?php 
        return [
            ';
        foreach ($champs_libres as $champ) {
            $clef = $champ->nom_sql;
            $valeur= $this->modele->{$clef};
            if(!in_array($clef, $champs_a_eviter)) {
                $valeur = str_replace("'", "\'", $valeur);
                $texte = $texte . '\'' . $clef . '\' => \'' . $valeur . '\',
                ';
            }
        }

        $texte = $texte.'];';
        
        // On enregistre le nouveau fichier de migration et on écrase le fichier s'il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Vue_sql/'.$this->modele->nom_sql.'.php';

        // Si le fichier existe, on le supprime
        if (file_exists($chemin_avec_nom_document) == true)
            unlink($chemin_avec_nom_document);

        // Enregistrement du fichier
        $fichier = fopen($chemin_avec_nom_document, "x+");
        fputs($fichier, $texte );
        fclose($fichier);

        return true;
    }
    
    protected function methodes_post_modification($modele, $modele_avant, $modifications){
        
        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $this->generer_migration();
    }

}