@push('donnees_pour_vuejs_data')
    contact: {!!  modele_par_defaut('contact') !!},
    type_element_formulaire_parent: undefined,
    contacts_prioritaires: false,
    paiement_document: [],
@endpush

@push('donnees_pour_vuejs_methods')

    contact_creer: function() {

        $('#modal_ajout_contact').modal('show');
    
        // on réinitialise le contact
    
        this.contact = {!!  modele_par_defaut('contact') !!}
    
        if(this.document.client_id != null && this.document.client_id != undefined && this.document.client_id != '')
            this.contact.client_id = this.document.client_id;
    
        this.contact.id = '';
    },

    contact_enregistrer: function() {

        // On afficher le loader
        loading();
    
        var $this = this;
    
        // on enregistre les infos du champ libre
        $.post({
    
            url: "eden/element/contact/creer",
            dataType: "json",
            method: 'POST',
            data: $('#formulaire_ajout_contact').serialize()
        }).done(async function(donnees) {
    
            // On retire le loader
            loading(false);
        
            if(donnees.retour !== true) {
        
               await erreur(donnees.retour);
               return;
            }
        
            $('#modal_ajout_contact').modal('hide');
        
            vue_instance.client.contacts.push(donnees.element);
        });
    },
    rattache_paiement_client: function(paiement) {

        // On stock le paiement id pour l'enregistrer a l'enregistrement du document
        vue_instance.paiement_document.push(paiement.id);
        paiement.selectionne = true;
        vue_instance.$forceUpdate();
    },

    retirer_paiement_client: function(paiement) {

        // On supprime le paiement
        const index = vue_instance.paiement_document.indexOf(paiement.id);
        if (index > -1) {
            vue_instance.paiement_document.splice(index, 1);
        }
        paiement.selectionne = false;
        vue_instance.$forceUpdate();
    },
@endpush

@push('modales')

    <!-- Modal ajout contact -->
    <div class="modal fade" id="modal_ajout_contact" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestion des contacts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" name="id" v-model="contact.id" />

                <div class="modal-body">
                    <form action="#" id="formulaire_ajout_contact" method="post" class="css_form">

                        <input type="hidden" name="client_id" :value="contact.client_id" />
                        {!! formulaire('contact') !!}

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" @click="contact_definir_comme_prioritaire(1)" v-show="contact.id != '' && (contact.contact_prioritaire == 0 || contact.contact_prioritaire == null)">Contact prioritaire</button>
                    <button type="button" class="btn btn-warning" @click="contact_definir_comme_prioritaire(0)" v-show="contact.id != '' && contact.contact_prioritaire == 1">Annuler contact prioritaire</button>
                    <button type="button" class="btn btn-warning" @click="contact_npai(1)" v-show="contact.id != '' && (contact.npai == 0 || contact.npai == null)">NPAI</button>
                    <button type="button" class="btn btn-warning" @click="contact_npai(0)" v-show="contact.id != '' && contact.npai == 1">Annuler NPAI</button>
                    <button type="button" class="btn btn-primary" @click="contact_enregistrer">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('donnees_pour_vuejs_created')

    if(this.document.client_id != null) {

        this.recupere_info_client();
    }
@endpush

@push('donnees_pour_vuejs_data')
    exoneration_tva : false,

    @if($management->existe())
        client: {!! collect(management('client', $management->modele->client_id)->infos_client_pour_gestion_commerciale()) !!},
        @if(!empty($management->modele->client_id))
            derniere_info_recuperee_client: {{$management->modele->client_id}},
        @else
            derniere_info_recuperee_client: 0,
        @endif
        test1: 1,
    @elseif(!empty($management->modele) && !empty($management->modele->client_id))
        client: {!! collect(management('client', $management->modele->client_id)->infos_client_pour_gestion_commerciale()) !!},
        derniere_info_recuperee_client: {{$management->modele->client_id}},

        test2: 2,
    @else
        client: {!! collect(management('client')->infos_client_pour_gestion_commerciale()) !!},
        derniere_info_recuperee_client: 0,
        test3: 3,
    @endif

    // pour ne pas systématiquement récupérer les infos du client ou du fournisseur vu qu'il y a un wathch deep


@endpush

@push('donnees_pour_vuejs_methods')

    recupere_info_client: async function(modification = false) {

        await this.$nextTick();

        var client_id = this.document.client_id;
    
        if(client_id === null || client_id == undefined || client_id == 0 || client_id == '') {
    
            this.client = {!! collect(management('client')->infos_client_pour_gestion_commerciale()) !!};
            return;
        }
    
        $.get({
    
            url: "{{ URL::to('eden/document/vente/infos_client') }}/"+client_id,
            dataType: "json"
        }).done(async (donnees) => {

            // on renseigne le client
            this.client = donnees;
        
            if(this.$refs.selection_projet_document != undefined && this.$refs.selection_projet_document.projet != undefined)
                this.$refs.selection_projet_document.projet.client_id = client_id;

            @if(!$management->existe())
                // les modalités de paiement sur le document
                if(donnees.modele.modalite_paiement_id != undefined && donnees.modele.modalite_paiement_id != null && donnees.modele.modalite_paiement_id != "")
                    this.document.modalite_paiement_id = donnees.modele.modalite_paiement_id;
        
                // le compte bancaire par défaut
                if(donnees.modele.compte_bancaire_id != undefined && donnees.modele.compte_bancaire_id != null && donnees.modele.compte_bancaire_id != "")
                    this.document.compte_bancaire_id = donnees.modele.compte_bancaire_id;
        
                // le responsable commercial par défaut
                if(donnees.modele.responsable_commercial != undefined && donnees.modele.responsable_commercial != null && donnees.modele.responsable_commercial != "")
                    this.document.responsable_commercial_id = donnees.modele.responsable_commercial;
        
                // le modèle de document par défaut
                if(donnees.modele.modele_document_defaut_{{ $management->_type_element }} != undefined && donnees.modele.modele_document_defaut_{{ $management->_type_element }} != null && donnees.modele.modele_document_defaut_{{ $management->_type_element }} != "")
                    this.document.type_modele_document = donnees.modele.modele_document_defaut_{{ $management->_type_element }};
        
                @foreach(App\Eden\Models\Champ_libre::where('type_element', $management->_type_element)->where('correspondance_fiche_tiers', '!=', '')->get() as $champ_libre)

                    if(donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != undefined && donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != null && donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != "") {

                        this.document.{{$champ_libre->nom_sql}} = donnees.modele.{{$champ_libre->correspondance_fiche_tiers}};
                    }
                    else{
                        @if(in_array($champ_libre->type, array(10,11,12)))
                            this.document.{{$champ_libre->nom_sql}} = [];
                        @else
                            this.document.{{$champ_libre->nom_sql}} = null;
                        @endif
                    }

                @endforeach
            @endif

            if(!this.document.adresse_de_facturation > 0 &&
                (this.document.adresse_de_facturation_texte == null || this.document.adresse_de_facturation_texte == '')){
                // s'il y a une seule adresse de facturation sur le client, alors on la sélectionne par défaut
                if(donnees.adresses_facturation.length == 1)
                    this.document.adresse_de_facturation = donnees.adresses_facturation[0].id;
                else{
                    this.document.adresse_de_facturation = null;

                    // On parcourt les adresse et si une adresse est en adresse par défaut, on sélectionne la première
                    for (var index = 0; index < donnees.adresses_facturation.length; index++) {

                        // On sort du for si on a une adresse par défaut
                        if(donnees.adresses_facturation[index].adresse_par_defaut == 1){
                            this.document.adresse_de_facturation = donnees.adresses_facturation[index].id;
                            index = donnees.adresses_facturation.length + 10;
                        }
                    }
                }
            }

            if(!this.document.adresse_de_livraison > 0 &&
                (this.document.adresse_de_livraison_texte == null || this.document.adresse_de_livraison_texte == '')){

                // s'il y a une seule adresse de livraison sur le client, alors on la sélectionne par défaut
                if(donnees.adresses_livraison.length == 1)
                    this.document.adresse_de_livraison = donnees.adresses_livraison[0].id;
                else{

                    this.document.adresse_de_livraison = null;

                    // On parcourt les adresse et si une adresse est en adresse par défaut, on sélectionne la première
                    for (var index = 0; index < donnees.adresses_livraison.length; index++) {

                        // On sort du for si on a une adresse par défaut
                        if(donnees.adresses_livraison[index].adresse_par_defaut == 1){
                            this.document.adresse_de_livraison = donnees.adresses_livraison[index].id;
                            index = donnees.adresses_livraison.length + 10;
                        }
                    }
                }
            }

            if(modification){

                this.$set(this.document,'contacts_ids',[]);

                await this.mise_a_jour_conditions_commerciales();

                this.gestion_categorie_comptable(donnees.modele.categorie_comptable_id);
            }
        });
    },

    ajoute_contact_au_document: function(contact) {

        var trouve = false;
    
        this.document.contacts_ids.forEach(function(contact_du_document, index) {
        
            if(contact_du_document == contact.id) {
        
                vue_instance.document.contacts_ids.splice(index, 1);
                trouve = true;
            }
    
        });
    
        if(trouve === false) {
    
            this.document.contacts_ids.push(contact.id);
        }
    },
@endpush