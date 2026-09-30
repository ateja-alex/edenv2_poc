<div class="row">
    <div class="col-sm-2">
        {!! management('synchro_mail')->champ('email_synchro')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        {!! management('synchro_mail')->champ('email_synchro')->attr('@change','recuperation_dossiers_boite_mail_microsoft()')->cree() !!}
    </div>
    <template v-if="synchro_mail.email_synchro != ''">
        <div class="col-sm-2">
            {!! management('synchro_mail')->champ('dossier_synchroniser')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select name="dossier_synchroniser" v-model="synchro_mail.dossier_synchroniser">
                <option v-for="(nom,index) in dossiers_boite_mail_synchro" :value="index" v-html="nom"></option>
            </select>
        </div>
    </template>
</div>

@push('donnees_pour_vuejs_data')
    dossiers_boite_mail_synchro : {},
@endpush

@push('donnees_pour_vuejs_methods')

    recuperation_dossiers_boite_mail_microsoft : async function(){

        var dossiers = [];

        if(this.email_synchro != ''){

            await $.ajax({

                url : '{{URL::to('/eden/microsoft/dossiers_boite_mail/')}}/'+this.synchro_mail.email_synchro,
                dataType : 'json',
            }).done(function(donnees){

                dossiers = donnees;
            });
        }

        this.dossiers_boite_mail_synchro = dossiers;
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.recuperation_dossiers_boite_mail_microsoft();
@endpush