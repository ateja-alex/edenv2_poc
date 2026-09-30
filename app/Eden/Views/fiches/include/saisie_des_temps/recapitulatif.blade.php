<div class="row recapitulatif">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    @traduction('interface.saisie_des_temps.recapitulatif_titre')

					<span>
                        @traduction('interface.saisie_des_temps.total_mensuel')
                        <span v-html="nombre_heures_mois"></span>
                        @{{ $root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)}}
                    </span>
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col"><span>@traduction('interface.saisie_des_temps.semaine')</span></th>
                                <th v-for="jour in recapitulatif.jours" scope="col" class="affichage_jour" v-html="$root.traduction('interface.jours.'+jour)"></th>
                                <th scope="col" class="affichage_jour">
                                    <span>@traduction('interface.saisie_des_temps.total')</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template>
                                <tr v-for="(semaine_recap,index_semaine) in recapitulatif.semaines">
                                    <td>
                                        <span class="texte_semaine">
                                            @{{ $root.traduction('interface.saisie_des_temps.semaine_du_au',null,[semaine_recap.numero,semaine_recap.debut,semaine_recap.fin]) }}
                                        </span>
                                    </td>

                                    <td v-for="jour in recapitulatif.jours"
                                        :class="semaine_recap.jours[jour].classe">
                                        <template v-if="semaine_recap.jours[jour]">
                                            <template v-if="semaine_recap.jours[jour].dans_mois">
                                                <span v-html="parseFloat(semaine_recap.jours[jour].nombre_heures)"></span>
                                                @{{ $root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)}}
                                            </template>
                                            <span class="affichage_date">@{{ semaine_recap.jours[jour].affichage }}</span>
                                        </template>
                                    </td>

                                    <td class="total">
                                        <span v-html="nombre_heures_semaine[index_semaine]"></span>
                                        @{{ $root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)}}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    recapitulatif : {},

@endpush

@push('donnees_pour_vuejs_created')
    this.$on('changement',() => {

        this.chargement_recapitulatif();

    });

    this.$on('changement_valeur',() => {

        this.chargement_recapitulatif();

    });
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_recapitulatif : function(){

        $.post({
            url : '{{route('saisie_des_temps.chargement_recapitulatif', [], false)}}',
            dataType:'json',
            data : this.feuille_de_temps
        }).done(async (donnees) => {

            this.recapitulatif = donnees;
        });
    },
@endpush

@push('donnees_pour_vuejs_computed')

    nombre_heures_semaine : function(){

        var nombre_heures_semaine = {};

        for(numero_semaine in this.recapitulatif.semaines){

            var semaine_recap = this.recapitulatif.semaines[numero_semaine];

            var nombre_heures = 0;

            for(jour of Object.values(semaine_recap.jours)){

                nombre_heures+= parseFloat(jour.nombre_heures);
            }

            nombre_heures_semaine[numero_semaine] = parseInt(nombre_heures*100)/100;
        }

        return nombre_heures_semaine;
    },

    nombre_heures_mois : function(){

        var nombre_heures_mois = 0;

        for(nombre_heures of Object.values(this.nombre_heures_semaine)){
            nombre_heures_mois += parseFloat(nombre_heures);
        }

        return nombre_heures_mois;
    },
@endpush
