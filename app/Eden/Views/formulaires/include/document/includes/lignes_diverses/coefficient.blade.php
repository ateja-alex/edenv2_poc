{{-- Coefficient --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse document_ligne_coefficient">

    <div class="css_sous_total_articles_document">   
        
        @traduction('document.lignes_diverses.coefficient.titre')
        <input type="text" :placeholder="traduction('interface.document.coefficient.designation')"
            v-model="article_sur_document.nom" @if(!empty($recapitulatif)) disabled @endif
            class="js_focus document_ligne_coefficient_designation" :class="{
                @foreach($style_ligne_document as $style) 
                    style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, 
                    @endforeach
            }">
    </div>
    <div class="css_sous_total_articles_document">

        @traduction('document.lignes_diverses.coefficient.pourcentage')
        <input type="number" :placeholder="traduction('interface.document.coefficient.quantite')"
            v-model="article_sur_document.quantite" @change="modification_coeff_document(article_sur_document)" @if(!empty($recapitulatif)) disabled @endif
            class="document_ligne_coefficient_quantite" :class="{
                @foreach($style_ligne_document as $style) 
                    style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, 
                @endforeach
            }">
    </div>

    <div class="css_sous_total_articles_document css_prix_ligne_article_document">

        @traduction('document.lignes_diverses.coefficient.total') :
        <span class="css_prix_ligne_article_document" v-html="total_coefficient_devis(article_sur_document)"></span>
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

