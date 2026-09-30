{{-- TVA --}}
<div class="cellule_document_colonne_article">

        <select v-if="{{ $edition_ligne }}" class="css_prix_ligne_article_document css_input_article_document" v-model="article_sur_document.tva" @change="mise_a_jour_total_document_vue();" style="margin-bottom:8px;" @if(!empty($fonctionnalite_colonnes['categorie_comptable_article_id']) || fonctionnalite('modification_tva_impossible_sur_documents') === true) disabled @elseif(!empty(moi_extranet())) :disabled="disable_champs_extranet" @endif>

            @if(!empty($taux_de_tva))
                @foreach($taux_de_tva as $taux)
                    <option value="{{$taux}}">{{floatval($taux)}}%</option>
                @endforeach
            @else
                <option value="0">0%</option>
                @if(config('maquette.taux_tva_bali'))
                <option value="3">3%</option>
                @endif
                @if(config('maquette.taux_tva_francais'))
                <option value="5.5">5,5%</option>
                <option value="8.5">8,5%</option>
                <option value="10">10%</option>
                <option value="20">20%</option>
                @endif
                @if(config('maquette.taux_tva_espagne'))
                <option value="21">21%</option>
                @endif
                @if(config('maquette.taux_tva_suisse'))
                <option value="7.7">7,7%</option>
                @endif
            @endif
        </select>
        <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{new Intl.NumberFormat('fr-FR', { style: 'decimal', minimumFractionDigits : 0 }).format(article_sur_document.tva)}} %</span>
</div>
