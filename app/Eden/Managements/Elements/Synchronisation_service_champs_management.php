<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;

class Synchronisation_service_champs_management extends Element_management{

    private $origine = null;

    public function origine(){

        if(!isset($this->origine))
            $this->origine = management('synchronisation_service_element',$this->modele->synchronisation_service_element_id);

        return $this->origine;
    }

    public function types_evenements($modele){

        $this->modele = $modele;

        $types_evenements = $modele->types_evenements ?? [];

        if(empty($modele->nom_externe)){

            $table_externe = $this->origine()->table_externe();

            $disponibilites = collect($table_externe['disponibilites'] ?? [])
                ->where('type_synchronisation', $this->modele->type_synchronisation)->first();

            if(sizeof($disponibilites['types_evenements'] ?? []) == 1)
                $types_evenements = $disponibilites['types_evenements'];
        }
        else{

            $champ_externe = $this->origine()->origine()->service()->champ_externe($this->origine()->modele->type_externe,$modele->nom_externe);

            $disponibilites = array_values(collect($champ_externe['disponibilites'])
                ->where('type_synchronisation', $this->origine()->modele->type_synchronisation)
                ->where('sens', $this->modele->sens)->toArray());

            if(sizeof($disponibilites) == 1)
                $types_evenements_obligatoires = [$disponibilites[0]['type_evenement']];
            else
                $types_evenements_obligatoires = collect($disponibilites)->filter(function($disponibilite){
                    return !empty($disponibilite['obligatoire']);
                })->pluck('type_evenement')->toArray();

            $types_evenements = array_merge($types_evenements_obligatoires, $modele->types_evenements);
        }

        return $this->champ('types_evenements')->affiche($types_evenements);
    }

    public function enregistre($modifications = array(), $modele = false){

        $champ_externe = $modifications['nom_externe'] ?? $this->modele->nom_externe ?? null;

        if(!empty($modifications['cle_mise_a_jour']) && empty($champ_externe))
            return traduction('messages.php.synchronisation_service_champs.erreur_cle_mise_a_jour');

        $filtrages = false;

        if(isset($modifications['filtrages'])){
            $filtrages = $modifications['filtrages'] ?? [];

            $filtrages = array_map(function($filtrage){
                return json_decode($filtrage, true);
            },$filtrages);

            unset($modifications['filtrages']);
        }

        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true || $filtrages === false)
            return $retour;

        $recherches_avancees = modele('recherche_avancee')
            ->where('type', $this->_type_element.'_'.$this->modele->id)
            ->get()->keyBy('id_cible');

        foreach($filtrages as $filtrage){

            $id_cible = $filtrage['id_cible'];
            $type_element = $filtrage['type_element'];
            $structure = $filtrage['structure'];

            if(empty($structure))
                continue;

            if(isset($recherches_avancees[$id_cible])){
                $recherche_avancee = $recherches_avancees[$id_cible];
                $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);
                unset($recherches_avancees[$id_cible]);
            }
            else
                $management_recherche_avancee = management('recherche_avancee');

            $management_recherche_avancee->enregistre([
                'type' => $this->_type_element.'_'.$this->modele->id,
                'type_element' => $type_element,
                'id_cible' => $id_cible,
                'structure' => $structure
            ]);
        }

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        return true;
    }

    public function filtrages(){

        return modele('recherche_avancee')
            ->where('type', $this->_type_element.'_'.$this->modele->id)
            ->get()->keyBy('id_cible');
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        $this->oublie_cache_synchronisation($modele);

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function methodes_post_suppression($modele){

        $this->oublie_cache_synchronisation($modele);

        return parent::methodes_post_suppression($modele);
    }

    protected function oublie_cache_synchronisation($modele){

        $type_element = modele('synchronisation_service_element')
            ->where('id', $modele->synchronisation_service_element_id)
            ->value('type_element');

        if(!empty($type_element))
            oublie_cache_eden('synchronisations.'.$type_element);
    }

    public function valeur_champ_interne($management_origine){

        if(!empty($this->modele->valeur_dur) && $this->modele->sens == 0)
            return $this->modele->valeur_dur;

        $resultat_lien_champ = service('lien_champ')->valeurs($this->modele->nom_sql, [
            'type_element' => $management_origine->_type_element,
            'elements_ids' => [$management_origine->modele->id],
            'filtrages' => $this->filtrages(),
        ])[0] ?? null;

        $valeur = $resultat_lien_champ['valeur'] ?? null;

        $modele_champ_libre = $resultat_lien_champ['modele_champ_libre'] ?? null;

        if(!empty($modele_champ_libre) && ($modele_champ_libre->type == 7
            || ($modele_champ_libre->nom_sql == 'pdf' && in_array($modele_champ_libre->type_element, \App\Eden\Variables::$documents_gescom)))){

            if($modele_champ_libre->type == 7)
                return storage_path('app/public/'.\Illuminate\Support\Str::after($valeur, 'storage/'));

            $management_fichier = $resultat_lien_champ['management_element'] ?? $management_origine;

            return storage_path('app/'.$management_fichier->recupere_chemin_pdf());
        }

        if(!empty($this->modele->correspondances)){
            $correspondances = json_decode($this->modele->correspondances, true);

            $valeur = collect($correspondances)->where('valeur_interne', $valeur)->first()['valeur_externe'] ?? null;
        }
        if(is_numeric($valeur))
            $valeur = 0 + $valeur;
        else if(is_string($valeur))
            $valeur = strip_tags($valeur);

        return $valeur ?? '';
    }

    public function valeur_champ_externe($element){

        if(!empty($this->modele->valeur_dur) && $this->modele->sens == 1)
            return $this->modele->valeur_dur;

        $partie_nom_externe = explode('.',$this->modele->nom_externe);

        $nom_externe = array_pop($partie_nom_externe);

        $valeur_a_modifier = &$element;

        foreach($partie_nom_externe as $partie){

            if(!isset($valeur_a_modifier[$partie]))
                $valeur_a_modifier[$partie] = [];

            $valeur_a_modifier = &$valeur_a_modifier[$partie];
        }

        $valeur = $element[$nom_externe] ?? null;

        if(!empty($this->modele->correspondances)){

            $correspondances = json_decode($this->modele->correspondances, true);

            $valeur = collect($correspondances)->where('valeur_externe', $valeur)->first()['valeur_interne'] ?? null;
        }

        // symétrique de valeur_champ_interne() : si le champ local est de type 7 (pièce jointe), la
        // valeur externe est le contenu binaire du fichier reçu, pas encore un chemin - on le stocke
        // en reprenant exactement la convention d'Upload_controller::upload() (disque "public", nom
        // aléatoire, sans dossier), quel que soit le service externe utilisé.
        if(!empty($valeur)) {

            $modele_champ_libre = champ_libre_modele($this->origine()->modele->type_element, $this->modele->nom_sql);

            if(!empty($modele_champ_libre) && $modele_champ_libre->type == 7) {

                $extension = str_starts_with($valeur, '%PDF') ? 'pdf' : (str_starts_with(ltrim($valeur), '<') ? 'xml' : 'bin');
                $nom_fichier = \Illuminate\Support\Str::random(40).'.'.$extension;

                \Storage::disk('public')->put($nom_fichier, $valeur);

                $valeur = $nom_fichier;
            }
        }

        return $valeur;
    }
}