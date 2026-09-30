<?php

namespace App\Eden\Managements\Rapports;

/**
 * Gestion des rapports
 */
class Rapport_graphique_funnel_management extends Rapport_base_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct($id_rapport = '', $titre = '', $sous_titre = '') {

        parent::__construct($id_rapport, $titre, $sous_titre);

        $this->vue_standard_js = 'rapport_graphique_funnel';
        $this->legende = array();
        $this->serie = array();
        
    }

    /**
     *
     * Ajoute une valeur à la légende
     *
     */
    public function legende($valeur) {

        $this->legende[] = $valeur;

        return true;
    }

    /**
     *
     * Crée une nouvelle série de données
     *
     */
    public function serie($nom) {

        $this->serie['nom'] = $nom;

        $this->serie['valeurs'] = array();

        return true;
    }


    /**
     *
     * Ajoute une valeur à la série
     *
     */
    public function valeur($valeur) {

        $this->serie['valeurs'][] = $valeur;

        return true;
    }

    /**
     *
     * Récupère les données pour la vue
     *
     */
    public function parametres_pour_vue($ajax = false) {

        parent::parametres_pour_vue($ajax);

        $this->parametres_pour_vue['legende'] = $this->legende;
        $this->parametres_pour_vue['serie'] = $this->serie;

        $this->parametres_pour_vue['props_composant']['series_rapport'] = (object) ($this->serie ?? []);
        $this->parametres_pour_vue['props_composant']['nombre_decimales_recap'] = $this->parametrage_rapport_libre['nombre_decimales_recap'] ?? 0;
    }

    /**
     *
     * On génère un rapport libre paramétré via l'interface
     *
     */
    public function genere($ajax = false) {

        if(empty($this->parametrage_rapport_libre))
            return parent::genere($ajax);

        // les filtres génériques du rapport
        $type_element = $this->rapport_libre->type_element;
        $this->applique_filtres(management($type_element));
        $this->recupere_valeurs_filtres(management($type_element));

        $variable = $this->parametrage_rapport_libre['variable'];

        $serie = $this->parametrage_rapport_libre['serie'];

        $this->serie(traduction($serie['index_traduction'].'.nom'));

        $filtre = modele('recherche_avancee')
            ->where('type','rapport')
            ->where('id_cible',$this->rapport_libre->id_rapport)
            ->first();

        if(!empty($filtre))
            $serie['filtre'] = management('recherche_avancee',$filtre->id,$filtre)->structure();

        $serie_management = new Serie_management($serie, $this);

        $resultats = $serie_management->recupere_resultats();
        $frequences = $serie_management->legende($type_element, $variable, $resultats);
        
        $resultats_definitifs = array();

        foreach ($resultats as $resultat) {

            if(empty($resultat[$variable])) {

                $resultat[$variable] = 0;
            }

            if (!isset($resultats_definitifs[$resultat[$variable]]))
                $resultats_definitifs[$resultat[$variable]] = 0;

            $resultats_definitifs[$resultat[$variable]] += $resultat['total_1'];
        }

        foreach ($frequences as $id_valeur => $valeur) {

            if (!isset($resultats_definitifs[$id_valeur]) || $resultats_definitifs[$id_valeur] == 0)
                continue;

            $this->legende($valeur);

            $this->valeur($resultats_definitifs[$id_valeur]);
        }

        return parent::genere($ajax);
    }

}
