<script>
    const champ_pourcentage = Vue.component('champ-pourcentage', {
        template: `<div style="display:flex;align-items:center;gap:5px;">
                        <champ-montant :nom_sql="nom_sql"  :modele="modele_pourcentage" :nombre_decimale="nombre_decimale" :lecture_seule="lecture_seule"></champ-montant>
                        <span>%</span>
                        <input type="hidden" :name="name" :value="modele[nom_sql]">
                    </div>`,
        props: {

            nom_sql: '',
            name: '',
            modele: '',
            lecture_seule: {
                type: Boolean | Number,
                default: false,
            },
            nombre_decimale : {
                type : Number | Boolean,
                default: false,
            },
        },
        computed : {
            modele_pourcentage : function(){
                var modele = structuredClone(this.modele);

                if(modele[this.nom_sql] != null && modele[this.nom_sql] != '')
                    modele[this.nom_sql] = ((parseFloat(modele[this.nom_sql])*10000) / (100)).toFixed(6);
                    // ici, on multiplie la valeur par 100 pour la remettre en pourcentage mais on fait *10000 / 100 pour compenser la perte de précision des flottants

                return modele;
            }
        },
        mounted : function(){
            this.$on('maj_champ_montant',(parametres_emit) => {
                this.modele[this.nom_sql] = parametres_emit.modele[this.nom_sql] / 100;
            });
        },
    });
</script>