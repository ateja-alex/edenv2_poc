<div class="row">
    <div class="col-sm-2">
        @traduction('formulaire.echeance.creation_multiple.titre')
    </div>
    <div class="col-sm-4">
        <select v-model="creation_multiple">
            <option value="0" v-html="$root.traduction('valeurs_listes_formatees.14.valeur_0')"></option>
            <option value="1" v-html="$root.traduction('valeurs_listes_formatees.14.valeur_1')"></option>
        </select>
    </div>
</div>
<template v-if="creation_multiple == 1">
    <div class="row">
        <div class="col-sm-2">
            @traduction('formulaire.echeance.creation_multiple.periodicite')
        </div>
        <div class="col-sm-4">
            <select v-model="periodicite">
                <option value="1" v-html="$root.traduction('formulaire.echeance.creation_multiple.mensuel')"></option>
                <option value="3" v-html="$root.traduction('formulaire.echeance.creation_multiple.trimestriel')"></option>
                <option value="12" v-html="$root.traduction('formulaire.echeance.creation_multiple.annuel')"></option>
            </select>
        </div>
        <div class="col-sm-2">
            @traduction('formulaire.echeance.creation_multiple.nombre_iterations')
        </div>
        <div class="col-sm-4">
            <input v-model="nombre_iterations" type="number" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <td>@traduction('formulaire.echeance.creation_multiple.date_echeances')</td>
                        <td>@traduction('formulaire.echeance.creation_multiple.montant')</td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(montant_date,index) in montant_dates_echeances">
                        <td v-show="false">
                            <input :name="'montants_dates_echeances['+index+'][date]'" :value="montant_date.date">
                            <input :name="'montants_dates_echeances['+index+'][montant]'" :value="montant_date.montant">
                        </td>
                        <td v-html="montant_date.date_affichage"></td>
                        <td v-html="montant_date.montant">@{{ montant_date.montant | montant }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')
    creation_multiple : 0,
    periodicite : 1,
    nombre_iterations : 1,
@endpush

@push('donnees_pour_vuejs_computed')

    montant_dates_echeances : function(){

        var dates_echeances = [];

        if(this.echeance.date == '' || this.echeance.date == null)
            return [];

        var prochaine_date = new Date(this.echeance.date);

        var montant = Math.round(this.$root.totaux.ttc / this.nombre_iterations * 100)/100;

        var montants = montant * (this.nombre_iterations - 1);

        var dernier_montant = Math.round((this.$root.totaux.ttc - montants) * 100)/100;

        for(var i = 1;i <= this.nombre_iterations;i++){

            var annee = prochaine_date.getFullYear();
            var mois = (prochaine_date.getMonth()+1).toString();

            if(mois.length == 1)
                mois = '0' + mois;

            var jour = prochaine_date.getDate().toString();

            if(jour.length == 1)
                jour = '0' + jour;

            dates_echeances.push({
                date_affichage : jour + '/' + mois + '/' + annee,
                date : annee + '-' + mois + '-' + jour,
                montant : i == this.nombre_iterations ? dernier_montant : montant,
            });

            let temp = new Date(prochaine_date)

            temp.setMonth(temp.getMonth() + parseInt(this.periodicite));

            if (temp.getDate() != prochaine_date.getDate())
                temp.setDate(0);

            prochaine_date = temp;
        }

        return dates_echeances;
    },
@endpush