@php

    if(!isset($noColonnes))
        $noColonnes = 0;
    
@endphp

@if(!empty($calcule_total_document['total_option']))

<table class="recap_option" width="{{ round($bloc->col / 12 * 100) }}%" class="{{ $type }}_bloc_{{ $i }}_{{ $j }}" valign="top">
    <tr>
        <td class="titre"> {!! traduction('liste.option.colonne.nom') !!} </td>
        <td class="titre"> {!! traduction('liste.code_ht.colonne.ht.nom') !!} </td>
        <td class="titre"> {!! traduction('liste.code_ttc.colonne.ttc.nom') !!} </td>
    </tr>
    
    @foreach ($calcule_total_document['total_option'] as $option)
        <tr>
            <td class="nom_option">
                {{ $option['nom'] }}
            </td>
            <td class="montant_ht">
                {{ $option['ht'] }} {!! maquette('devise_application_symbole') !!}
            </td>
            <td class="montant_ttc">
                {{ $option['ttc'] }} {!! maquette('devise_application_symbole') !!}
            </td>
        </tr>
    @endforeach
</table>

@endif



