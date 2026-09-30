{{-- Ligne remise --}}
{{-- Select & Move --}}
@include('eden::formulaires.include.document.includes.debut_ligne',['ligne_divers' => true, 'recapitulatif' => $recapitulatif])
<div class="document_contenu_ligne_diverse document_ligne_remise">

    <div>
        @traduction('document.lignes_diverses.remise.titre')
        @if($articles_modifiables === true && empty($recapitulatif))
            <input type="text" class="css_input_article_document " v-model="article_sur_document.nom" :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
        @else
            <br>
            @{{ article_sur_document.nom }}
        @endif
    </div>

    <div>
        <span>
            {!! maquette('devise_application_symbole') !!}
        </span>
        @if($articles_modifiables === true && empty($recapitulatif))
            <input type="text" 
                class="css_input_article_document"
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}"
                v-if="article_sur_document.type_remise != 1 || article_sur_document.remise == ''" 
                :disabled="article_sur_document.type_remise == 2 && article_sur_document.remise != ''" 
                @change="article_sur_document.type_remise = 1;article_sur_document.remise = $event.target.value; mise_a_jour_total_document_vue()">
            <input type="text" v-else v-model="article_sur_document.remise" 
                class="css_input_article_document" 
                @change="mise_a_jour_total_document_vue()" 
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
        @else
            <div v-if="article_sur_document.type_remise == 1">
                @{{ article_sur_document.remise }}
            </div>
        @endif
    </div>

    <div>
        <span>%</span>
        @if($articles_modifiables === true && empty($recapitulatif))
            <input type="text" 
                class="css_input_article_document"
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}"
                v-if="article_sur_document.type_remise != 2 || article_sur_document.remise == ''" 
                :disabled="article_sur_document.type_remise == 1 && article_sur_document.remise != ''" 
                @change="article_sur_document.type_remise = 2;article_sur_document.remise = $event.target.value; mise_a_jour_total_document_vue()">
            <input type="text" 
                v-else
                v-model="article_sur_document.remise"
                class="css_input_article_document"
                @change="mise_a_jour_total_document_vue()" 
                :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
        @else
            <div v-if="article_sur_document.type_remise == 2">
                @{{ article_sur_document.remise }}
            </div>
        @endif
    </div>

    <div>
        @traduction('document.lignes_diverses.remise.remise_ht')
        <span class="css_prix_ligne_article_document" v-show="article_sur_document.total > 0">
            @{{ article_sur_document.total | montant }}
        </span>
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