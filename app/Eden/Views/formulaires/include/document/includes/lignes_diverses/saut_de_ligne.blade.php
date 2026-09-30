{{-- Saut de ligne --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse" :colspan="taille_colonne">
    <div class="d-flex align-items-center justify-content-center">
        <div>- - - - - - -</div>
        <div class="font-weight-bold ml-3 mr-3">@traduction('document.lignes_diverses.saut_de_ligne.titre')</div>
        <div>- - - - - - -</div>
    </div>
</div>
{{-- Afficher & Supprimer --}}
<div class="document_ligne_options">
    @if(empty($recapitulatif))
        <div class="css_actions_article_document">
            @foreach($colonne_options_lignes_diverses as $colonne)
                @include('eden::formulaires.include.document.includes.options.'.$colonne)
            @endforeach
            @include('eden::formulaires.include.document_style_ligne_document')
        </div>
    @endif
</div>