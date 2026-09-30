<?php

namespace App\Eden\Managements\Elements;

class Eden_sous_formulaire_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false){
        
        if($this->existe())
            return parent::enregistre($modifications, $modele);

        $nom_sous_formulaire_tmp = $modifications['nom_sous_formulaire'];
        $nom_sous_formulaire = $modifications['nom_sous_formulaire'];

        $count = 1;

        while(modele('eden_sous_formulaire')->where('nom_sous_formulaire', $nom_sous_formulaire_tmp)->count() > 0) {

            $nom_sous_formulaire_tmp = $nom_sous_formulaire.'_'.$count;

            $count++;
        }

        $modifications['nom_sous_formulaire'] = $nom_sous_formulaire_tmp;
        
        return parent::enregistre($modifications, $modele);
        
    }
    
    public function modele_par_defaut(){

        $modele = parent::modele_par_defaut();

        $modele->data_vue = [];
        $modele->remplacement_supplementaire = [];

        return $modele;

    }

    /**
     *
     * Supprime un élément
     *
     * @param $modele le modèle que l'on veut supprimer (si non fourni, on se base sur le modèle lié au management)
     *
     * @return true si tout va bien, une erreur (string) si il y a une erreur (impossible de supprimer)
     *
     */
    public function supprime($modele = false) {

        if($modele === false) {

            if(isset($this->modele) && $this->modele->exists === true)
                $modele = $this->modele;
            else {

                exception("Erreur lors de la suppression : le modèle n'a pas été trouvé");
            }
        }

        if($this->verifie_profil_suppression($modele) !== true)
            return traduction('message.php.droits.suppression_impossible');

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

        $chemin = app_path().'/Eden/Migrations/Sous_formulaire/'. $modele->nom_sous_formulaire .'.php';
        $chemin_spe = app_path().'/Migrations/Sous_formulaire/'. $modele->nom_sous_formulaire .'.php';
        
        if(!file_exists($chemin) && file_exists($chemin_spe))
            unlink($chemin_spe);
        else if(file_exists($chemin) && !file_exists($chemin_spe))
            return true;

        return true;
    }

}