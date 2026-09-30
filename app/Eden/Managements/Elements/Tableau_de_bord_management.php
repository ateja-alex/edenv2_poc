<?php

namespace App\Eden\Managements\Elements;

class Tableau_de_bord_management extends Element_management {

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        $index_traductions = modele('traduction_index')
            ->where('index','LIKE','tableau_de_bord.'.$this->modele->id.'.filtres.%')->get()
            ->pluck('index')->toArray();

        $traduction_service = service('traduction');
    
        if(!empty($this->modele->filtres)){

            $filtres = json_decode($this->modele->filtres,true);

            $changement = false;

            foreach($filtres as &$filtre){

                if(!empty($filtre['nouveau'])){

                    unset($filtre['nouveau']);

                    $index_traduction = service('traduction')->calcul_index_traduction(
                        11,
                        array(
                            'tableau_de_bord',
                            $this->modele->id,
                            'filtres',
                            $filtre['id']
                        ),
                        array(
                            'nom' => $filtre['nom'],
                        )
                    );

                    $filtre['index_traduction'] = $index_traduction;
                    unset($filtre['nom']);

                    $changement = true;
                }
                else{
                    unset($index_traductions[array_search($filtre['index_traduction'].'.nom',$index_traductions)]);
                }
            }

            if($changement){
                $this->modele->filtres = json_encode($filtres);
                $this->modele->save();
            }
        }

        foreach($index_traductions as $index_traduction){
            $traduction_service->supprime_index_traduction(str_replace('.nom','',$index_traduction));
        }

        $ids_filtres = collect($filtres)->pluck('id')->toArray();

        $correspondances_a_supprimer = modele('tableau_de_bord_contenu_correspondance_filtres')
            ->select('tableau_de_bord_contenu_correspondance_filtres.*')
            ->join('tableau_de_bord_contenu','tableau_de_bord_contenu.id','=','tableau_de_bord_contenu_correspondance_filtres.tableau_de_bord_contenu')
            ->where('tableau_de_bord',$this->modele->id)
            ->whereNotIn('id_filtre_tableau_de_bord',$ids_filtres)
            ->get();
        
        foreach($correspondances_a_supprimer as $correspondance_a_supprimer){
            management('tableau_de_bord_contenu_correspondance_filtres',
                $correspondance_a_supprimer->id, $correspondance_a_supprimer)->supprime();
        }

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function liste_colonnes_options(){

        $options = parent::liste_colonnes_options();

        $options[] = 'lien';

        return $options;
    }

    public function filtres(){

        $filtres = array();

        $filtres_modeles = json_decode($this->modele->filtres,true) ?? [];

        $types_comptatibles = [
            'filtre-date' => [4,5],
            'filtre-recherche-element' => [42,10],
            'filtre-montant' => [2,3],
            'filtre-texte' => [0],
        ];

        foreach($filtres_modeles as $filtre_modele){

            $type_filtre = $filtre_modele['type'];

            if($type_filtre == 'filtre-recherche-element'){
                if($filtre_modele['type_element_ajax'] == 'utilisateur')
                    $type_filtre = 'filtre-utilisateur';
                else if($filtre_modele['type_element_ajax'] == 'famille')
                    $type_filtre = 'filtre-famille';
            }

            $filtre = array(
                'id' => $filtre_modele['id'],
                'nom_sql' => $filtre_modele['id'],
                'index_traduction' => $filtre_modele['index_traduction'].'.nom',
                'type_filtre' => $type_filtre,
                'compatibilites' => [
                    'type' => $types_comptatibles[$filtre_modele['type']] ?? []
                ],
            );

            if($filtre_modele['type'] == 'filtre-recherche-element'){
                $filtre['compatibilites']['type_element_ajax'] = $filtre_modele['type_element_ajax'];
                $filtre['modele'] = [
                    'type' => 42,
                    'type_element_ajax' => $filtre_modele['type_element_ajax']
                ];
            }

            $filtres[] = $filtre;
        }

        return $filtres;
    }
}