<script>
    const filtre_utilisateur = Vue.component('filtre-utilisateur', {
        template: `
            <div>
                <div v-if="affichage">
                    <template v-if="options.includes(valeurs)">
                        <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.'+this.valeurs)"></span>
                    </template>
                    <template v-else-if="!Array.isArray(valeurs) && valeurs.startsWith('lien_champ|')">
                        <span>@traduction('filtres.champ_recherche_element.contenu')</span>
                        <span class="variable" v-html="affichage_lien_champ"></span>
                    </template>
                    <template v-else>
                        @traduction('filtres.champ_utilisateur.contenu')
                        <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
                    </template>
                </div>
                <div v-else class="css_liste_checkbox_popover filtre_utilisateur">
                    <div style="display: flex;align-items: center;justify-content: center;min-width:200px;" v-if="chargement_elements">
                        <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
                    </div>
                    <template v-else>
                        <div v-if="!gestion_lien_champ" class="d-flex" style="padding:10px;gap: 10px;">
                            <label v-for="option in options" style="display: flex;gap: 5px;">
                              <input
                                  type="radio"
                                  :value="option"
                                  v-model="valeur_checkbox"
                              />
                              <span>@{{ $root.traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.'+option) }}</span>
                            </label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between col-12 my-2">
                            <template v-if="gestion_lien_champ">
                                <parametrage-lien-champ
                                    :lien_champ="valeur_checkbox != null ? valeur_checkbox.replace('lien_champ|','') : ''"
                                    @changement_lien_champ="valeur_checkbox = 'lien_champ|'+$event"
                                    :type_element="type_element_source"
                                    :filtres_valeur_final="{
                                        champs : [
                                            {type_element_ajax : filtre.modele.type_element_ajax},
                                            {type_element : filtre.modele.type_element_ajax, nom_sql : 'id'},
                                        ]
                                    }"
                                    :valeur_unique="true"></parametrage-lien-champ>
                            </template>
                            <template v-else>
                                <div style="width: 40%">
                                    <span>@traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche')</span>
                                </div>
                                <div style="width: 60%">
                                    <input type="text" v-model="recherche" style="height: 24px; border: 1px solid rgb(228, 224, 224);"/>
                                </div>
                            </template>
                            <i v-if="type_element_source != null" @click="gestion_lien_champ = !gestion_lien_champ;valeur_checkbox = null" class="css_action_icon fas fa-random"></i>
                        </div>
                        <template v-if="!gestion_lien_champ">
                            <div v-if="profils.length > 0 || equipes.length > 0"
                                class="d-flex align-items-center justify-content-between col-12 my-2">
                                <span> @traduction('filtres.cree_filtre_pour_liste.champ_liste.trier_par') : </span>
                                <span style="font-size: 11px;" v-if="profils.length > 0"
                                    @click="tri = (tri == 'profil' ? null : 'profil');categorie_replier=[]"
                                    :class="'badge badge-'+(tri == 'profil' ? 'success' : 'default')">
                                    @traduction('interface.profil')
                                </span>
                                <span style="font-size: 11px;" v-if="equipes.length > 1"
                                    @click="tri = (tri == 'equipe' ? null : 'equipe');categorie_replier=[]"
                                    :class="'badge badge-'+(tri == 'equipe' ? 'success' : 'default')">
                                    @traduction('tables_libres.equipe.nom_table')
                                </span>
                            </div>
                            <div v-if="utilisateurs.filter(x => x.id == $root.moi.id).length > 0 || ($parent.$options.name == 'recherche-avancee' && $parent.parametres_recherche_avancee.utilisateur_id == null)" class="form-check form-check-inline
                                css_checkbox_popover css_checkbox_utilisateur">
                                <label class="filtre_utilisateur_ligne_donnee">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        value="#utilisateur_connecte#"
                                        v-model="valeurs_checkbox"
                                        name="valeurs[]">
                                    <img :src="($root.moi.avatar != null && $root.moi.avatar != '' && $root.moi.avatar != undefined && ($parent.$options.name != 'recherche-avancee' || $parent.parametres_recherche_avancee.utilisateur_id > 0) ? 'storage/'+$root.moi.avatar : 'eden/images/no_avatar.jpg')"
                                        alt="" class="css_avatar_filtre_user">
                                    <div class="d-flex flex-column">
                                        @traduction('filtres.cree_filtre_pour_liste.champ_liste.moi')
                                    </div>
                                </label>
                            </div>

                            <template v-for="categorie in utilisateurs_par_categorie">
                                <div class="css_sous_titre_filtre_utilisateurs" @click="categorie_replier.includes(categorie.id) ? categorie_replier.splice(categorie_replier.indexOf(categorie.id),1) : categorie_replier.push(categorie.id)">
                                    <i class="icone_categorie icon-eden" :style="'color:'+categorie.couleur"></i>
                                    <span style="width:105px;max-width: 105px;">@{{ categorie.nom }}</span>
                                    <span class="badge badge-default" v-if="categorie.utilisateurs.filter(x => valeurs_checkbox.includes(x.id)).length > 0"
                                        @click.stop="deselectionnner(categorie.utilisateurs)">
                                        @traduction('filtres.cree_filtre_pour_liste.champ_liste.tout_deselectionner')
                                    </span>
                                    <span class="badge badge-default" v-if="categorie.utilisateurs.filter(x => valeurs_checkbox.includes(x.id)).length < categorie.utilisateurs.length"
                                        @click.stop="selectionnner(categorie.utilisateurs)">
                                        @traduction('filtres.cree_filtre_pour_liste.champ_liste.tout_selectionner')
                                    </span>
                                    <span class="css_arrow_down">
                                        <i :class="'fas fa-angle-'+(!categorie_replier.includes(categorie.id) ? 'down' : 'up')"></i>
                                    </span>
                                </div>
                                <div class="groupe_utilisateur" v-if="!categorie_replier.includes(categorie.id)">
                                    <div v-for="element in categorie.utilisateurs"
                                            class="form-check form-check-inline css_checkbox_popover css_checkbox_utilisateur">
                                            <label class="filtre_utilisateur_ligne_donnee">

                                                <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :value="element.id"
                                                        v-model="valeurs_checkbox"
                                                        name="valeurs[]"
                                                >
                                                <img v-if="element.modele.avatar != '' && element.modele.avatar != null " :src="'storage/'+element.modele.avatar " alt=""
                                                    class="css_avatar_filtre_user">
                                                <img v-else src="{{asset('eden/images/no_avatar.jpg')}}" alt="" class="css_avatar_filtre_user">
                                                <div class="d-flex flex-column" v-html="element.affichage_pour_recherche">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                            </template>
                        </template>
                    </template>
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
                type : [String, Array],
                default: function(){
                    return [];
                }
            },
            affichage: {
                type : Boolean,
                default: false
            },
            type_element_source: {
                type : String,
                default : null,
            },
        },
        data:function(){
            return {
                tri : null,
                recherche : null,
                categorie_replier: [],
                liste_utilisateurs: [],
                chargement_elements : true,
                options:['vide','non_vide'],
                gestion_lien_champ :false,
            }
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
            deselectionnner : function(utilisateurs){

                var valeurs_checkbox = this.valeurs_checkbox;

                for(utilisateur of utilisateurs){

                    if(valeurs_checkbox.includes(utilisateur.id))
                        valeurs_checkbox.splice(valeurs_checkbox.indexOf(utilisateur.id),1);
                }

                this.valeurs_checkbox = valeurs_checkbox;
            },
            selectionnner : function(utilisateurs){

                var valeurs_checkbox = this.valeurs_checkbox;

                for(utilisateur of utilisateurs){

                    if(!valeurs_checkbox.includes(utilisateur.id))
                        valeurs_checkbox.push(utilisateur.id);
                }

                this.valeurs_checkbox = valeurs_checkbox;
            },
        },
        computed: {
            utilisateurs_par_categorie : function(){

                var utilisateurs_par_categorie = [];

                var utilisateurs = this.utilisateurs;

                if(this.recherche != null && this.recherche != '')
                    utilisateurs = utilisateurs.filter(element => element.modele.chaine_tags_recherche && element.modele.chaine_tags_recherche.toLowerCase().includes(this.recherche.toLowerCase()))

                if(this.$root.moi.type_utilisateur == 2) {

                    var utilisateurs_eden = utilisateurs.filter(element => element.modele.type_utilisateur == 2);

                    if(utilisateurs_eden.length > 0)
                        utilisateurs_par_categorie.push({
                            id: 'easydev',
                            nom: 'Equipe EDEN',
                            couleur: '#5b40ff',
                            utilisateurs: utilisateurs_eden
                        });
                }

                if(this.tri == null){

                    var utilisateurs_erp = utilisateurs.filter(element => element.modele.type_utilisateur < 2);

                    if(utilisateurs_erp.length > 0)
                        utilisateurs_par_categorie.push({
                            id : 'eden',
                            nom : this.$root.traduction('filtres.cree_filtre_pour_liste.champ_liste.utilisateurs_erp'),
                            utilisateurs : utilisateurs_erp
                        });

                    var utilisateurs_ressource = utilisateurs.filter(element => element.modele.type_utilisateur == 3);

                    if(utilisateurs_ressource.length > 0)
                        utilisateurs_par_categorie.push({
                            id : 'ressource',
                            nom : 'Ressource',
                            utilisateurs : utilisateurs_ressource
                        });
                }
                else if(this.tri == 'profil'){

                    for(profil of this.profils){

                        var utilisateurs_profil = utilisateurs.filter(element => element.modele.type_utilisateur != 2 && element.modele.profil_id == profil.id);

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

                        var utilisateurs_equipe = utilisateurs.filter(element => element.modele.type_utilisateur != 2 && element.modele.equipe == equipe.id);

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

                    return a.affichage_pour_recherche.localeCompare(b.affichage_pour_recherche);
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
            valeur_checkbox : {
                get(){
                    if(Array.isArray(this.valeurs))
                        return null;

                    return this.valeurs;
                },
                set(valeur){
                    this.changement_filtre(valeur);
                },
            },
            valeurs_checkbox : {
                get(){

                    if(!Array.isArray(this.valeurs))
                        return [];

                    return this.valeurs.map((x) => {return x == '#utilisateur_connecte#' ? x : parseInt(x)});
                },
                set(valeur){
                    if(valeur.length == 0)
                        valeur = null;

                    this.changement_filtre(valeur);
                },
            },
            valeurs_selectionnes : function(){

                var valeurs_selectionnes = [];

                if(this.valeurs_checkbox.includes('#utilisateur_connecte#'))
                    valeurs_selectionnes.push(this.$root.traduction('filtres.cree_filtre_pour_liste.filtre_utilisateur.utilisateur_connecte'));

                return valeurs_selectionnes.concat(this.utilisateurs.filter(x => this.valeurs_checkbox.includes(x.id)).map((x) => {return x.affichage_pour_recherche}));
            },
            affichage_lien_champ : function(){

                if(Array.isArray(this.valeurs) || !this.valeurs.startsWith('lien_champ|'))
                    return '';

                var affichage_lien_champ = '';

                for(lien_champ of this.valeurs.replace('lien_champ|','').split('/')){

                    if(affichage_lien_champ != '')
                        affichage_lien_champ += ' => ';
                    
                    var partie_lien = lien_champ.split('.');
                    var type_element = partie_lien[0];
                    var nom_sql = partie_lien[1];

                    affichage_lien_champ += (nom_sql == 'id' ? 'ID' : this.$root.traduction('champs_libres.'+type_element+'.'+nom_sql+'.nom'))+' ('+lien_champ+')';
                }

                return affichage_lien_champ;
            },
        },
        mounted : function(){

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

            $.post({
                url: '/eden/element/recherche/utilisateur/%25',
                dataType: "json",
                data: {
                    source: {
                        type_element : this.filtre.modele.type_element,
                        nom_sql : this.filtre.modele.nom_sql,
                    }
                },
            }).done(async (donnees) => {
                this.liste_utilisateurs = donnees;
                this.chargement_elements = false;
            });
        },
    });
</script>
