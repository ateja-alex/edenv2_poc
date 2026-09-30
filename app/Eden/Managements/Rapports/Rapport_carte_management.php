<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Managements\Rapports\Rapport_base_management;
use App\Eden\Models\Champ_libre;

class Rapport_carte_management extends Rapport_base_management {

    public function genere($ajax = false) {

        $adresses_par_type = array();

        $type_elements_affiche = [];

        if(!empty($this->parametrage_rapport_libre['types_elements_carte']))
            $type_elements_affiche = $this->parametrage_rapport_libre['types_elements_carte'];

        $managements = [];
        $champs_managements = [];
        $managements['adresse'] = management('adresse');

        $this->applique_filtres($managements['adresse']);
        $this->recupere_valeurs_filtres($managements['adresse']);

        foreach ($type_elements_affiche as $type_element) {
            $managements[$type_element] = management($type_element);
        }

        $champs_libres = Champ_libre::where('type_element', 'adresse')->where('type', 42)->whereIn('type_element_ajax', $type_elements_affiche)->get();

        $adresses = modele($this->rapport_libre->type_element)->select($this->rapport_libre->type_element.'.*')->whereNotNull($this->rapport_libre->type_element.'.'. $this->parametrage_rapport_libre['mappage_latitude'])->whereNotNull($this->rapport_libre->type_element.'.'. $this->parametrage_rapport_libre['mappage_longitude']);

        foreach ($champs_libres as $champ) {
            $adresses = $adresses->leftJoin($champ->type_element_ajax, $this->rapport_libre->type_element . '.' . $champ->nom_sql, '=', $champ->type_element_ajax . '.id')
                ->where(\DB::raw("coalesce(".$champ->type_element_ajax.".inactif, 0)"), 0);
        }
        if($this->rapport_libre->type_element != 'adresse')
            $adresses = $adresses->join('adresse', 'adresse.id', '=', $this->rapport_libre->type_element.'.id');
        foreach ($this->valeurs_filtre as $valeurs) {
            $id_filtre = $valeurs['id'];
            $valeurs = $valeurs['valeurs'];
            $filtre = $this->options[$id_filtre];
            if (empty($filtre['nom_sql']))
                continue;

            if(!isset($management_element_filtre->_type_element) || $management_element_filtre->_type_element != $filtre['type_element'])
                $management_element_filtre = $managements[$filtre['type_element']];

            if(!isset($champs_managements[$filtre['type_element'] . '.' . $filtre['nom_sql']]))
                $champs_managements[$filtre['type_element'] . '.' . $filtre['nom_sql']] = $management_element_filtre->champ($filtre['nom_sql']);

            $champ = $champs_managements[$filtre['type_element'] . '.' . $filtre['nom_sql']];
            $nom_sql = $filtre['nom_sql'];

            if(!empty($filtre['alias'])) {
                $champ->modele->alias_champ = $filtre['alias'];
                $nom_sql = $filtre['alias'];
            }

            $adresses = $adresses->where(function($query) use ($champ, $valeurs, $nom_sql, $filtre){
                return  $champ->applique_filtre_sur_requete($valeurs, $query);
            });
        }

        $filtres = modele('recherche_avancee')
            ->where('type',$this->rapport_libre->id_rapport.'.type_element')
            ->get()->keyBy('id_cible');

        foreach($filtres as $type_element => $filtre){
            $structure = management('recherche_avancee',$filtre->id,$filtre)->structure();

            $adresses = $adresses->where(function($requete) use ($type_element,$structure){
                management('recherche_avancee')->applique_filtrage($structure,$requete,$type_element);

                if($type_element != 'adresse')
                    $requete->orWhereNull($type_element . '.id');

                return $requete;
            });
        }

        if(request()->has('filtres_pour_fiche')) {
            foreach (request()->get('filtres_pour_fiche') as $colonne => $valeur) {

                $type_element = false;

                if(strpos($colonne, '.') !== false)
                    list($type_element, $colonne) = explode('.', $colonne);

                if (($type_element === false || $type_element == 'adresse') && $colonne == "id") {
                    $adresses = $adresses->where('adresse.id', $valeur);
                    continue;
                }

                if($type_element !== false && !empty($managements[$type_element]))
                    $managements_element = $managements[$type_element];
                else
                    $managements_element = $managements['adresse'];

                try {
                    $champ = $managements_element->champ($colonne);
                } catch (\App\Eden\Exceptions\Eden_exception $e) {
                    continue;
                }
                if (is_array($valeur) && isset($valeur['debut'])) {
                    if (!empty($valeur['variable']))
                        list($debut, $fin) = $champ->transforme_variable_date($valeur['variable']);
                    if (!empty($debut))
                        $valeur['debut'] = date('Y-m-d', strtotime($debut . ' -1 year'));
                    if (!empty($fin))
                        $valeur['fin'] = date('Y-m-d', strtotime($fin . ' -1 year'));

                    $valeur['variable'] = '';
                }

                if (!is_array($valeur))
                    $valeur = [$valeur];

                $adresses = $champ->applique_filtre_sur_requete($valeur, $adresses);
            }
        }

        $requete_couleurs = clone($adresses);

        $adresses = $adresses->get();

        $couleurs_ids = array();

        $filtres = modele('recherche_avancee')
            ->where('type',$this->rapport_libre->id_rapport.'.couleur')
            ->get()->keyBy('id_cible');

        if(!empty($this->parametrage_rapport_libre['couleurs'])) {

            $couleurs = collect($this->parametrage_rapport_libre['couleurs'])->keyBy('id')->toArray();

            foreach ($couleurs as $index => $couleur) {

                $requete_couleurs_tmp = clone($requete_couleurs);

                $requete_couleurs_tmp = $requete_couleurs_tmp->whereNotNull($couleur['type_element'].'.id');

                if(!empty($filtres[$index])){
                    $structure = management('recherche_avancee',$filtres[$index]->id,$filtres[$index])->structure();
                    management('recherche_avancee')->applique_filtrage($structure,$requete_couleurs_tmp,$couleur['type_element']);
                }

                $elements_couleurs = $requete_couleurs_tmp->select('adresse.id')->get()->pluck('id');

                foreach ($elements_couleurs as $element_id) {

                    if (!isset($couleurs_ids[$element_id]))
                        $couleurs_ids[$element_id] = [
                            'couleur' => empty($couleur['couleur']) ? '#FF6E6E' : $couleur['couleur'],
                            'legende' => empty($couleur['legende']) ? '' : $couleur['legende']
                        ];
                }
            }
        }

        foreach ($champs_libres as $champ) {
            $adresses_par_type[$champ->type_element_ajax] = [
                'adresses' => $adresses->where($champ->nom_sql, '!=', NULL),
                'nom_sql' => $champ->nom_sql,
            ];
        }

        $adresses_par_type_par_couleurs = [];

        foreach($adresses_par_type as $type_element => $adresses){

            $type_element_nom_sql = $adresses['nom_sql'];
            $adresses = $adresses['adresses'];

            $elements = modele($type_element)->whereIn('id', $adresses->pluck($type_element_nom_sql))->get()->keyBy('id');
            $nom_table = traduction('tables_libres.' . $type_element . '.nom_table');

            foreach($adresses as $adresse){

                if(empty($elements[$adresse->{$type_element_nom_sql}]))
                    continue;

                $texte_affichage = $this->parametrage_rapport_libre['affichage_info_bulle'] ?? "";
                $affichage = [];
                $element = management($type_element, $adresse->{$type_element_nom_sql}, $elements[$adresse->{$type_element_nom_sql}] ?? null);

                $matches = array();
                $regex = "/#([a-zA-Z0-9_.]*)#/";
                preg_match_all($regex, $texte_affichage, $matches);
                $variables = $matches[1];

                foreach ($variables as $variable) {
                    $variable_tmp = explode('.', $variable);
                    $type_element_variable = $variable_tmp[0];
                    $nom_sql = $variable_tmp[1];
                    $element_variable = $element->modele;

                    if($type_element_variable == 'adresse')
                        $element_variable = $adresse;
                    elseif($type_element_variable != $type_element) {
                        $affichage["#" . $variable . "#"] = "";
                        continue;
                    }

                    if(!isset($champs_managements[$type_element_variable . '.' . $nom_sql]))
                        $champs_managements[$type_element_variable . '.' . $nom_sql] = $managements[$type_element_variable]->champ($nom_sql);

                    $affichage["#" . $variable . "#"] = $champs_managements[$type_element_variable . '.' . $nom_sql]->affiche($element_variable->{$nom_sql});
                }

                $couleur = $couleurs_ids[$adresse->id] ?? ['couleur' => '#FF6E6E', 'legende' => $nom_table];

                $adresse->affichage_carte_google_map = $element->affiche_lien(str_replace(array_keys($affichage), $affichage, $texte_affichage));
                $adresse->icone_google_map = $element->retourne_icone_gmap();

                if(!isset($adresses_par_type_par_couleurs[$couleur['legende']]))
                    $adresses_par_type_par_couleurs[$couleur['legende']] = [];

                if(!isset($adresses_par_type_par_couleurs[$couleur['legende']][$couleur['couleur']]))
                    $adresses_par_type_par_couleurs[$couleur['legende']][$couleur['couleur']] = [];

                $adresses_par_type_par_couleurs[$couleur['legende']][$couleur['couleur']][] = $adresse;
            }
        }

        if(empty($adresses_par_type_par_couleurs))
            $adresses_par_type_par_couleurs = (object) [];

        $this->adresses_par_type = $adresses_par_type_par_couleurs;

        return parent::genere($ajax);
    }

    /**
     *
     * Récupère les données pour la vue
     *
     */
    public function parametres_pour_vue($ajax = false) {

        parent::parametres_pour_vue($ajax);

        $this->parametres_pour_vue['props_composant']['latitude'] = 48.864716;
        $this->parametres_pour_vue['props_composant']['longitude'] = 2.349014;

        if(!empty($this->parametrage_rapport_libre['positionnement']) && $this->parametrage_rapport_libre['positionnement'] == 'point') {
            $this->parametres_pour_vue['props_composant']['latitude'] = $this->parametrage_rapport_libre['latitude'];
            $this->parametres_pour_vue['props_composant']['longitude'] = $this->parametrage_rapport_libre['longitude'];
        } else {
            $this->parametres_pour_vue['props_composant']['centre_points'] = true;
        }

        $this->parametres_pour_vue['props_composant']['adresses_par_type'] = $this->adresses_par_type;
        $this->parametres_pour_vue['props_composant']['zoom'] = $this->parametrage_rapport_libre['zoom'] ?? 5;

        $this->parametres_pour_vue['props_composant']['desactiver_clusterisation'] = !empty($this->parametrage_rapport_libre['desactiver_clusterisation']);

        
        $this->parametres_pour_vue['props_composant']['mappage_latitude'] = $this->parametrage_rapport_libre['mappage_latitude'];
        $this->parametres_pour_vue['props_composant']['mappage_longitude'] = $this->parametrage_rapport_libre['mappage_longitude'];
    }
}