<?php

namespace App\Eden\Managements\Elements;

class Tableau_de_bord_contenu_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false){

        if(array_key_exists('correspondances_filtres',$modifications))
            $this->correspondances_filtres = $modifications['correspondances_filtres'] == 'false' ? [] : $modifications['correspondances_filtres'];

        return parent::enregistre($modifications, $modele);
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if(isset($this->correspondances_filtres)) {

            $correspondances_actuelles = $this->correpondances_filtres();

            foreach ($this->correspondances_filtres as $correspondance) {

                if (!empty($correspondance['id'])) {

                    $correspondance_actuel = $correspondances_actuelles->where('id', $correspondance['id']);

                    $correspondances_actuelles->forget(array_keys($correspondance_actuel->toArray())[0]);

                    $correspondance_actuel = $correspondance_actuel->first();

                    $management = management('tableau_de_bord_contenu_correspondance_filtres',
                        $correspondance_actuel->id, $correspondance_actuel);
                } else
                    $management = management('tableau_de_bord_contenu_correspondance_filtres');

                $management->enregistre([
                    'tableau_de_bord_contenu' => $this->modele->id,
                    'id_filtre_tableau_de_bord' => $correspondance['id_filtre_tableau_de_bord'],
                    'nom_sql_compatible' => $correspondance['nom_sql_compatible'],
                ]);
            }

            foreach ($correspondances_actuelles as $correspondance_actuelle) {

                management('tableau_de_bord_contenu_correspondance_filtres',
                    $correspondance_actuelle->id, $correspondance_actuelle)->supprime();
            }
        }
    }

    public function correpondances_filtres(){

        return modele('tableau_de_bord_contenu_correspondance_filtres')
            ->where('tableau_de_bord_contenu',$this->modele->id)
            ->get();
    }
}