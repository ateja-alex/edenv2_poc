<script>
    const selection_email = Vue.component('selection-email', {
        template: `
            <div v-click_outside="" class="selection_email">
                <component :key="cle_component" ref="content_editable" @click="chargement_selection()" :is="content_editable" :emails="modele" :couleurs_groupes="couleurs_groupes" :type_destinataire="type_destinataire"></component>
                <dropdown v-if="selection_choix">
                    <div class="titre" v-if="propositions_recherche.length > 0">
                        @traduction('composant.emails.selection_email.adresses_suggerees')
                    </div>
                    <div v-else-if="recherche.includes('@') && !recherche.endsWith('@') && !recherche.startsWith('@')" @click.stop="choix_proposition(
                        {
                            valeur : recherche,
                            obligatoire : false,
                        })">
                        @traduction('composant.emails.selection_email.utiliser_cette_adresse') : <strong>@{{ recherche }}</strong>
                    </div>
                    <div v-for="proposition in propositions_recherche" @click.stop="choix_proposition(proposition)">
                        <span v-html="proposition.affichage_select ?? proposition.valeur"></span>
                    </div>
                </dropdown>
            </div>
        `,
        props : {
            type_element : {
                type : String,
                required : true,
            },
            elements : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            modele : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            defaut : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            type_destinataire : {
                type : Number,
                required : true,
            },
            parametres_destinataires : {
                type : Object,
                default : function(){
                    return {};
                },
            },
            couleurs_groupes : {
                type : Object,
                default : function(){
                    return {};
                },
            },
        },
        data:function(){
            return{
                selection_choix : false,
                propositions : [],
                content_editable : null,
                cle_component : 0,
                recherche : '',
            }
        },
        mounted : async function(){

            this.content_editable = {
                name:"zone_recherche_email",
                template: `<div contenteditable="true" @click="clic_zone()" ref="div_editable" @input="gestion_recherche($event)" @keydown="gestion_suppression($event)" style="border: 1px solid #d4d4d4;padding:5px;width: 100%;">
                        <template v-for="(modele,index_modele) in emails"><span class="saisie_email" :key="'saisie_'+index_modele">\u200B</span><div contenteditable="false" :email="modele.valeur" :key="modele.valeur" class="mail_selectionne" :style="'display: inline-block;padding: 0px 10px;margin-right: 3px;border-radius: 3px;user-select: none;background: '+( couleurs_groupes[type_destinataire+'_'+modele.groupe_id] ?? '#e0e0e0')">
                                <span v-html="modele.affichage ?? modele.valeur"></span>
                                <i class="fas fa-times" v-if="!modele.obligatoire" style="margin-left: 5px;cursor: pointer;" @click.stop="$parent.suppresion_modele(index_modele)"></i>
                            </div></template><span class="saisie_email" key="saisie_fin">\u200B</span>
                    </div>`,
                props : {
                    emails : {
                        type : Array,
                        required: true,
                    },
                    couleurs_groupes : {
                        type : Object,
                        default : function(){
                            return {};
                        },
                    },
                    type_destinataire : {
                        type : Number,
                        required : true,
                    },
                },
                data : function(){
                    return {
                        position_curseur : null,
                    }
                },
                methods : {
                    gestion_recherche : function(evenement){
                        this.calcul_position_curseur();

                        const div = this.$refs.div_editable;
                        const emails_dom = Array.from(div.querySelectorAll('div.mail_selectionne')).map(node => node.getAttribute('email'));
                        const emails_modele = this.emails.map(e => e.valeur);

                        if (emails_dom.length !== emails_modele.length || !emails_modele.every(e => emails_dom.includes(e)))
                            this.$parent.suppresion_modele(this.emails.findIndex(e => !emails_dom.includes(e.valeur)));
                        else
                            this.$parent.gestion_recherche(evenement);
                    },
                    clic_zone : function(){
                        this.calcul_position_curseur();

                        if (this.zone_saisie_courante() == null)
                            this.focus_position(this.position_curseur);

                        this.$emit('click');
                    },
                    zones_saisie : function(){
                        return Array.from(this.$refs.div_editable.querySelectorAll('span.saisie_email'));
                    },
                    zone_saisie_courante : function(){
                        const selection = window.getSelection();
                        if (!selection.rangeCount)
                            return null;

                        var noeud = selection.getRangeAt(0).startContainer;
                        if (noeud.nodeType === Node.TEXT_NODE)
                            noeud = noeud.parentNode;

                        return noeud != null && noeud.classList != null && noeud.classList.contains('saisie_email') ? noeud : null;
                    },
                    gestion_suppression : function(evenement){
                        if (evenement.key != 'Backspace' && evenement.key != 'Delete')
                            return;

                        const selection = window.getSelection();
                        if (!selection.isCollapsed)
                            return;

                        const zone = this.zone_saisie_courante();
                        if (zone == null || zone.textContent.replace(/\u200B/g, '') != '')
                            return;

                        const index_zone = this.zones_saisie().indexOf(zone);
                        const index_email = evenement.key == 'Backspace' ? index_zone - 1 : index_zone;

                        if (this.emails[index_email] == null || this.emails[index_email].obligatoire)
                            return;

                        evenement.preventDefault();
                        this.$parent.suppresion_modele(index_email);
                    },
                    calcul_position_curseur : function(){
                        const div = this.$refs.div_editable;
                        const selection = window.getSelection();

                        if (!selection.rangeCount || !div.contains(selection.getRangeAt(0).startContainer)){
                            this.position_curseur = this.emails.length;
                            return;
                        }

                        const curseur = selection.getRangeAt(0);
                        var position = 0;

                        for (const noeud of Array.from(div.childNodes)){

                            if (noeud.nodeType !== Node.ELEMENT_NODE || !noeud.classList.contains('mail_selectionne'))
                                continue;

                            if (curseur.comparePoint(div, Array.prototype.indexOf.call(div.childNodes, noeud)) >= 0)
                                break;

                            position++;
                        }

                        this.position_curseur = position;
                    },
                    focus_position : function(position){
                        const div = this.$refs.div_editable;
                        const zones = this.zones_saisie();
                        const zone = zones[Math.max(0, Math.min(position, zones.length - 1))];

                        div.focus();

                        if (zone == null)
                            return;

                        if (zone.firstChild == null)
                            zone.appendChild(document.createTextNode('\u200B'));

                        const range = document.createRange();
                        range.setStart(zone.firstChild, zone.firstChild.length);
                        range.collapse(true);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        this.position_curseur = position;
                    },
                }
            }
        },
        methods : {
            gestion_recherche : function(evenement){

                var conteneur = evenement.target.cloneNode(true);
                const mails = conteneur.querySelectorAll('div.mail_selectionne');
                mails.forEach(div => div.remove());
                this.recherche = conteneur.textContent.replace(/\u200B/g, '').trim();
            },
            choix_proposition : function(proposition){

                var position_curseur = this.$refs.content_editable.position_curseur;

                var nouvelle_liste = [...this.modele];
                nouvelle_liste.splice(position_curseur, 0, proposition);

                this.$emit('maj_modele', {mails : nouvelle_liste, position : position_curseur});
                this.recherche = '';
                this.selection_choix = false;
                this.$nextTick(() => {
                    this.$refs.content_editable.focus_position(position_curseur + 1);
                });
            },

            suppresion_modele : async function(index_modele){
                this.$emit('maj_modele', {mails : this.modele.filter((mod,idx) => idx != index_modele)});
                await  this.$nextTick();
                this.selection_choix = false;
                this.$refs.content_editable.focus_position(index_modele);
            },

            chargement_selection : function(){
                $.post({
                    url : 'eden/email/chargement/destinataires',
                    dataType : 'json',
                    data : {
                        groupes_ids : [
                            this.elements.map(e => e.id)
                        ],
                        parametres : {
                            niveau : 1,
                            ...this.parametres_destinataires,
                        }
                    },
                }).done(async(data) => {

                    this.propositions = this.elements.length > 0 ? data.valeurs[0][0] : data.valeurs_globales[0];
                    this.selection_choix = true;
                });
            },
        },
        computed : {
            propositions_recherche : function(){

                var propositions = this.defaut
                    .concat(this.propositions.filter(p => !this.defaut.map(d => d.valeur).includes(p.valeur)))
                    .filter(p => !this.modele.map(m => m.valeur).includes(p.valeur));

                if(this.recherche == '')
                    return propositions;

                return propositions.filter(p => p.valeur.toLowerCase().includes(this.recherche.toLowerCase()));
            },
        },
        watch:{
            modele : function(){
                this.cle_component++; 
            },
        },
        directives : {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (event.target.parentElement != null && !(el.contains(event.target)) && vnode.context.selection_choix)
                            vnode.context.selection_choix = false;
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
    });
</script>