@php

    $langue = maquette('langue_par_defaut_code');
    if(!isset($noColonnes))
        $noColonnes = 0 ;
@endphp


@if(isset($afficher_stot_ht_av_remise) && $afficher_stot_ht_av_remise && $document->remise_globale > 0)
    <tr class="totaux totaux_av_remise_ht">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux" >{!! isset($label_stot_ht_av_remise) ? $label_stot_ht_av_remise : traduction('pdf.document_gescom.total_ht_avant_remise', $langue) !!}</th>

        @php

            $montant_ht_avant_remise = 0;

            if($document->remise_globale_type == 1){

                $montant_ht_avant_remise = $document->montant_document_ht * (100 / (100 - $document->remise_globale));

            }

            else{

                $montant_ht_avant_remise = ($document->montant_document_ttc + $document->remise_globale) * (100 / (100 + 20));

            }

        @endphp

        <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ht')->affiche($montant_ht_avant_remise) }} {!! maquette('devise_application_symbole') !!} </td>
    </tr>
@endif

@if(isset($afficher_stot_ttc_av_remise) && $afficher_stot_ttc_av_remise && $document->remise_globale > 0)
    <tr class="totaux totaux_av_remise_ttc">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux" >{!! isset($label_stot_ttc_av_remise) ? $label_stot_ttc_av_remise : traduction('pdf.document_gescom.total_ttc_avant_remise', $langue) !!}</th>

        <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ttc')->affiche($calcule_total_document['avant_remise']['ttc']) }} {!! maquette('devise_application_symbole') !!} </td>
    </tr>
@endif

@if(isset($afficher_remise_ht) && $afficher_remise_ht && $document->remise_globale > 0)
    <tr class="totaux totaux_remise_ht">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux" >{!! isset($label_remise_ht) ? $label_remise_ht : traduction('pdf.document_gescom.total_remise_ht', $langue) !!}</th>

        <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('remise_globale')->affiche($document->remise_globale) }}{!! ($document->remise_globale_type == 1)  ? '%' : maquette('devise_application_symbole') !!} </td>
    </tr>
@endif

@if(isset($afficher_stot_ht) && $afficher_stot_ht)
    <tr class="totaux totaux_ht">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux">{!!   isset($label_stot_ht) ? $label_stot_ht : traduction('pdf.document_gescom.total_ht', $langue) !!}</th>
        <td class="montant_totaux" style="text-align: right;">
            {{ management($type_element)->champ('montant_document_ht')->affiche($document->montant_document_ht) }} {!! maquette('devise_application_symbole') !!}
            @if(isset($afficher_stot_eco_contribution) && $afficher_stot_eco_contribution && ($calcule_total_document['eco_contribution_inclus'] > 0))
                <div class="montant_eco_contribution_inclus">
                    {!! traduction('document.eco_contribution_inclus',null,[management($type_element)->champ('montant_document_ht')->affiche($calcule_total_document['eco_contribution_inclus'])]) !!} {!! maquette('devise_application_symbole') !!}
                </div>
            @endif
        </td>
    </tr>
@endif

@if(isset($afficher_stot_eco_contribution) && $afficher_stot_eco_contribution && $calcule_total_document['eco_contribution'] > 0)
    <tr class="totaux totaux_eco_contribution">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux">{!!   isset($label_stot_eco_contribution) ? $label_stot_eco_contribution : traduction('pdf.document_gescom.total_eco_contribution', $langue) !!}</th>
        <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ht')->affiche($calcule_total_document['eco_contribution']) }} {!! maquette('devise_application_symbole') !!}</td>
    </tr>
@endif

@if(isset($afficher_stot_tva) && $afficher_stot_tva)
    <tr class="totaux totaux_tva">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux">{!! isset($label_stot_tva) ? $label_stot_tva : traduction('pdf.document_gescom.total_tva', $langue) !!} <span></span></th>
        <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_tva')->affiche($calcule_total_document['tva']) }} {!! maquette('devise_application_symbole') !!}</td>
    </tr>
@endif

@if(isset($afficher_total_ttc) && $afficher_total_ttc)
    <tr class="totaux totaux_ttc">
        @if($noColonnes > 2)
            <td colspan="{{ $noColonnes - 2 }}"></td>
        @endif

        <th class="titre_totaux" >{!! isset($label_tot_ttc) ? $label_tot_ttc : traduction('pdf.document_gescom.total_ttc', $langue) !!}</th>
        <td class="montant_totaux" style="text-align: right;">
            {{ management($type_element)->champ('montant_document_ttc')->affiche($document->montant_document_ttc) }} {!! maquette('devise_application_symbole') !!}
            @if(isset($afficher_stot_eco_contribution) && $afficher_stot_eco_contribution && ($calcule_total_document['eco_contribution_inclus_ttc'] > 0 || $calcule_total_document['eco_contribution_ttc'] > 0))
                <div class="montant_eco_contribution_inclus">
                    {!! traduction('document.eco_contribution_inclus',null,[management($type_element)->champ('montant_document_ttc')->affiche($calcule_total_document['eco_contribution_inclus_ttc']+$calcule_total_document['eco_contribution_ttc'])]) !!} {!! maquette('devise_application_symbole') !!}
                </div>
            @endif
        </td>
    </tr>
@endif

<!-- les acomptes -->
@if(!empty($acomptes) || (isset($afficher_solde_ttc) && $afficher_solde_ttc))

    @hasSection('net_a_payer_acompte')
        @yield('net_a_payer_acompte')
    @else
        <?php $net_a_payer = $document->montant_document_ttc; ?>

        @foreach($acomptes as $acompte)
            <tr class="totaux totaux_acompte">
                @if($noColonnes > 2)
                    <td colspan="{{ $noColonnes - 4 }}"></td>
                @endif

                <th class="titre_totaux"colspan="3" >{!! traduction('pdf.modele.facture_acompte', $langue) !!} {{ $acompte['management']->modele->reference_document }} {!! traduction('pdf.modele.du', $langue) !!} {{ formate_date('d/m/Y', $acompte['management']->modele->date) }}</th>
                <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ttc')->affiche($acompte['management']->modele->montant_document_ttc) }} {!! maquette('devise_application_symbole') !!}</td>
            </tr>
                <?php $net_a_payer -= $acompte['management']->modele->montant_document_ttc; ?>
        @endforeach

        @foreach($paiements as $paiement)
            <tr class="totaux totaux_acompte">
                @if($noColonnes > 2)
                    <td colspan="{{ $noColonnes - 4 }}"></td>
                @endif

                <th class="titre_totaux"colspan="3" >{!! traduction('pdf.modele.facture_paiement', $langue) !!} {{ $paiement->titre }} {!! traduction('pdf.modele.du', $langue) !!} {{ formate_date('d/m/Y', $paiement->date) }}</th>
                <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ttc')->affiche($paiement->montant) }} {!! maquette('devise_application_symbole') !!}</td>
            </tr>
                <?php $net_a_payer -= $paiement->montant; ?>
        @endforeach

        <tr class="totaux totaux_net_a_payer">
            @if($noColonnes > 2)
                <td colspan="{{ $noColonnes - 2 }}"></td>
            @endif

            <th class="titre_totaux">{!! traduction('pdf.modele.net_a_payer', $langue) !!}</th>
            <td class="montant_totaux" style="text-align: right;">{{ management($type_element)->champ('montant_document_ttc')->affiche($net_a_payer) }} {!! maquette('devise_application_symbole') !!}</td>
        </tr>
    @endif

@endif