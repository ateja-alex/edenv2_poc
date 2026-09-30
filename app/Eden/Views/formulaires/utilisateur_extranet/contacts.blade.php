<span class="profil_droits_divers">
    <div  class="gestion_profil_bloc" >
        <div v-if="ajax_chargement_contacts_associes === false" class="type_profil">
            <div class="titre">
                Contacts associés
                <div class="actions" v-click_en_dehors="">
                    <i class="fas fa-plus" @click="ajout_contact = true"></i>
                    <div v-if="ajout_contact" class="action_popover">
                        <champ-selection-element type_element="contact" 
                            nom_sql="contact_id" :modele="{contact_id : null}"
                            :desactiver_creation_a_la_volee="true"
                            :filtrage="[{champ:'id',condition:'whereNotIn',valeur:contacts.map(c => c.id)},
                            {champ:'utilisateur_extranet_id',condition:'whereNull',valeur:null}]"
                        ></champ-selection-element>
                    </div>
                </div>
            </div>
            <div style="display: grid;grid: auto-flow / 1fr 1fr 1fr;">
                <div class="valeurs" v-for="contact in contacts_sort" :key="contact.id">
                    <label :for="'acces_'+contact.id" 
                        v-html="contact.chaine_affichage"></label>
                    <input type="checkbox" :disabled="$parent.options.contact_id_source == contact.id || (utilisateur_extranet.contacts_associes.length == 1 && utilisateur_extranet.contacts_associes.includes(contact.id))" 
                        v-model="utilisateur_extranet.contacts_associes" :id="'acces_'+contact.id" :value="contact.id">
                    <input type="hidden" name="contacts[]" :value="contact.id" v-if="utilisateur_extranet.contacts_associes.includes(contact.id)">
                </div>
                <input type="hidden" name="contacts" value="" v-if="utilisateur_extranet.contacts_associes === undefined || utilisateur_extranet.contacts_associes.length == 0">
            </div>
        </div>
    </div>
</span>


@push('donnees_pour_vuejs_data')

    contacts : [],
    ajax_chargement_contacts_associes: false,
    chargement_termine: false,
    ajout_contact : false,

@endpush

@push('donnees_pour_vuejs_mounted')

    this.$root.$on('selection-element',(parametres) => {

        if(parametres.nom_champ == 'contact_id'){

            this.ajout_contact = false;

            if(this.utilisateur_extranet.contacts_associes === undefined)
                this.utilisateur_extranet.contacts_associes = [];

            this.utilisateur_extranet.contacts_associes.push(parametres.element.id);
            this.contacts.push(parametres.element);
        }
    });

    this.$on('input_texte_modifie',(parametres) => {

        if(parametres.nom_champ == 'email')
            this.chargement_contacts_associes();
    });

    this.chargement_contacts_associes();

@endpush

@push('donnees_pour_vuejs_methods')

    chargement_contacts_associes : function(){

        if(this.ajax_chargement_contacts_associes !== false)
            this.ajax_chargement_contacts_associes.abort();

        var filtrage = [];

        if(this.$parent.options.contact_id_source)
            filtrage.push({
                champ : 'id',
                condition : 'where',
                valeur : this.$parent.options.contact_id_source
            });

        if(this.utilisateur_extranet.id > 0)
            filtrage.push({
                champ : 'utilisateur_extranet_id',
                condition : 'orWhere',
                valeur : this.utilisateur_extranet.id
            });

        if(this.utilisateur_extranet.email && this.utilisateur_extranet.email != '')
            filtrage.push({
                champ : 'adresse_email',
                condition : 'orWhere',
                valeur : this.utilisateur_extranet.email
            });

        if(filtrage.length == 0){

            this.ajax_chargement_contacts_associes = false;
            this.$set(this.utilisateur_extranet, 'contacts_associes', []);
            this.contacts = [];

            return;
        }
        

        this.ajax_chargement_contacts_associes = $.post({
            url : 'eden/elements/contact',
            dataType : 'json',
            data:{
                filtrage:filtrage
            }
        }).done(async (elements) => {

            this.contacts = elements.concat(this.contacts.filter(c => this.utilisateur_extranet.contacts_associes.includes(c.id) && !elements.map(e => e.id).includes(c.id)));

            if(this.utilisateur_extranet.contacts_associes === undefined)
                this.$set(this.utilisateur_extranet, 'contacts_associes', elements.filter(c => c.utilisateur_extranet_id == this.utilisateur_extranet.id 
                    || (this.$parent.options.contact_id_source && this.$parent.options.contact_id_source == c.id)).map(c => c.id));

            this.ajax_chargement_contacts_associes = false;
        });
    },

@endpush

@push('donnees_pour_vuejs_computed')

    contacts_sort : function(){
        return this.contacts.sort((a,b) => {

            if(this.$parent.options.contact_id_source){
                if (a.id === this.$parent.options.contact_id_source) return -1;
                if (b.id === this.$parent.options.contact_id_source) return 1;
            }

            const inA = this.utilisateur_extranet.contacts_associes.includes(a.id);
            const inB = this.utilisateur_extranet.contacts_associes.includes(b.id);
            if (inA && !inB) return -1;
            if (!inA && inB) return 1;
            return 0;        
        });
    },
@endpush

@push('donnees_pour_vuejs_directives')

    click_en_dehors : {
        bind: function (el, binding, vnode) {
            el.clickOutsideEvent = function (event) {
                if (!(el.contains(event.target)))
                    vnode.context.ajout_contact = false;
            };
            document.body.addEventListener('click', el.clickOutsideEvent)
        },
        unbind: function (el) {
            document.body.removeEventListener('click', el.clickOutsideEvent)
        },
    },

@endpush