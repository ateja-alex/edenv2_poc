<!-- BREVO -->
<div class="row" v-if="synchronisation_service.service == 1">
    <div class="col-sm-2">
        @traduction('interface.formulaires.synchronisation_service.brevo.cle_api')
    </div>
    <div class="col-sm-4">
        <champ-texte :modele="parametres" nom_sql="cle_api" :modele_champ_force="{
            format_champ: 'password'
        }"></champ-texte>
    </div>
</div>
<!-- M3 -->
<template v-if="synchronisation_service.service == 2">
    <div class="row">
        <div class="col-sm-2">
            @traduction('interface.formulaires.synchronisation_service.esalink.client_id')
        </div>
        <div class="col-sm-4">
            <champ-texte :modele="parametres" nom_sql="client_id" :modele_champ_force="{
                format_champ: 'password'
            }"></champ-texte>
        </div>
        <div class="col-sm-2">
            @traduction('interface.formulaires.synchronisation_service.esalink.client_secret')
        </div>
        <div class="col-sm-4">
            <champ-texte :modele="parametres" nom_sql="client_secret" :modele_champ_force="{
                format_champ: 'password'
            }"></champ-texte>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            @traduction('interface.formulaires.synchronisation_service.esalink.cle_api')
        </div>
        <div class="col-sm-4">
            <champ-texte :modele="parametres" nom_sql="cle_api" :modele_champ_force="{
                format_champ: 'password'
            }"></champ-texte>
        </div>
    </div>
</template>


<div class="row" v-if="synchronisation_service.id > 0">
    <div class="col-sm-2">
        @traduction('interface.formulaires.synchronisation_service.url_cron')
    </div>
    <div class="col-sm-4">
        <input type="text" disabled :value="url_cron">
        @traduction('interface.formulaires.synchronisation_service.attention_cron')
    </div>
</div>

<input type="hidden" v-if="parametres_saisis" name="parametres" :value="JSON.stringify(parametres)">
<input type="hidden" v-else name="parametres" :value="null">

@push('donnees_pour_vuejs_data')
    parametres : {},
@endpush

@push('donnees_pour_vuejs_computed')
    url_cron(){
        if(this.synchronisation_service.id > 0)
            return window.location.origin + '/eden/cron/synchronisation_service_groupe/' + this.synchronisation_service.id;

        return null;
    },

    parametres_saisis(){
        return Object.values(this.parametres).filter(valeur => valeur !== null && valeur !== '').length > 0;
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.synchronisation_service.id > 0){

        $.post({
            url: "{{ route('synchronisation_service.parametres', '__ID__', false) }}".replace('__ID__', this.synchronisation_service.id),
            dataType: 'json',
        }).done((parametres) => {
            this.parametres = Object.keys(parametres).length > 0 ? parametres : {};
        });
    }
@endpush