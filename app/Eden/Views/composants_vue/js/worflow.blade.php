<script>
    const workflow = Vue.component('workflow', {
        template: `<div class="css_conteneur_progression_ticket">
                        <div class="steps-container">
                            <template v-for="(valeur,cle) in valeurs_listes">
                                <div class="step" style="cursor: pointer;" @click="modifie_valeur_workflow(valeur.id_valeur)"
                                    :class="{'completed':(ordre_valeur_actuel > cle), 'in-progress':(ordre_valeur_actuel == cle)}">
                                    <svg v-if="ordre_valeur_actuel > cle" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
                                    </svg>
                                    <div v-if="ordre_valeur_actuel == cle" class="preloader"></div>

                                    <div class="label"
                                        :class="{'completed':(ordre_valeur_actuel > cle), 'loading':(ordre_valeur_actuel == cle)}">
                                        @{{ valeur.valeur }}
                                    </div>
                                    <div class="icon"
                                        :class="{'completed':(ordre_valeur_actuel > cle), 'in-progress':(ordre_valeur_actuel == cle)}">
                                       <i :class="(valeur.icone ? valeur.icone : 'fas fa-thumbtack')"></i>
                                    </div>
                                </div>
                                <div v-if="cle != valeurs_listes.length - 1" class="line"
                                    :class="{'completed':(ordre_valeur_actuel > cle + 1), 'next-step-in-progress':(ordre_valeur_actuel == cle + 1), 'prev-step-in-progress':(ordre_valeur_actuel == cle)}"></div>
                            </template>
                        </div>
                    </div>`,
        props:{
            champ_libre :{
                type : Object,
                default : null
            },
            modele_element : {
                type : Object,
                default : null
            }
        },
        computed: {

            valeurs_listes : function(){

                var valeurs_listes = [];

                if(this.champ_libre.type == 1){

                    var id_cl = this.champ_libre.liste_choix > 0 ? this.champ_libre.liste_choix : this.champ_libre.id_cl;

                    var valeur_liste = this.$root.valeurs_listes_libres[id_cl];

                     for(index in valeur_liste){
                        if(index != 'liaisons' && index != 'categorie')
                            valeurs_listes = valeurs_listes.concat(this.$root.affichage_valeur_liste_libre(this.modele_element ,valeur_liste[index],valeur_liste.liaisons, this.champ_libre.type_element, this.champ_libre.nom_sql)
                                .filter(element => element.id_valeur != 0 || (element.id_valeur == 0 && !this.champ_libre.cacher_sans_valeur)));
                    }

                    valeurs_listes.sort((a,b) => (a.ordre > b.ordre) ? 1 : ((b.ordre > a.ordre) ? -1 : 0));
                }
                else if(this.champ_libre.type == 20)
                    valeurs_listes = Object.values(this.$root.valeurs_listes_formatees[this.champ_libre.liste_choix]);

                if(this.champ_libre.cacher_sans_valeur)
                    valeurs_listes = valeurs_listes.filter((valeur) => valeur.id_valeur !== 0);
                
                return valeurs_listes;
            },

            ordre_valeur_actuel : function(){

                var ordre = -1;

                for(cle in this.valeurs_listes){

                    var valeur = this.valeurs_listes[cle];

                    if(valeur.id_valeur == this.modele_element[this.champ_libre.nom_sql])
                        ordre = cle;
                }

                return ordre;
            }
        },
        methods : {

            modifie_valeur_workflow: function(nouvelle_valeur) {

                if(this.champ_libre.lecture_seule)
                    return;

                this.modele_element[this.champ_libre.nom_sql] = nouvelle_valeur;

                this.enregistrer_workflow(nouvelle_valeur);

            },

            enregistrer_workflow: function (nouvelle_valeur){

                $.post({
                    url: "/eden/element/" + this.champ_libre.type_element + "/" + this.modele_element.id + "/enregistrer",
                    dataType: "json",
                    data: { [this.champ_libre.nom_sql]: nouvelle_valeur }
                }).done(async (donnees) => {

                    if(donnees.retour !== true) {
                        await erreur(donnees.retour);
                        return;
                    }

                    Object.assign(this.modele_element, donnees.changement_enregistrement);
                });

            },
        }
    });
</script>
