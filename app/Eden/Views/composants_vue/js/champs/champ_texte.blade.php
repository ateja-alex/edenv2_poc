<script>
    const champ_texte = Vue.component('champ-texte', {
        template: `<div class="champ_texte">
            <div class="champ_texte_conteneur">
                <a v-show="!colonne_champ" class="fas fa-envelope css_background_couleur_primaire champ_texte_icone_mail css_pointer css_input_ajout_selection_element"
                    :href="'mailto:' + modele[nom_sql]" target="_blank" v-if="modele[nom_sql] && modele_champ.format_champ == 'email'"></a>
                <a :class="(modele_champ.contenu && !colonne_champ ? modele_champ.contenu[0] + ' ' : '') + 'champ_texte_icone_url css_pointer css_input_ajout_selection_element'"
                   :style="modele_champ.contenu ? 'color:'+modele_champ.contenu[1]+';background-color:'+modele_champ.contenu[2]+';' : ''"
                   :href="modele[nom_sql]" target="_blank" v-if="modele[nom_sql] && modele_champ.format_champ == 'url'"></a>
                <i v-show="!colonne_champ" class="fas fa-folder-open css_background_couleur_primaire champ_texte_icone_dossier css_pointer css_input_ajout_selection_element"
                    @click="ouvrir_dossier()" aria-hidden="true" v-if="modele[nom_sql] && modele_champ.format_champ == 'dossier'"></i>

                <div class="input_scribens_container" v-if="scribens_active && scribens_cle_api">
                    <div class="bouton_correction_scribens" @click="scribens_check(id_random,scribens_cle_api);$event.target.style='display:none;'" ></div>
                    <input :class="(erreur_validation !== null ? 'champ_texte_erreur_input ' : '') + 'champ_texte_input'" :name="name"
                        v-model="modele[nom_sql]" :type="modele_champ.format_champ == 'password' && !montrer_contenu_mdp ? 'password' : 'text'"
                        @change="modification_champ()" @keyup="respect_casse_format" :maxlength="taille_maximale" :minlength="taille_minimale"
                        @paste="paste($event)" :id="id_random" autocomplete="off" :disabled="lecture_seule">
                </div>
                <input v-else :class="(erreur_validation !== null ? 'champ_texte_erreur_input ' : '') + 'champ_texte_input'" :name="name"
                    v-model="modele[nom_sql]" :type="modele_champ.format_champ == 'password' && !montrer_contenu_mdp ? 'password' : 'text'"
                    @change="modification_champ()" @keyup="respect_casse_format" :maxlength="taille_maximale" :minlength="taille_minimale"
                    @paste="paste($event)" :id="id_random" autocomplete="off" :disabled="lecture_seule">

                <span class="champ_texte_nombre_max_caracteres" v-if="modele_champ.nombre_max_caracteres">
                    <span v-if="!modele[nom_sql]">0</span>
                    <span v-else v-text="modele[nom_sql].length"></span>
                    /<span v-text="modele_champ.nombre_max_caracteres"></span>
                </span>
                <i class="champ_texte_afficher_mdp fas fa-eye css_pointer css_input_ajout_selection_element css_background_couleur_primaire" @click="montrer_contenu_mdp = !montrer_contenu_mdp" aria-hidden="true" v-if="modele_champ.format_champ == 'password'"></i>
            </div>
            <span class="champ_texte_erreur_validation" v-if="erreur_validation" v-html="erreur_validation"></span>
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
            colonne_champ : {
                type:Boolean,
                default: false,
            },
            scribens_active: false,
            scribens_cle_api: '',
            type_element: '',
            modele_champ_force: {
                type: Object,
                default: function(){
                    return null;
                }
            },
        },
        data: function(){
            return {

                nombre_max_caracteres : null,
                erreur_validation: null,
                montrer_contenu_mdp : false,
                modele_champ: {},
                formats_verifiables : {!! collect(\App\Eden\Variables::$formats_champ_texte_verifiables) !!},
            }
        },
        computed: {

            id_random: function() {

                length = 15;

                var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

                if (! length) {
                    length = Math.floor(Math.random() * chars.length);
                }

                var str = '';
                for (var i = 0; i < length; i++) {
                    str += chars[Math.floor(Math.random() * chars.length)];
                }

                return str;
            },

            taille_maximale: function() {

                var taille_maxi = null;

                if(this.modele_champ.format_champ === 'siren')
                    taille_maxi = 11;
                else if(this.modele_champ.format_champ === 'siret')
                    taille_maxi = 17;
                else if(this.modele_champ.format_champ === 'nic')
                    taille_maxi = 5;
                else if(this.modele_champ.nombre_max_caracteres != null && this.modele_champ.nombre_max_caracteres != false)
                    taille_maxi = this.modele_champ.nombre_max_caracteres;

                return taille_maxi;
            },
            taille_minimale: function() {

                var taille_mini = null;

                if(this.modele_champ.format_champ === 'siren')
                    taille_mini = 11;
                else if(this.modele_champ.format_champ === 'siret')
                    taille_mini = 17;
                else if(this.modele_champ.format_champ === 'nic')
                    taille_mini = 5;

                return taille_mini;
            },
        },

        methods : {

            paste: function(evenement) {

                if(['siren', 'siret', 'nic'].includes(this.modele_champ.format_champ))
                    this.$root.copie_siren_nic_siret(evenement);

                this.respect_casse_format(evenement);

                if(this.formats_verifiables.includes(this.modele_champ.format_champ))
                    this.valider_format();
            },

            respect_casse_format: function(evenement) {

                if(this.modele[this.nom_sql] == null)
                    return;

                let champ = null;

                if(evenement != null)
                    champ = evenement.target
                else
                    champ = document.getElementById(this.id_random);

                if(champ == null)
                    return;

                if(['siren', 'siret'].includes(this.modele_champ.format_champ)) {

                    // Sauvegarde de la sélection avant transformation : le formatage ajoute/retire des
                    // espaces de regroupement, donc début ET fin doivent être décalés pareil (jamais
                    // réduits à un seul point, sinon une sélection en cours - ex: Ctrl+C - est perdue)
                    const debut_selection = champ.selectionStart;
                    const fin_selection = champ.selectionEnd;
                    const ancienne_longueur = champ.value.length;

                    this.formater_numero_siren_siret(evenement, champ);

                    this.$nextTick(() => {

                        const difference = champ.value.length - ancienne_longueur;
                        champ.setSelectionRange(
                            debut_selection + difference,
                            fin_selection + difference
                        );
                    });

                    return;
                }

                const ancienne_valeur = this.modele[this.nom_sql];
                let valeur_transformee = ancienne_valeur;

                if(this.modele_champ.format_champ === 'majuscule')
                    valeur_transformee = ancienne_valeur.toUpperCase();
                else if(this.modele_champ.format_champ === 'premiere_lettre_majuscule')
                    valeur_transformee = ancienne_valeur.replace(/(^|[\s\-',.])(\p{L})/gu, (correspondance, separateur, lettre) => {
                        return separateur + lettre.toUpperCase();
                    });
                else if(this.modele_champ.format_champ === 'email')
                    valeur_transformee = ancienne_valeur.toLowerCase();

                if(valeur_transformee === ancienne_valeur)
                    return; // rien à transformer : on ne touche pas à la sélection en cours (Ctrl+C notamment)

                // Sauvegarde de la sélection avant transformation (début ET fin, jamais réduits à un
                // seul point, sinon une sélection en cours - ex: Ctrl+C - est perdue)
                const debut_selection = champ.selectionStart;
                const fin_selection = champ.selectionEnd;
                const ancienne_longueur = champ.value.length;

                this.modele[this.nom_sql] = valeur_transformee;

                // Ajustement du curseur après transformation (les formats majuscule/email/première
                // lettre ne changent jamais la longueur, donc la différence sera 0 en pratique)
                this.$nextTick(() => {

                    const difference = champ.value.length - ancienne_longueur;
                    champ.setSelectionRange(
                        debut_selection + difference,
                        fin_selection + difference
                    );
                });
            },

            formater_numero_siren_siret(evenement, champ) {

                // extraire uniquement les chiffres (on accepte collage avec espaces / tirets / points)
                const chiffres = evenement != null ? champ.value.replace(/\D/g, '') : this.modele[this.nom_sql].replace(/\D/g, '');
                
                let valeur_formatee = '';

                if(this.modele_champ.format_champ === 'siren') {

                    const groupes = chiffres.match(/.{1,3}/g) || [];
                    valeur_formatee = groupes.join(' ');
                }
                else {

                    // SIRET : 3 groupes de 3 puis reste (jusqu'à 5)
                    const partie1 = chiffres.slice(0, 3);
                    const partie2 = chiffres.slice(3, 6);
                    const partie3 = chiffres.slice(6, 9);
                    const reste = chiffres.slice(9);
                    const parties = [partie1, partie2, partie3, reste];
                    
                    valeur_formatee = parties.filter(Boolean).join(' ')
                }

                // Mise à jour du modèle Vue et du champ
                this.$set(this.modele, this.nom_sql, valeur_formatee.trim());
            },
            modification_champ: function(){

                this.$parent.$emit('input_texte_modifie', {
                    nouvelle_valeur : this.modele[this.nom_sql],
                    nom_champ : this.nom_sql,
                });
                
                if(this.formats_verifiables.includes(this.modele_champ.format_champ))
                    this.valider_format();
            },

            valider_format: function(){

                $.ajax({
                    url:'eden/champ/methode/'+this.type_element+'/'+this.nom_sql+'/valider',
                    dataType: 'json',
                    data:{
                        valeur : this.modele[this.nom_sql],
                    }
                }).done((retour) => {

                    if(retour.succes === false)
                        this.erreur_validation = retour.message;
                    else
                        this.erreur_validation = null;
                })
            },

            ouvrir_dossier: function(){

                fetch('http://localhost:5678/open-folder?path=' + encodeURIComponent(this.modele[this.nom_sql]))
                    .catch(() => {
                        toastr.error("Impossible de contacter l'utilitaire d'ouverture de dossier sur ce poste. Vérifiez qu'il est installé et lancé.");
                    });
            },
        },

        created: function() {

            if(this.modele_champ_force != null)
                this.modele_champ = structuredClone(this.modele_champ_force);
            else{
                var requete_champ_libre = typeof this.$root.recuperer_champ_libre == 'function'
                    ? this.$root.recuperer_champ_libre(this.type_element, this.nom_sql)
                    : $.ajax({
                        url:'eden/champs/valeurs/'+this.type_element+'/'+this.nom_sql,
                        dataType:'json'
                    });

                requete_champ_libre.then((champ_libre) => {

                    if(champ_libre == null)
                        return;

                    this.modele_champ = structuredClone(champ_libre);

                    if(this.modele_champ.format_champ === 'url')
                        this.modele_champ.contenu = JSON.parse(this.modele_champ.contenu);

                    if(this.formats_verifiables.includes(this.modele_champ.format_champ))
                        this.valider_format();

                    if(['siren', 'siret'].includes(this.modele_champ.format_champ))
                        this.respect_casse_format();
                });
            }

            this.$watch('modele.' + this.nom_sql, () => {
                this.respect_casse_format();
            });
        }
    });
</script>
