{{-- Titre --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])

<div class="document_contenu_ligne_diverse document_ligne_titre" :colspan="taille_colonne">
    <div class="css_saisie_articles_sur_document_input_group">
        <span class="css_saisie_articles_sur_document_input_texte_gauche">@traduction('document.lignes_diverses.titre.titre') :</span>
        <input type="text" class="css_input_article_document css_saisie_articles_sur_document_input_input js_focus" v-model="article_sur_document.nom" 
            :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}" 
            @if(!empty($recapitulatif)) disabled @endif>
    </div>
</div>
{{-- Afficher & Supprimer --}}
<div class="document_ligne_options" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['right'])">
    @if(empty($recapitulatif))
        <div class="css_actions_article_document">
            @foreach($colonne_options_lignes_diverses as $colonne)
                @include('eden::formulaires.include.document.includes.options.'.$colonne)
            @endforeach
            @include('eden::formulaires.include.document_style_ligne_document')
        </div>
    @endif
</div>