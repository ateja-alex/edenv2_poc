<script>
const recherche_par_siret = Vue.component('recherche-par-siret', {
    template:
        `<div class="row recherche_siret_conteneur" v-if="element.id == null">
            <div class="col-md-12" >
                <input type="text" class="recherche_siret_input" placeholder="Rechercher une entreprise" v-model="champ_recherche_entreprise_via_siret" @keyup="recherche_entreprise_via_siret">
            </div>
            <template v-if="entreprises_recherches.length > 0 && champ_recherche_entreprise_via_siret.length > 0">
                <div class="col-md-12" >
                    <div class="recherche_siret_header_resultats">
                        <div class="col-md-2 recherche_siret_header_resultats_titre">
                            @traduction('formulaire.recherche_siret.siret')
                        </div>
                        <div class="col-md-5 recherche_siret_header_resultats_titre">
                            @traduction('formulaire.recherche_siret.nom')
                        </div>
                        <div class="col-md-5 recherche_siret_header_resultats_titre">
                            @traduction('formulaire.recherche_siret.adresse')
                        </div>
                    </div>
                    <div class="recherche_siret_resultat" v-for="entreprise_recherche in entreprises_recherches" @click="selectionne_entreprise_via_siret(entreprise_recherche)">
                        <div class="col-md-2" v-text="entreprise_recherche.siret"></div>
                        <div class="col-md-5" v-if="entreprise_recherche.nom != '' && entreprise_recherche.nom != 'NULL'"
                            v-text="entreprise_recherche.nom"></div>
                        <div class="col-md-5" v-else v-text="entreprise_recherche.enseigne1Etablissement"></div>
                        <div class="col-md-5" v-text="entreprise_recherche.adresse"></div>
                    </div>
                    <br/><br/>
                </div>
            </template>
        </div>`,
    props:{
        element: {
            type:Object,
            default: {}
        },
        type_element: {
            type: String,
            default: ''
        },
    },

    data : function(){
        return {
            champ_recherche_entreprise_via_siret : '',
            entreprises_recherches : [],
            champs_mappage_insee : [],
            champs_libres_element : [],
        }
    },
    methods: {

        recherche_entreprise_via_siret: async function(){

            var recherche = this.champ_recherche_entreprise_via_siret;

            await $.ajax({

                url: "eden/api_interne/recuperer_siret",
                dataType: "json",
                method: 'post',
                data: {
                    recherche:recherche
                },
            }).done((data) => {

                this.entreprises_recherches = [];

                var results = $.map(data, (dataItem) => {

                    var etablissement = dataItem;

                    this.entreprises_recherches.push(etablissement);
                })
            });
        },
	
        selectionne_entreprise_via_siret: function(entreprise_recherche){

            if(!entreprise_recherche.numeroVoieEtablissement)
                entreprise_recherche.numeroVoieEtablissement = '';

            if(entreprise_recherche.libelleVoieEtablissement == "NULL VILLE")
                entreprise_recherche.libelleVoieEtablissement = '';
            
            var valeur = '';
            var date = null;

            this.champs_mappage_insee.forEach((champ) => {

                valeur = '';
                champ_libre_element = this.champs_libres_element[champ.champ_eden];

                champ.champ_insee.split('+').forEach((champ_insee, index) => {

                    if(index > 0)
                        valeur += ' ';

                    valeur += entreprise_recherche[champ_insee.trim()].trim();
                });

                if(champ_libre_element && champ_libre_element.type == 2)
                    valeur = parseInt(valeur);
                else if(champ_libre_element && champ_libre_element.type == 3)
                    valeur = parseFloat(valeur);
                else if(champ_libre_element && [4,5].includes(champ_libre_element.type)){

                    try{

                        date = new Date(Date.parse(valeur));

                        mois = date.getMonth() + 1;

                        mois = (mois < 10 ? '0' : '') + mois;
                        jour = (date.getDate() < 10 ? '0' : '') + date.getDate();

                        valeur = date.getFullYear() +'-'+mois+'-'+jour;

                        if(champ_libre_element.type == 5){

                            heure = (date.getHours() < 10 ? '0' : '') + date.getHours();
                            minutes = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
                            secondes = (date.getSeconds() < 10 ? '0' : '') + date.getSeconds();

                            valeur += ' '+heure+":"+minutes+":"+secondes;
                        }

                    }
                    catch(error){
                        valeur = null;
                    }
                }

                try{

                    const champs = champ.champ_eden.split('.');
                    const dernier_champ = champs.pop();

                    this.$set(
                        champs.length > 0 ? champs.reduce((acc, nom_champ) => acc?.[nom_champ], this.element) : this.element, 
                        dernier_champ, 
                        valeur
                    );
                }
                catch(e){
                    console.error('Champ ' + champ.champ_eden + ' dans ' + this.type_element + ' inexistant');
                }
            });

            this.$set(this, 'champ_recherche_entreprise_via_siret', '');
        },

        recuperation_champs_libres_element : async function() {

            await $.ajax({
                url: 'eden/champs/valeurs/'+this.type_element,
                dataType:'json'
            }).done((champs_libres) => {
                var champs_libres_reindexes = Object.fromEntries(
                    Object.values(champs_libres).map(champ => [champ.nom_sql, champ])
                );
                this.$set(this,'champs_libres_element',champs_libres_reindexes);
            });
        },

        recuperation_champs_mappage_insee : async function() {

            await $.post({
                url : 'eden/elements/mappage_insee',
                dataType:'json',
            }).done((elements) => {
                this.$set(this,'champs_mappage_insee',elements);
            });
        },
    },
    mounted : async function(){
    
        await this.recuperation_champs_libres_element();
        await this.recuperation_champs_mappage_insee();
    }
});
</script>
