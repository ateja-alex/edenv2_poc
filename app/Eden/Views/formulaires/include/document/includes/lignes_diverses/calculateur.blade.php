{{-- Calculateur --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse" :colspan="taille_colonne">
    <div class="d-flex" style="align-items: center;justify-content: center;">
        <span style="width:20px;font-size:15px;" class="fas fa-calculator"></span>
        <label for="" class="css_label_input_article_document w-100" style="margin-bottom:8px;">
            <span class="css_black">@traduction('document.lignes_diverses.calculateur.titre') :</span>

            <input type="text" class="css_input_article_document js_focus" v-model="article_sur_document.nom" @if(!empty($recapitulatif)) disabled @endif
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">

        </label>
    </div>
</div>
{{-- Afficher & Supprimer --}}
<div class="document_ligne_options" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['right'])">
    @if(empty($recapitulatif))
        <div class="css_actions_article_document">
            @foreach($colonne_options_lignes_diverses as $colonne)
                @include('eden::formulaires.include.document.includes.options.'.$colonne)
            @endforeach
            @if(fonctionnalite('calculateur_sur_document'))
                <span v-if="article_sur_document.calculateur && article_sur_document.calculateur.erreur == false" :title="traduction('document.colonnes.designation.afficher_calculateur')" class="css_btn_action_article_document" @click="afficher_calculateur(article_sur_document);" style="background-color: yellowgreen; position: relative;"><i class="fas fa-calculator"></i></span>
                <span v-else-if="article_sur_document.calculateur && article_sur_document.calculateur.erreur == true" :title="traduction('document.colonnes.designation.afficher_calculateur')" class="css_btn_action_article_document" @click="afficher_calculateur(article_sur_document);" style="background-color:red; position: relative;"><i class="fas fa-calculator"></i></span>
                <span v-else class="css_btn_action_article_document" @click="afficher_calculateur(article_sur_document);" :title="traduction('document.colonnes.designation.afficher_calculateur')" style="position: relative;"><i class="fas fa-calculator"></i></span>
            @endif
            @include('eden::formulaires.include.document_style_ligne_document')
        </div>
    @endif
</div>