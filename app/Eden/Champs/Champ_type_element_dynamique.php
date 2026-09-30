<?php

namespace App\Eden\Champs;

use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use DB;

class Champ_type_element_dynamique extends Champ {

    public string $type_filtre = 'filtre-type-element-dynamique';

    public function affiche($valeur = false){

        $type_element = $valeur;

		if($type_element === false)
			$type_element = $this->valeur;

        if(empty($type_element))
            return $type_element;

        $nom_table = table_libre($type_element)->nom_table;

        return $nom_table;
    }

    public function cree($valeur = null) {

        if($valeur !== null)
            $this->value($valeur);

        $classes_css = '';
        if (isset($this->modele->classes_css))
            $classes_css = $this->modele->classes_css;

        $classes_js = '';
        if (isset($this->modele->classes_js))
            $classes_js = $this->modele->classes_js;

        $paterne_champ = $this->paterne_champ();

        $contenu = json_decode($this->modele->contenu);

        $input = '<select @change="$emit(\'changement_type_element_dynamique\',{type_element : \''.$this->modele->type_element.'\',nom_sql: \''.$this->modele->nom_sql.'\'})" class="'.$classes_css.' '.$classes_js.'" '.$this->attributs().'>';

        foreach ($contenu as $element){

            if(!$element->valeur)
                continue;

            $input .= '<option value="'.$element->type_element.'">'.traduction('tables_libres.'.$element->type_element.'.nom_table').' ('.$element->type_element.')</option>';

        }

        $input .= "</select>";

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    /**
	 *
	 * Crée le filtre pour les listes
	 *
	 */
	public function cree_filtre_pour_liste($valeurs = false, $filtre = false) {

		$html = '<div class="css_liste_checkbox_popover" style="max-height:unset;"> ';

        $contenu = $this->modele->contenu;

        try{
            $contenu = json_decode($contenu);
        }
        catch(\Exception $e){
            $contenu = array();
        }

        $valeur_filtre = null;
        
        if(!empty($valeurs['element_id']['id'])) {
            $valeur_filtre = $valeurs['element_id']['id'];
            $valeur_type_element = $valeurs['element_id']['type_element'];
            $valeur_a_afficher = strip_tags(management($valeur_type_element, $valeur_filtre)->affiche());

        }

        $html .= '<div style="max-height:200px;overflow-y:auto;">';

        $html .= '
                <div class="form-check form-check-inline css_checkbox_popover">
                    <label class="d-flex align-items-center">

                        <input
                            class="form-check-input js_filtre_sur_liste"
                            type="checkbox"
                            type_filtre="checkbox_type_element_dynamique"
                            value="0"
                            ' . (!empty($valeurs['type_element']) && is_array($valeurs['type_element']) && in_array(0, $valeurs['type_element'], true) ? 'checked="checked"' : '') . '
                            nom_sql="' . $this->modele->nom_sql . '"
                            name="' . $this->modele->nom_sql . '[]"
                            >
                            &nbsp;&nbsp;
                        <div class="d-flex flex-column">
                            Sans Valeur
                        </div>

                    </label>
                </div>';

        foreach($contenu as $type_element) {

            if($type_element->valeur !== true)
                continue;

            $table_libre = table_libre($type_element->type_element);

            $html .= '
                    <div class="form-check form-check-inline css_checkbox_popover">
                        <label class="d-flex align-items-center">

                            <input
                                class="form-check-input js_filtre_sur_liste"
                                type="checkbox"
                                @change="recherche_pour_filtre_champ_element_dynamique($event,\''.$this->modele->nom_sql.'\')"
                                type_filtre="checkbox_type_element_dynamique"
                                value="' . $type_element->type_element . '"
                                ' . (!empty($valeurs['type_element']) && is_array($valeurs['type_element']) && in_array($type_element->type_element, $valeurs['type_element']) ? 'checked="checked"' : '') . '
                                nom_sql="' . $this->modele->nom_sql . '"
                                name="' . $this->modele->nom_sql . '[]"
                                >
                                &nbsp;&nbsp;
                            <div class="d-flex flex-column">
                                ' . $table_libre->nom_table . '
                            </div>

                        </label>
                    </div>';
        }

		$html .= '</div>';

        $html .= '<div>';

        $html .= '
			<div style="border-top:2px solid lightgrey" >
				<div class="d-flex align-items-center justify-content-between" style="padding:10px;">
					<span>'.traduction_blade('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche').'</span>
					<input type="text" nom_sql="'.$this->modele->nom_sql.'" id="recherche_'.$this->modele->nom_sql.'" style="height: 24px; border: 1px solid #e4e0e0; margin: 0px 10px; margin-left: 0px; width: 200px;"

						@keyup="recherche_pour_filtre_champ_element_dynamique($event,\''.$this->modele->nom_sql.'\')">

				</div>

				<div class="css_liste_checkbox_popover" style="max-height:200px;overflow-y:auto;">';

        $html .= '	<div class="form-check form-check-inline css_checkbox_popover" style="display:block">
						<label>
							<input class="form-check-input js_filtre_sur_liste annule_filtre" @click="supprimer_recherche_pour_filtre_champ_element_ajax(\''.$this->modele->nom_sql.'\')" type_element_dynamique="" type_filtre="checkbox_element_id_dynamique" name="'.$this->modele->nom_sql.'" nom_sql="'.$this->modele->nom_sql.'" type="radio" value="0">
							'.traduction_blade('filtres.cree_filtre_pour_liste.champ_recherche_element.aucun_element_selectionne').'
						</label>
					</div>';

        if($valeur_filtre != null) {

			$html .= '<div id="valeur_par_defaut_'.$this->modele->nom_sql.'">
                            <div class="filtre_type_element_dynamique_titre">
                                '.traduction_blade('tables_libres.'.$valeur_type_element,'nom_table').'
                            </div>
                            <div class="form-check form-check-inline css_checkbox_popover" style="display:block">
    
                                <label>
                                    <input class="form-check-input js_filtre_sur_liste" checked="checked" type_filtre="checkbox_element_id_dynamique" type_element_dynamique="'.$valeur_type_element.'" name="'.$this->modele->nom_sql.'" nom_sql="'.$this->modele->nom_sql.'" type="radio" value="'.$valeur_filtre.'">
                                    '.$valeur_a_afficher.'
                                </label>
                            </div>
						</div>';
        }

        $html .= '<div v-for="(elements,type_element) in filtre.filtre_recherche_element" v-if="elements.length > 0" >
                        <div class="filtre_type_element_dynamique_titre">
                        '.traduction_blade("'tables_libres.'+type_element",'nom_table', true).'
                        </div>
                        <div v-for="element in elements" class="form-check form-check-inline css_checkbox_popover" style="display:block">
                            <label>
                                <input class="form-check-input js_filtre_sur_liste" type_filtre="checkbox_element_id_dynamique" :type_element_dynamique="type_element" name="'.$this->modele->nom_sql.'" nom_sql="'.$this->modele->nom_sql.'" type="radio" :value="element.id">
                                {{ element.affichage_pour_recherche }}
                            </label>
					    </div>
                    </div>
                    ';

        $html .= '	</div>

        </div>';

        $html .= '</div>';

		$html .= '</div>';

		return $html;
	}

    public function applique_filtre_sur_requete($filtre, $requete) {

		if(empty($filtre['types_elements']))
			return $requete;

        $alias_champ = $this->alias_champ_requete();

        $champ_element_id_dynamique = $this->champ_element_id_dynamique();

        if(!empty($filtre['elements_ids']) && $champ_element_id_dynamique !== null){

            $elements_ids = $filtre['elements_ids'];

            $alias_champ_element_dynamique = $champ_element_id_dynamique->champ->alias_champ_requete();

            $requete = $requete->where(function($sous_requete) use ($alias_champ,$elements_ids,$alias_champ_element_dynamique){
                foreach ($elements_ids as $element){
                    $sous_requete->orWhere(function($ss_requete) use ($alias_champ,$element,$alias_champ_element_dynamique){
                        $ss_requete->where($alias_champ,$element['type_element'])
                            ->where($alias_champ_element_dynamique,$element['id']);
                    });
                }
            });
        }
        else if (!empty($filtre['types_elements']))
            $requete = $requete->whereIn($alias_champ, $filtre['types_elements']);

        return $requete;

	}

    public function champ_element_id_dynamique(){

        $champ_element_id_dynamique = Champ_libre::where('type_element',$this->modele->type_element)
            ->where('contenu',$this->modele->nom_sql)
            ->where(function($sous_requete){
                $sous_requete->where('inactif',0)->orWhereNull('inactif');
            })->first();

        if($champ_element_id_dynamique == null)
            return null;

        return champ_libre($this->modele->type_element,$champ_element_id_dynamique->nom_sql);
    }
}