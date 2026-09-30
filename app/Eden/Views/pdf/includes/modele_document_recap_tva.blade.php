@php

    if(!isset($noColonnes))
        $noColonnes = 0;
    
@endphp

<table class="recap_tva" width="{{ round($bloc->col / 12 * 100) }}%" class="{{ $type }}_bloc_{{ $i }}_{{ $j }}" valign="top">
    <tr>
        <td class="titre">
            @if(isset($label_taux))
                {!! $label_taux !!}
            @else
                {!! traduction('liste.code_tva.colonne.taux.nom') !!}
            @endif
        </td>
        <td class="titre">
            @if(isset($label_ht))
                {!! $label_ht !!}
            @else
                {!! traduction('liste.code_ht.colonne.ht.nom') !!}
            @endif
        </td>
        <td class="titre">
            @if(isset($label_tva))
                {!! $label_tva !!}
            @else
                {!! traduction('liste.code_tva.colonne.tva.nom') !!}
            @endif
        </td>
    </tr>
    
    @foreach ($tableau_tva_par_taux as $taux => $ligne)
        <tr>
            <td class="valeur_taux">
                {{ floatval($taux) }} %
            </td>
            <td class="montant_ht">
                {{ management($type_element)->champ('montant_document_ht')->affiche($ligne['ht']) }} {!! maquette('devise_application_symbole') !!}
            </td>
            <td class="montant_tva">
                {{ management($type_element)->champ('montant_document_tva')->affiche($ligne['tva']) }} {!! maquette('devise_application_symbole') !!}
            </td>
        </tr>
    @endforeach
</table>



