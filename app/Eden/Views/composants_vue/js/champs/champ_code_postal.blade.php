<script>
    const champ_code_postal = Vue.component('champ-code-postal', {
        template: `<div style="width: 100%;">
            <input v-model="modele[nom_sql]" :name="name" :placeholder="placeholder" :disabled="lecture_seule" type="text" @blur="affichage_select = false" @input="changement_code_postal" @focusin="changement_code_postal" />
            <div class="select_code_postal" v-if="affichage_select && communes_recherches !== false">
              <div v-for="commune of communes_recherches" @mousedown.stop="choix_code_postal(commune)">
                @{{ commune.code_postal }} | @{{ commune.commune.nom }}
              </div>
              <div class="aucun_resultat" v-if="communes_recherches.length == 0">
                @traduction('composant.champ_selection_element_multiple.aucun_resultat')
              </div>
            </div>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            placeholder: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            mappage: {
                type: Object,
                default: function(){
                    return {
                        ville : null,
                        region : null,
                    }
                }
            },
        },
        data: function(){
            return {
                communes : [],
                affichage_select : false,
            }
        },
        computed: {

            communes_recherches : function(){

                var code_postal_modele = this.modele[this.nom_sql];

                if(code_postal_modele.length < 2)
                    return false;

                var communes_recherches = [];

                for(commune of this.communes){

                    for(code_postal of commune.codesPostaux){

                        if(code_postal.startsWith(code_postal_modele))
                            communes_recherches.push({
                                code_postal : code_postal,
                                commune : commune
                            });
                    }
                }

                return communes_recherches;
            },
        },

        methods : {

            choix_code_postal : function(commune){

                this.$set(this.modele,this.nom_sql,commune.code_postal);

                if(this.mappage.ville !== null)
                    this.$set(this.modele,this.mappage.ville,commune.commune.nom);

                if(this.mappage.region !== null)
                    this.$set(this.modele,this.mappage.region,commune.commune.region.nom);

                this.affichage_select = false;
            },

            changement_code_postal : function(){

                this.affichage_select = this.modele[this.nom_sql] != null && this.modele[this.nom_sql].length > 1;
            },
        },

        mounted: function() {

            if(this.$root.cache.communes != null)
                this.communes = this.$root.cache.communes;
            else {

                $.ajax({
                    url: 'https://geo.api.gouv.fr/communes?fields=code,nom,region,codesPostaux',
                    dataType: 'json',
                }).done((communes) => {
                    this.$set(this.$root.cache, 'communes', communes);
                    this.communes = communes;
                });

            }

        }
    });
</script>