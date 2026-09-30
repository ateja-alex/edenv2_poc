@if(fonctionnalite('ecart_gestion_ttc')['note_de_frais'])
    <div class="row">
        <div :class="'col-sm-'+($parent.nom_formulaire == 'note_de_frais_formulaire' ? 4 : 2)">
            {!! management('note_de_frais')->champ('ecart_gestion_ttc')->nom_vue() !!}
        </div>
        <div :class="'col-sm-'+($parent.nom_formulaire == 'note_de_frais_formulaire' ? 8 : 4)">
            {!! management('note_de_frais')->champ('ecart_gestion_ttc')->attr('lecture_seule','note_de_frais.comptabilisee == 1 ? true : $parent.formulaire_lecture_seule',1)->cree() !!}
        </div>
    </div>

    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql == 'ecart_gestion_ttc'){

                var seuil_ecart_gestion_ttc = {!! fonctionnalite('seuil_ecart_gestion_ttc') !!};

                if(this.note_de_frais.ecart_gestion_ttc > seuil_ecart_gestion_ttc){
                    alerte_eden(this.$root.traduction('messages.js.ecart_gestion_ttc.trop_eleve',null,[this.$options.filters.montant(seuil_ecart_gestion_ttc)]));
                    this.note_de_frais.ecart_gestion_ttc = seuil_ecart_gestion_ttc;
                }
                
                this.mise_a_jour_montant();
            }
        });
    @endpush

    @include('eden::formulaires.note_de_frais.include.maj_montant')
@endif