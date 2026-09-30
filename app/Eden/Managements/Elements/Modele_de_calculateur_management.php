<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champs_liste_formatee;

class Modele_de_calculateur_management extends Element_management {
    /**
     *
     * On défini le calculateur par défaut
     *
     */
    public function modele_par_defaut() {

        $modele = parent::modele_par_defaut();

        $modele->calculateur = [
            'calcul' => [
                'formule' => ''
            ],
            'ponderation' => [],
            'variables' => [],
            'quantite' => [],
            'erreur' => false,
        ];

        return $modele;

    }

    /**
     *
     * On surcharge la méthode enregistre pour gérer le calculateur
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if (!isset($modifications['calculateur'])) {
            return parent::enregistre($modifications, $modele);
        }
        
        $modifications['nom'] = $modifications['calculateur']['quantite']['designation'];
        $modifications['calculateur']['designation'] = $modifications['calculateur']['quantite']['designation'];

        if (empty($modifications['calculateur']['variables']) && empty($modifications['calculateur']['ponderation']))
            return traduction('messages.php.modele_de_calculateur.calculateur_vide');

        $erreur = false;

        if(isset($modifications['calculateur']['variables'])) {
            foreach ($modifications['calculateur']['variables'] as $variable) {
                if (empty($variable['nom']) || empty($variable['valeur']))
                    $erreur = traduction('messages.php.modele_de_calculateur.calculateur_variable_vide');
            }
        }

        if(isset($modifications['calculateur']['ponderation'])) {
            foreach ($modifications['calculateur']['ponderation'] as $ponderation) {
                if (empty($ponderation['conditions'])) {
                    $erreur = traduction('messages.php.modele_de_calculateur.calculateur_condition_invalide');
                    continue;
                }

                foreach ($ponderation['conditions'] as $condition) {
                    if ($condition['type'] == 0)
                        continue;

                    foreach ($condition['donnees'] as $donnee) {
                        if (empty($donnee['critere']) || empty($donnee['valeur'])) {
                            $erreur = traduction('messages.php.modele_de_calculateur.calculateur_condition_vide');
                            continue;
                        }
                    }
                }
            }
        }

        if ($erreur !== false)
            return $erreur;

        $modele_par_defaut = $this->modele_par_defaut()->toArray()['calculateur'];

        $ligne_a_eviter = ['cree_par', 'modifie_par', 'chaine_affichage', 'chaine_tags_recherche', 'cle_externe', 'cree_le', 'id', 'inactif', 'modifie_le'];

        foreach ($modele_par_defaut as $ligne_par_defaut => $valeur_par_defaut){

            if(!isset($modifications['calculateur'][$ligne_par_defaut]) && !in_array($ligne_par_defaut,$ligne_a_eviter))
                $modifications['calculateur'][$ligne_par_defaut] = $valeur_par_defaut;

        }

        $modifications['calculateur'] = json_encode($modifications['calculateur']);
        $retour = parent::enregistre($modifications, $modele);

        return $retour;

    }
}