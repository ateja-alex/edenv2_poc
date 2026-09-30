<script>
    const menu_usurpation = Vue.component('menu-usurpation', {
        template:
        `<div class="nav-item css_block_nav_systeme_ticket" v-click_outside>
			<a @click='afficher()' role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-people-arrows css_btn_action_header"></i></a>
			<div class="css_block_popover_usurpation menu_usurpation" v-if="affichage">
                <div class="liste_menu_usurpation">
                    <div class="loader_menu_usurpation" v-if="chargement_elements">
                        <img src="/eden/images/ajax_loader.gif" />
                    </div>
                    <template v-else>
                        <div class="d-flex align-items-center col-12 my-2">
                            <div style="width: 35%">
                                <span>@traduction('interface.usurpation.recherche')</span>
                            </div>
                            <div style="width: 60%">
                                <input class="recherche_usurpation" type="text" v-model="recherche"/>
                            </div>
                        </div>
                        <div v-if="profils.length > 0 || equipes.length > 0"
                            class="d-flex align-items-center justify-content-between col-12 my-2">
                            <span> @traduction('interface.usurpation.trier_par') : </span>
                            <span class="badge_tri_usurpation" v-if="profils.length > 0"
                                @click="tri = (tri == 'profil' ? null : 'profil');categorie_replier=[]"
                                :class="'badge badge-'+(tri == 'profil' ? 'success' : 'default')">
                                @traduction('interface.profil')
                            </span>
                            <span class="badge_tri_usurpation" v-if="equipes.length > 1"
                                @click="tri = (tri == 'equipe' ? null : 'equipe');categorie_replier=[]"
                                :class="'badge badge-'+(tri == 'equipe' ? 'success' : 'default')">
                                @traduction('tables_libres.equipe.nom_table')
                            </span>
                        </div>

                        <template v-for="categorie in utilisateurs_par_categorie">
                            <div class="css_sous_titre_usurpation" @click="categorie_replier.includes(categorie.id) ? categorie_replier.splice(categorie_replier.indexOf(categorie.id),1) : categorie_replier.push(categorie.id)">
                                <i class="icone_categorie icon-eden" :style="'color:'+categorie.couleur"></i>
                                <span class="nom_categorie_usurpation">@{{ categorie.nom }}</span>
                                <span class="css_arrow_down">
                                    <i :class="'fas fa-angle-'+(!categorie_replier.includes(categorie.id) ? 'down' : 'up')"></i>
                                </span>
                            </div>
                            <div class="groupe_utilisateur" v-if="!categorie_replier.includes(categorie.id)">
                                <div v-for="element in categorie.utilisateurs" v-if="element.id !== $root.moi.id"
                                        class="form-check form-check-inline css_usurpation_popover css_checkbox_usurpation"
                                        @click=usurper(element.id)
                                        >
                                        <label class="usurpation_ligne_donnee">
                                            <img v-if="element.avatar != '' && element.avatar != null " :src="'storage/'+element.avatar " alt=""
                                                class="css_avatar_usurpation">
                                            <img v-else src="{{asset('eden/images/no_avatar.jpg')}}" alt="" class="css_avatar_usurpation">
                                            <div class="d-flex flex-column" v-html="element.affichage_pour_recherche?.trim() || element.chaine_affichage?.trim() || ((element.nom || '') + ' ' + (element.prenom || '')).trim()">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>`,

        data: function() {
            return {
                affichage: false,
                tri : null,
                recherche : null,
                categorie_replier: [],
                liste_utilisateurs: [],
                chargement_elements : true,
            }
        },

        methods: {
            afficher: function(){
                this.affichage = !this.affichage;

                if(this.$root.cache.profils == null){
                    this.$set(this.$root.cache,'profils',[]);

                    $.post({
                        url : 'eden/elements/profil',
                        dataType:'json',
                    }).done((elements) => {
                        this.$set(this.$root.cache,'profils',elements);
                    });
                }

                if(this.$root.cache.equipes == null){
                    this.$set(this.$root.cache,'equipes',[]);

                    $.post({
                        url : 'eden/elements/equipe',
                        dataType:'json',
                    }).done((elements) => {
                        this.$set(this.$root.cache,'equipes',elements);
                    });
                }

                if(this.$root.cache_liste_utilisateurs.global){
                    this.liste_utilisateurs = structuredClone(this.$root.cache_liste_utilisateurs.global);
                    this.chargement_elements = false;
                } else {
                    $.post({
                        url: '{{route('base_eden.element.recuperer_liste','utilisateur', false)}}',
                        dataType: "json",

                    }).done(async (donnees) => {

                        this.$set(this.$root.cache_liste_utilisateurs,'global',donnees);

                        this.liste_utilisateurs = donnees;
                        this.chargement_elements = false;
                    });
                }
            },

            usurper: function(id){
                window.location.href = '{{ url('/eden/parametrage/usurpation/') }}/'+id;
            },
        },
        computed: {
            utilisateurs_par_categorie : function(){

                var utilisateurs_par_categorie = [];

                var utilisateurs = this.utilisateurs;

                if(this.recherche != null && this.recherche != '')
                    utilisateurs = utilisateurs.filter(element => element.chaine_tags_recherche && element.chaine_tags_recherche.toLowerCase().includes(this.recherche.toLowerCase()))

                if(this.$root.moi.type_utilisateur == 2) {

                    var utilisateurs_eden = utilisateurs.filter(element => element.type_utilisateur == 2);

                    if(utilisateurs_eden.length > 0)
                        utilisateurs_par_categorie.push({
                            id: 'easydev',
                            nom: this.$root.traduction('interface.usurpation.utilisateurs_eden'),
                            couleur: '#5b40ff',
                            utilisateurs: utilisateurs_eden
                        });
                }

                if(this.tri == null){

                    var utilisateurs_erp = utilisateurs.filter(element => element.type_utilisateur < 2);

                    if(utilisateurs_erp.length > 0)
                        utilisateurs_par_categorie.push({
                            id : 'eden',
                            nom : this.$root.traduction('filtres.cree_filtre_pour_liste.champ_liste.utilisateurs_erp'),
                            utilisateurs : utilisateurs_erp
                        });
                }
                else if(this.tri == 'profil'){

                    for(profil of this.profils){

                        var utilisateurs_profil = utilisateurs.filter(element => element.type_utilisateur != 2 && element.profil_id == profil.id);

                        if(utilisateurs_profil.length > 0)
                            utilisateurs_par_categorie.push({
                                id : profil.id,
                                nom : this.$root.traduction(profil.index_traduction+'.nom'),
                                utilisateurs : utilisateurs_profil
                            });
                    }
                }
                else if(this.tri == 'equipe'){

                    var equipes = this.equipes.sort(function(a,b){
                        return a.nom.localeCompare(b.nom);
                    });

                    for(equipe of this.equipes){

                        var utilisateurs_equipe = utilisateurs.filter(element => element.type_utilisateur != 2 && element.equipe == equipe.id);

                        if(utilisateurs_equipe.length > 0)
                            utilisateurs_par_categorie.push({
                                id : equipe.id,
                                nom : equipe.nom,
                                couleur: equipe.couleur_fond ?? null,
                                utilisateurs : utilisateurs_equipe
                            });
                    }
                }

                return utilisateurs_par_categorie;
            },
            utilisateurs : function(){
                var utilisateurs = this.liste_utilisateurs;

                return utilisateurs.sort(function(a,b){

                    const get_valeur_tri = (utilisateur) =>
                        utilisateur.affichage_pour_recherche?.trim() ||
                        utilisateur.chaine_affichage?.trim() ||
                        ((utilisateur.nom || '') + ' ' + (utilisateur.prenom || '')).trim();

                    return get_valeur_tri(a).localeCompare(get_valeur_tri(b));
                });
            },
            profils : function(){

                if(this.$root.cache.profils == null)
                    return [];

                return this.$root.cache.profils;
            },
            equipes : function(){

                if(this.$root.cache.equipes == null)
                    return [];

                return this.$root.cache.equipes;
            },
        },
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target))) {
                            vnode.context.affichage = false;
                        }
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        },
    });


</script>