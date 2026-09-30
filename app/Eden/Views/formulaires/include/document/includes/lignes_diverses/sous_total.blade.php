@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])

<div class="document_contenu_ligne_diverse document_ligne_sous_total">
    
    <div>
        <span>@traduction('document.lignes_diverses.sous_total.titre') :</span>
        <input type="text" class="js_focus" v-model="article_sur_document.nom" @if(!empty($recapitulatif)) disabled @endif
            :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
    </div>

    @if((isset($colonnes_articles['total'])))
        {{-- Total HT --}}
        <div class="document_ligne_sous_total_total">
            @traduction('document.colonnes.total.titre')
            <span class="css_prix_ligne_article_document css_input_article_document">
                @{{ article_sur_document.total | montant }}
            </span>
        </div>
    @endif

    @if((isset($colonnes_articles['total_ttc'])))
        {{-- Total TTC --}}
        <div class="document_ligne_sous_total_total">
            @traduction('document.colonnes.total_ttc.titre')
            <span class="css_prix_ligne_article_document css_input_article_document">
                @{{ article_sur_document.total_ttc | montant }}
            </span>
        </div>
    @endif
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
