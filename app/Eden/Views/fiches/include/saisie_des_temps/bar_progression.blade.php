<div class="row barre_progression mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <progress class="progress" :value="total_saisi" :max="temps_a_saisir"></progress>
                    </div>
                    <div class="col-sm-12 text-center">
                        <h5>
                            <span :style="total_saisi < temps_a_saisir ? 'color: red;' : ''">@{{ total_saisi }}</span>
                            <span>/</span>
                            <span>@{{temps_a_saisir}}</span>
                            <span v-html="$root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)"></span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    temps_a_saisir_defaut : {{ fiche('saisie_des_temps')->structure_fiche()['options']['nombre_temps_par_defaut'] ?? 7 }},
@endpush

@push('donnees_pour_vuejs_computed')

    temps_a_saisir : function(){

        if(!this.informations_dates.dates_pour_saisie)
            return 0;

        var dates = this.informations_dates.dates_pour_saisie.filter(date => date.jour_indisponibilite === false && ![0,6].includes(new Date(date.date).getDay()));

        @if(fiche('saisie_des_temps')->structure_fiche()['options']['unite'] == 'jour')
            var temps_a_saisir = dates.length;
        @else
            var temps_a_saisir = this.temps_a_saisir_defaut * dates.length;
        @endif
    
        if(this.utilisateur_actuel && this.utilisateur_actuel.type_contrat == 1)
            temps_a_saisir = ((this.utilisateur_actuel.quantite_contrat > 0 ? this.utilisateur_actuel.quantite_contrat : 35) / 5 * dates.length).toFixed(2);
        else if (this.utilisateur_actuel && this.utilisateur_actuel.type_contrat == 2)
            temps_a_saisir = dates.length;

        return temps_a_saisir;
    },

    total_saisi : function(){

        var total_saisi = 0;

        for(element of this.elements){

            var tableau = element.lignes ? element.lignes : element.feuilles_de_temps;

            if(tableau){

                if(this.feuille_de_temps.type_saisie == 'element'){
                    for(date of Object.values(tableau.durees)){
                        if(date[this.champ_de_duree] > 0)
                            total_saisi += parseFloat(date[this.champ_de_duree]);
                    }
                }
                else{

                    for(ligne of Object.values(tableau)){
                        for(date of Object.values(ligne.durees)){
                            if(date[this.champ_de_duree] > 0)
                                total_saisi += parseFloat(date[this.champ_de_duree]);
                        }
                    }

                }

            }
        }

        return Number.parseFloat(total_saisi.toFixed(2));
    },
@endpush