<script>
    const rapport_tableau = Vue.component('rapport-tableau', {
        template: `
        <div style="display: flex;flex-direction: column;">
            <div @click="export_liste" v-if="export_excel" style="display: flex;align-items: center;gap: 5px;padding: 5px;float: right;margin-left: auto;" 
                class="css_ajouter_element css_pointer">
                <i class="fas fa-file-export"></i>
                @traduction('rapport.tableau.export')
            </div>
            <div style="max-height: 64vh;overflow: auto;">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr class="css_tableau_titre">
                            <th style="position:relative" @click="changement_tri('axe_y')">
                                <span v-html="champ_y != 'serie' ? $root.traduction('champs_libres.'+type_element+'.'+champ_y+'.nom') : ''"></span>
                                <i style="position: absolute;right: 5px;top: 9px;" :class="'fas fa-arrow-'+(tri.direction == 'asc' ? 'down' : 'up')" v-if="tri.valeur == 'axe_y'"></i>
                            </th>
                            <th style="position:relative" v-for="titre in titres[champ_x]" @click="changement_tri(titre.id)">
                                <span v-html="titre.nom"></span>
                                <i style="position: absolute;right: 5px;top: 9px;" :class="'fas fa-arrow-'+(tri.direction == 'asc' ? 'down' : 'up')" v-if="tri.valeur == titre.id"></i>
                            </th>
                            <th style="position:relative" @click="changement_tri('total')" v-if="champ_x != 'serie'">
                                <span>Total</span>
                                <i style="position: absolute;right: 5px;top: 9px;" :class="'fas fa-arrow-'+(tri.direction == 'asc' ? 'down' : 'up')" v-if="tri.valeur == 'total'"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="champ_y in resultats_tries">
                            <td v-html="champ_y.nom"></td>
                            <td v-for="ligne in champ_y.lignes" v-html="ligne"></td>
                            <td class="css_tableau_titre" v-if="champ_x != 'serie'" v-html="totaux['champ_y'][champ_y.id] ?? 0"></td>
                        </tr>
                        <tr class="css_tableau_titre" v-if="champ_y != 'serie'">
                            <td>Total</td>
                            <td v-for="(total, valeur_x) in totaux['champ_x']" v-html="total"></td>
                            <td v-if="champ_x != 'serie'" style="font-weight:bold;" v-html="totaux.total"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>`,
        props: {
            id_rapport : {
                type: String,
                default : ''
            },
            type_element : {
                type: String,
                default : ''
            },
            titres : {
                type: Object,
                default : function(){
                    return {};
                }
            },
            resultats : {
                type: Array,
                default : []
            },
            cacher_colonnes_vides : {
                type: Boolean,
                default : false
            },
            champ_x : {
                type: String,
                default : null
            },
            champ_y : {
                type: String,
                default : null
            },
            export_excel : {
                type: Boolean,
                default : false
            },
        },

        data : function(){
            return {
                tri : {
                    valeur : null,
                    direction : 'asc'
                },
            }
        },

        methods : {

            changement_tri : function(valeur){

                if(this.tri.valeur == valeur)
                    this.tri.direction = this.tri.direction == 'asc' ? 'desc' : 'asc';
                else
                    this.tri = {valeur : valeur, direction : 'asc'};
            },

            export_liste : function(){

                $.ajax({
                    url : '/eden/rapport/excel/'+this.id_rapport,
                    dataType: "json",
                    method: 'post',
                    data: {
                        'filtres': this.$parent.filtres,
                        'valeurs_filtres': this.$parent.valeurs_filtres
                    },
                    success : function(data){
                        window.open(data.url_fichier, '_blank');
                    },
                });
            }
        },

        computed: {
            resultats_tries : function(){

                var resultats_tries = [];

                for(valeur_y of this.valeurs_y){

                    var lignes = [];

                    var resultats = this.resultats.filter(r => r[this.champ_y] == valeur_y.id);

                    for(valeur_x of this.titres[this.champ_x]){

                        lignes.push(resultats.find(r => r[this.champ_x] == valeur_x.id)?.total_affichage ?? 0);
                    }

                    if(this.cacher_colonnes_vides && lignes.filter(l => l != 0).length == 0)
                        continue;

                    resultats_tries.push({
                        id : valeur_y.id,
                        nom : valeur_y.nom,
                        lignes : lignes
                    });
                }

                return resultats_tries;
            },
            valeurs_y : function(){

                var titres = structuredClone(this.titres[this.champ_y]);

                if(this.tri.valeur == 'axe_y'){

                    if(this.tri.direction == 'desc')
                        titres = titres.reverse();
                }
                else if(this.tri.valeur == 'total'){

                    var totaux = {};

                    for(valeur_y of titres){

                        var resultats = this.resultats.filter(r => r[this.champ_y] == valeur_y.id);

                        totaux[valeur_y.id] = Math.round(resultats.reduce((acc, r) => acc + (parseFloat(r.total_1 ?? 0)), 0)* 100) / 100;
                    }

                    titres = titres.sort((a,b) => {

                        if(this.tri.direction == 'asc')
                            return (parseFloat(totaux[a.id]) ?? 0) - (parseFloat(totaux[b.id]) ?? 0);
                        else
                            return (parseFloat(totaux[b.id]) ?? 0) - (parseFloat(totaux[a.id]) ?? 0);
                    });
                }
                else if(this.tri.valeur){

                    var totaux = {};

                    for(valeur_y of titres){
                        totaux[valeur_y.id] = this.resultats.filter(r => r[this.champ_y] == valeur_y.id && r[this.champ_x] == this.tri.valeur)[0]?.total_1 ?? 0;
                    }

                    titres = titres.sort((a,b) => {

                        if(this.tri.direction == 'asc')
                            return (parseFloat(totaux[a.id]) ?? 0) - (parseFloat(totaux[b.id]) ?? 0);
                        else
                            return (parseFloat(totaux[b.id]) ?? 0) - (parseFloat(totaux[a.id]) ?? 0);
                    });
                    
                }

                return titres;
            },
            totaux : function(){

                var totaux = {
                    'champ_y' : {},
                    'champ_x' : {},
                    'total' : 0
                };

                if(this.champ_x != 'serie'){

                    for(valeur_y of this.valeurs_y){

                        var resultats = this.resultats.filter(r => r[this.champ_y] == valeur_y.id);

                        var total = Math.round(resultats.reduce((acc, r) => acc + (parseFloat(r.total_1 ?? 0)), 0) * 100) / 100;

                        totaux.total += total;

                        totaux.champ_y[valeur_y.id] = total.toString().replace('.',',');
                    }
                }

                if(this.champ_y != 'serie'){
                    for(valeur_x of this.titres[this.champ_x]){

                        var resultats = this.resultats.filter(r => r[this.champ_x] == valeur_x.id);

                        var total = Math.round(resultats.reduce((acc, r) => acc + (parseFloat(r.total_1 ?? 0)), 0) * 100) / 100;

                        totaux.champ_x[valeur_x.id] = total.toString().replace('.',',');
                    }
                }

                if(totaux.total != 0)
                    totaux.total = (Math.round(totaux.total * 100) / 100).toString().replace('.',',');

                return totaux;
            }
        }
    });
</script>
