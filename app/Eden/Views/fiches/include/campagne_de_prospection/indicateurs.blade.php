<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-8" style="font-weight: 900; font-size: 20px;">
                @if(fiche('saisie_des_temps')->structure_fiche()['options']['unite'] == 'heure')
                    @traduction('module_sur_fiche.projet.indicateurs.heures_realisees')
                @else
                    @traduction('module_sur_fiche.projet.indicateurs.journees_realisees')
                @endif

            </div>
            <div class="col-md-4" style="font-weight: 900; font-size: 20px; text-align: right;">
                @{{ heures_realisees }}
                @if(fiche('saisie_des_temps')->structure_fiche()['options']['unite'] == 'heure')
                    @traduction('module_sur_fiche.projet.indicateurs.unite_heure')
                @else
                    @traduction('module_sur_fiche.projet.indicateurs.unite_jour')
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
				<span class="css__lien" data-toggle="modal" data-target="#modal_details_heures">
				    @traduction('module_sur_fiche.projet.indicateurs.detail_heures')
				</span>
            </div>
        </div>
    </div>
</div>

@include('eden::fiches.include.projet_details_des_heures_modale')

@push('donnees_pour_vuejs_data')
    heures_realisees: {{ $indicateurs['heures_realisees'] }},
@endpush