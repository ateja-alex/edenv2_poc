<div class="row">
    @champ('docusign_signataire','email',2,4)
</div>

<div class="docusign_choix_email">
    <div class="docusign_choix_email" v-for="(emails,type_element) in emails_possibles">
        <div class="docusign_choix_email_element" @click="affichage_emails(type_element)">
            <b v-html="emails.length+' '+$root.traduction('tables_libres.'+type_element+'.'+(emails.length == 1 ? 'element' : 'element_pluriel'))"></b>
            <i :class="'fas fa-chevron-'+(cacher_emails.includes(type_element) ? 'up' : 'down')"></i>
        </div>
        <div class="docusign_choix_email_mails" v-if="!cacher_emails.includes(type_element)">
            <div v-for="adresse_email in emails" class="css_option_choix_sur_formulaire" :class="{css_option_choix_sur_formulaire_actif : docusign_signataire.email == adresse_email}" @click="docusign_signataire.email = adresse_email">
                <p>@{{ adresse_email }}</p>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
	emails_possibles : {},
    cacher_emails :[],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.recuperer_emails_possibles();
@endpush

@push('donnees_pour_vuejs_methods')

    recuperer_emails_possibles : function(){

        if(this.$root.cache_formulaires.docusign_signataire == undefined || this.$root.cache_formulaires.docusign_signataire.emails_disponibles == undefined){
            this.$set(this.$root.cache_formulaires,'docusign_signataire',{});
            this.$set(this.$root.cache_formulaires.docusign_signataire,'emails_disponibles',{});
        }

        var type_element = this.sous_formulaire ? this.docusign_enveloppe.type_element : this.$root.docusign_enveloppe.type_element;
        var id_element = this.sous_formulaire ? this.docusign_enveloppe.element_id : this.$root.docusign_enveloppe.element_id;

        if(this.$root.cache_formulaires.docusign_signataire.emails_disponibles[type_element+'/'+id_element] == undefined){

            $.post({
                url: '{{URL::to('/eden/element')}}/'+type_element+'/'+id_element+'/valeurs_champs_relies',
                dataType:'json',
                data : {
                    format_champ : 'email'
                }
            }).done((donnees) => {

                this.emails_possibles = donnees;

                this.$set(this.$root.cache_formulaires.docusign_signataire.emails_disponibles,type_element+'/'+id_element,donnees);
            });
        }
        else
            this.emails_possibles = this.$root.cache_formulaires.docusign_signataire.emails_disponibles[type_element+'/'+id_element];
    },

    affichage_emails : function(type_element){
        if(this.cacher_emails.includes(type_element))
            this.cacher_emails.splice(this.cacher_emails.indexOf(type_element),1);
        else
            this.cacher_emails.push(type_element);
    },
@endpush