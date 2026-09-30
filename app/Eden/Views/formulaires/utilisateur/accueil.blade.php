<div class="row">
    <div class="col-sm-2">@traduction('formulaire.utilisateur.page_accueil')</div>
    <div class="col-sm-4">
        <select v-if="!accueil_issue_autre" v-model="utilisateur.accueil" name="accueil" @change="utilisateur.accueil == 'autre' ? utilisateur.accueil = 'eden/' : ''">
            <optgroup v-for="categorie in tableau_accueil" :label="categorie.nom">
                <option v-for="valeur in categorie.valeurs" :value="valeur.valeur" >@{{ valeur.nom }}</option>
            </optgroup>
            <option value="autre" v-html="$root.traduction('formulaire.utilisateur.autre')"></option>
        </select>
        <template  v-if="accueil_issue_autre">
            <input type="text" name="accueil" v-model="utilisateur.accueil">
            <span @click="utilisateur.accueil = 'eden/accueil'" style="cursor: pointer;">
                <i class="fas fa-times"></i>
                @traduction('formulaire.utilisateur.revenir_page_accueil_predefinies')
            </span>
        </template>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    tableau_accueil : [
        {
            nom : 'Pages par défaut',
            valeurs : [
                {
                    nom : 'Accueil de base',
                    valeur : 'eden/accueil'
                },
                @if(!empty(maquette('page_accueil')))
                {
                    nom : 'Maquette',
                    valeur : '{{maquette('page_accueil')}}'
                },
                @endif
            ]
        },
    ],
@endpush

@push('donnees_pour_vuejs_mounted')

    $.post({
        url : 'eden/elements/tableau_de_bord',
        dataType:'json',
    }).done((elements) => {

        if(elements.length > 0){

            var tableaux_de_bord = elements.map((element) => {
                return {
                    nom : this.$root.traduction(element.index_traduction+'.nom'),
                    valeur : 'eden/tableau_de_bord/'+element.id
                }
            });

            this.tableau_accueil.push({
                nom : 'Tableaux de bord',
                valeurs : tableaux_de_bord
            });

        }
    });

@endpush

@push('donnees_pour_vuejs_computed')

    accueil_issue_autre : function(){

        var valeurs_possibles = [];

        for(categorie of this.tableau_accueil){
            valeurs_possibles = valeurs_possibles.concat(categorie.valeurs.map((valeur) => {return valeur.valeur}));
        }

        return this.utilisateur.accueil != null && !valeurs_possibles.includes(this.utilisateur.accueil);
    },

@endpush