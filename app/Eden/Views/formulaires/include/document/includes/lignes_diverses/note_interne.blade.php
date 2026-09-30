{{-- Commentaire --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse css_padding_20_article_document" :colspan="taille_colonne">
<label for="" class="css_label_input_article_document w-100">
    <span class="css_black">@traduction('document.colonnes.note_interne.titre')</span>

        @if(fonctionnalite('notes_internes_wysiwyg_documents') === true)
            <div class="col-md-10">

                <span v-if="article_sur_document.note_interne_wysiwyg == undefined || article_sur_document.note_interne_wysiwyg==''" @click="note_interne_article_wysiwyg(article_sur_document, article_index)">@traduction('document.colonnes.note_interne.inserer')</span>
                <span style="text-align: left;" v-html="article_sur_document.note_interne_wysiwyg"  @click="note_interne_article_wysiwyg(article_sur_document, article_index)"></span>
                <textarea style="display:none;" class="js_focus" v-model="article_sur_document.note_interne_wysiwyg"></textarea>
            </div>
        @else

            <textarea name="" id="" cols="30" rows="4" v-model="article_sur_document.contenu" class="js_focus" @if(!empty($recapitulatif)) disabled @endif
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}"></textarea>
        @endif
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