<script>
    const filtre_time = Vue.component('filtre-time', {
        template: `
            <div class="filtre_time_dropdown">
                <div class="filtre_time_champ">
                    <span>@traduction('filtres.cree_filtre_pour_liste.champ_time.debut')
                    <input type="time" v-model="debut" :step="filtre.modele.format_champ === 'H:i:s' ? 1 : 60"/>
                </div>
                <div class="filtre_time_champ">
                    @traduction('filtres.cree_filtre_pour_liste.champ_time.fin')
                    <input type="time" v-model="fin" :step="filtre.modele.format_champ === 'H:i:s' ? 1 : 60"/>
                </div>
            </div>
        `,
        props: {
            filtre : {
                type : Object,
                default: function(){
                    return {};
                }
            },
            valeurs : {
                type : Object,
                default: function(){
                    return {
                        debut : null,
                        fin : null,
                    };
                }
            },
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed: {
            debut : {
                get(){
                    return this.valeurs.debut;
                },
                set(valeur){

                    if(valeur == '')
                        valeur = null;

                    this.changement_filtre({
                        debut : valeur,
                        fin : this.valeurs.fin,
                    });
                },
            },
            fin : {
                get(){
                    return this.valeurs.fin;
                },
                set(valeur){

                    if(valeur == '')
                        valeur = null;

                    this.changement_filtre({
                        debut : this.valeurs.debut,
                        fin : valeur,
                    });
                },
            },
        }
    });
</script>
