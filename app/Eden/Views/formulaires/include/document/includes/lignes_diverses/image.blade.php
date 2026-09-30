{{-- Image --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse" :colspan="taille_colonne">
    <label for="" class="css_label_input_article_document w-100">
        <span class="css_black">@traduction('document.lignes_diverses.image.titre')</span>
    </label>
    <label for="" class="css_label_input_article_document">
        <div class="document_image_conteneur_choix_taille">
            <div class="document_image_conteneur_input_choix_taille">
                <input type="range" v-model="article_sur_document.format" min="1" step="1" max="440" @if(!empty($recapitulatif)) disabled @endif
                    :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
            </div>
            @if (empty($recapitulatif))
                
                <div class="document_image_boutons_choix_taille">
                    <span :class="'btn ' + (article_sur_document.format == 110 ? 'btn-primary' : 'btn-secondary')" @click="article_sur_document.format = 110" v-html="$root.traduction('document.lignes_diverses.image.petit')"></span>
                    <span :class="'btn ' + (article_sur_document.format == 220 ? 'btn-primary' : 'btn-secondary')" @click="article_sur_document.format = 220" v-html="$root.traduction('document.lignes_diverses.image.moyen')"></span>
                    <span :class="'btn ' + (article_sur_document.format == 330 ? 'btn-primary' : 'btn-secondary')" @click="article_sur_document.format = 330" v-html="$root.traduction('document.lignes_diverses.image.grand')"></span>
                </div>
            @endif
        </div>

        <champ-file name="image_document" ref="image_document" :valeur="article_sur_document.nom" v-model="article_sur_document.nom" 
            :hauteur_apercu="article_sur_document.format" style="max-width:1000px;overflow-x:auto;" @if(!empty($recapitulatif)) disabled @endif
            :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
        </champ-file>
    </label>
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