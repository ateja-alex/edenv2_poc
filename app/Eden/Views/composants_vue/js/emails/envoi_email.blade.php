<script>
    const envoi_email = Vue.component('envoi-email', {
        template: ` <div>
                      <template v-if="modale_email">
                        <transition name="modal">
                          <div class="modal-mask">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header ">
                                        <h5 style="display: flex;justify-content: space-between;width: 100%;">
                                            @traduction('interface.modal_email.titre')
                                            <div>
                                                <span @click="envoyer_email()">
                                                    <i class="css_action_icon fas fa-paper-plane"></i>
                                                </span>
                                                <span type="button" class="close" @click="modale_email = false" aria-label="Close">
                                                    <span aria-hidden="true"><i style="width: 30px;height: 30px;line-height: 30px;" class="fas fa-times "></i></span>
                                                </span>
                                            </div>
                                        </h5>
                                    </div>
                                    <div class="modal-body css_form" style="max-height: calc(100vh - 175px);overflow-y: auto;">

                                        <div class="row">
                                            <div class="col-sm-2">
                                                <b>@traduction('interface.modal_email.champs.expediteur')</b>
                                            </div>
                                            <div class="col-sm-4 d-flex align-items-center">
                                                <select name="expediteur" v-model="informations_mails.compte_email" @change="changement_compte_email()">
                                                    <option v-for="compte_email in comptes_emails" :value="compte_email.id">@{{compte_email.chaine_affichage}}</option>
                                                </select>
                                                <bouton-creation-pour-champ 
                                                    type_element_ajax="compte_email" 
                                                    @enregistrement="ajout_compte_email($event)"
                                                    :valeurs_par_defaut="creation_compte_email_microsoft">
                                                </bouton-creation-pour-champ>
                                            </div>
                                            <template v-if="aliases.length > 0">
                                                <div class="col-sm-2">
                                                    <b>@traduction('interface.modal_email.champs.alias')</b>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select name="alias_email" v-model="informations_mails.alias_email">
                                                        <option :value="null" v-html="$root.traduction('interface.modal_email.champs.alias.sans_alias')"></option>
                                                        <option v-for="alias in aliases" :value="alias">@{{alias}}</option>
                                                    </select>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="row" v-if="modeles_emails.length > 0">
                                            <div class="col-sm-2">
                                                <b>@traduction('interface.modal_email.champs.modele_email')</b>
                                            </div>
                                            <div class="col-sm-4">
                                                <select name="modele_email" v-model="informations_mails.modele_email" @change="changement_modele_email">
                                                    <option :value="null"></option>
                                                    <template v-for="(modeles_emails_langues,langue) in Object.groupBy(modeles_emails,(modele) => modele.affichage_langue)">
                                                        <optgroup :label="langue"></optgroup>
                                                        <optgroup v-for="(modeles_emails,categorie) in Object.groupBy(modeles_emails_langues,(modele) => modele.affichage_categorie)" :label="'   '+categorie">
                                                            <option v-for="info in modeles_emails" :value="info.id">@{{ info.nom }} (@{{ info.sujet_modele }})</option>
                                                        </optgroup>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" v-if="elements.length > 1">
                                            <div class="col-sm-2">
                                                <b>@traduction('interface.modal_email.champs.regroupement')</b>
                                            </div>
                                            <div class="col-sm-4">
                                                <select name="regroupement" v-model="regroupement" @change="changement_regroupement()">
                                                    <option :value="null">Aucun regroupement</option>
                                                    <option value="1">Tout regrouper</option>
                                                    <option v-for="champ in champs_regroupements" :value="champ.nom_sql" 
                                                        v-html="'Par '+$root.traduction(champ.index_traduction+'.nom')+' ('+champ.nom_sql+')'"></option>
                                                </select>
                                            </div>
                                        </div>

                                        <template v-if="informations_mails.mails.length > 1">
                                            <div class="row" v-for="type_destinataire in 3">
                                                <div class="col-sm-2"><b v-html="$root.traduction('interface.modal_email.champs.destinataires_globaux.' + type_destinataire)"></b></div>
                                                <div class="col-sm-10">
                                                    <selection-email :type_element="type_element" 
                                                        :modele="informations_mails.destinataires[type_destinataire]"
                                                        @maj_modele="changement_destinataires_globaux(type_destinataire,$event)"
                                                        :type_destinataire="type_destinataire"
                                                        :parametres_destinataires="parametres_destinataires"
                                                        :defaut="informations_mails.destinataires_defaut[type_destinataire]"
                                                        :couleurs_groupes="couleurs_groupes.destinataire"
                                                        ></selection-email>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    <b>@traduction('interface.modal_email.champs.pieces_jointes')</b>
                                                </div>
                                                <div class="col-sm-10">
                                                    <selection-piece-jointe
                                                        @maj_modele="changement_pieces_jointes_globales($event)" 
                                                        :type_element="type_element" 
                                                        :modele="informations_mails.pieces_jointes"
                                                        :defaut="informations_mails.pieces_jointes_defaut"
                                                        :parametres_destinataires="parametres_destinataires"
                                                        :couleurs_groupes="couleurs_groupes.piece_jointe"
                                                    ></selection-piece-jointe>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-2"><b>@traduction('interface.modal_email.champs.sujet_email')</b></div>
                                                <div class="col-sm-10">
                                                    <champ-publipostage :type_element="type_element"
                                                        :modele="informations_mails" nom_sql="sujet" @change="gestion_publipostage"></champ-publipostage>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><b>@traduction('interface.modal_email.champs.message')</b> </div>
                                                <div class="col-sm-12">
                                                    <champ-publipostage :type_element="type_element"
                                                        :modele="informations_mails" nom_sql="contenu" type_champ="textarea-wysiwyg-vue" @change="gestion_publipostage"></champ-publipostage>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="row mt-2" v-if="informations_mails.mails.length > 1">
                                            <div class="col-sm-12 css_form_ligne_titre" style="display: flex;justify-content: space-between;align-items: center;padding: 5px 20px;">
                                                @traduction('interface.modal_email.mails_a_envoyer')
                                                <i :class="'css_pointer fas fa-chevron-'+(affichage_contenu_global ? 'up' : 'down')" @click="changement_affichage_contenu_global"></i>
                                            </div>
                                        </div>

                                        <template v-for="(mail,index_mail) in informations_mails.mails">
                                            <div class="row mt-2" v-if="informations_mails.mails.length > 1">
                                                <div class="col-sm-12 css_form_ligne_titre" style="display: flex;justify-content: space-between;align-items: center;padding: 5px 20px;">
                                                    <span v-html="affichage_regroupement
                                                        +' : '
                                                        +(mail.cle_groupement ?? mail.elements[0]).chaine_affichage"></span>
                                                    <i :class="'css_pointer fas fa-chevron-'+(mail.affichage_contenu ? 'up' : 'down')" @click="mail.affichage_contenu = !mail.affichage_contenu"></i>
                                                </div>
                                            </div>

                                            <template v-if="informations_mails.mails.length == 1 || mail.affichage_contenu">

                                                <div class="row" v-for="type_destinataire in 3">
                                                    <div class="col-sm-2"><b v-html="$root.traduction('interface.modal_email.champs.destinataires.' + type_destinataire)"></b></div>
                                                    <div class="col-sm-10">
                                                        <selection-email 
                                                            :type_destinataire="type_destinataire"
                                                            @maj_modele="mail.destinataires[type_destinataire] = $event.mails" 
                                                            :type_element="type_element" :elements="mail.elements" 
                                                            :modele="mail.destinataires[type_destinataire]"
                                                            :defaut="mail.destinataires_defaut[type_destinataire]"
                                                            :parametres_destinataires="parametres_destinataires"
                                                            :couleurs_groupes="couleurs_groupes.destinataire"
                                                            ></selection-email>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-2">
                                                        <b>@traduction('interface.modal_email.champs.pieces_jointes')</b>
                                                    </div>
                                                    <div class="col-sm-10">
                                                        <selection-piece-jointe
                                                            @maj_modele="mail.pieces_jointes = $event.pieces_jointes" 
                                                            :type_element="type_element" :elements="mail.elements" 
                                                            :modele="mail.pieces_jointes"
                                                            :defaut="mail.pieces_jointes_defaut"
                                                            :parametres_destinataires="parametres_destinataires"
                                                            :couleurs_groupes="couleurs_groupes.piece_jointe"
                                                        ></selection-piece-jointe>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-2"><b>@traduction('interface.modal_email.champs.sujet_email')</b></div>
                                                    <div class="col-sm-10">
                                                        <champ-publipostage :type_element="type_element" :elements="mail.elements" 
                                                            :modele="mail" nom_sql="sujet"></champ-publipostage>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12"><b>@traduction('interface.modal_email.champs.message')</b> </div>
                                                    <div class="col-sm-12">
                                                        <champ-publipostage :type_element="type_element" :elements="mail.elements" 
                                                            :modele="mail" nom_sql="contenu" type_champ="textarea-wysiwyg-vue"></champ-publipostage>
                                                    </div>
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                  </div>
                                </div>
                            </div>
                        </transition>
                    </template>
                </div>

        `,
        data: function(){

            var structure_email = {
                destinataires : {
                    1 : [],
                    2 : [],
                    3 : []
                },
                destinataires_defaut : {
                    1 : [],
                    2 : [],
                    3 : []
                },
                sujet : '',
                contenu : '',
                pieces_jointes : [],
                pieces_jointes_defaut : [],
                elements : [],
                valeurs_publipostes : {},
                affichage_contenu : false,
            };

            var informations_mails = {
                compte_email : null,
                alias_email : null,
                modele_email : null,
                sujet : '',
                contenu : '',
                destinataires : {
                    1 : [],
                    2 : [],
                    3 : []
                },
                destinataires_defaut : {
                    1 : [],
                    2 : [],
                    3 : []
                },
                pieces_jointes : [],
                pieces_jointes_defaut : [],
                valeurs_publipostes : [],
                template_email : 'template_standard',
                enregistrer_echange : false,
                mails : [structure_email],
            };

            return {
                modale_email : false,

                type_element : null,
                elements: [],

                champs_libres : [],

                regroupement : 1,

                comptes_emails : [],
                aliases: [],
                modeles_emails : [],

                informations_mails_modele : informations_mails,
                structure_email : structure_email,
                informations_mails : informations_mails,

                couleurs : {
                    'destinataire' : [
                        'rgb(0 123 255 / 20%)',
                        'rgb(40 167 69 / 20%)',
                        'rgb(255 140 0 / 20%)',
                        'rgb(153 102 255 / 20%)',
                        'rgb(255 105 180 / 20%)',
                        'rgb(23 162 184 / 20%)',
                        'rgb(255 193 7 / 20%)',
                        'rgb(102 16 242 / 20%)',
                        'rgb(32 201 151 / 20%)',
                        'rgb(255 127 80 / 20%)'
                    ],
                    'piece_jointe' : [
                        'rgb(139 0 0 / 70%)', 
                        'rgb(25 25 112 / 70%)',
                        'rgb(85 107 47 / 70%)',
                        'rgb(72 61 139 / 70%)',
                        'rgb(143 34 178 / 70%)',
                        'rgb(0 100 95 / 70%)',
                        'rgb(184 134 11 / 70%)',
                        'rgb(128 71 0 / 70%);',
                        'rgb(128 0 83 / 70%)',
                        'rgb(39 43 40 / 70%)' 
                    ]
                },
                couleurs_groupes : {},
            }
        },

        methods:{

            initialisation_email : function(parametres_email){

                this.aliases = [];

                this.informations_mails = structuredClone(this.informations_mails_modele);
                this.regroupement = 1;

                $.post({

                    url: 'eden/email/initialisation',
                    data: parametres_email,
                    dataType:'json'
                }).done(async (retour) => {

                    if(retour.erreur === true) {
                        erreur(retour.message);
                        return;
                    }

                    this.modale_email = true;

                    this.comptes_emails = retour.comptes_emails;
                    this.modeles_emails = retour.modeles_emails;
                    this.type_element = retour.type_element;
                    this.elements = retour.elements;
                    this.champs_libres = retour.champs_libres;

                    if(this.comptes_emails.length > 0){
                        this.informations_mails.compte_email = this.comptes_emails[0].id;
                        this.changement_compte_email();
                    }

                    var modele_email_par_defaut = this.modeles_emails.filter((modele) => modele.par_defaut == 1)[0] ?? null;

                    if(parametres_email.modele_email > 0){
                        this.informations_mails.modele_email = parametres_email.modele_email;
                        this.changement_modele_email();
                    }
                    else if(modele_email_par_defaut){
                        this.informations_mails.modele_email = modele_email_par_defaut.id;
                        this.changement_modele_email();
                    }
                    else{
                        this.regroupement = this.champs_regroupements.length == 1 ? this.champs_regroupements[0].nom_sql : null;
                        await this.changement_regroupement();
                    }
                });
            },

            changement_modele_email : function(){

                var modele_email = this.modeles_emails.filter((modele) => modele.id == this.informations_mails.modele_email)[0] ?? null;

                this.informations_mails.sujet = modele_email?.sujet_modele ?? '';
                this.informations_mails.contenu = modele_email?.modele ?? '';
                this.informations_mails.template_email = modele_email && modele_email.utilisation_template_vide ? 'template_vide' : 'template_standard';
                this.informations_mails.enregistrer_echange = modele_email?.enregistrer_echange ?? false;

                if(!this.informations_mails.mails.length == 1 || this.regroupement == (modele_email?.regroupement_multiple ?? null)){
                    this.chargement_destinataire();
                    this.chargement_piece_jointe();
                    this.gestion_publipostage();
                }
                else if(modele_email && (this.champs_regroupements.filter((champ) => champ.nom_sql == modele_email.regroupement_multiple).length > 0
                    || modele_email.regroupement_multiple == 1 || modele_email.regroupement_multiple == null)){
                    this.regroupement = modele_email.regroupement_multiple;
                    this.changement_regroupement();
                }
                else{
                    this.regroupement = null;
                    this.changement_regroupement();
                }
            },

            changement_compte_email : function(){

                this.informations_mails.alias_email = null;

                var compte_email = this.comptes_emails.filter((compte_email) => compte_email.id == this.informations_mails.compte_email)[0] ?? null;

                if(compte_email == null){
                    this.aliases = [];
                    return;
                }

                this.aliases = compte_email.aliases ?? [];

                if(compte_email.type_de_compte == 1){
                    $.ajax({
                        url: 'eden/microsoft/alias_email/'+compte_email.utilisateur_id,
                        dataType:'json',
                    }).done((alias) => {
                        this.aliases = this.aliases.concat(alias);
                    });
                }
            },

            changement_regroupement : async function(){

                var mails = [];

                if(this.regroupement == null){
                    for(element of this.elements){
                        var structure_email = structuredClone(this.structure_email);
                        structure_email.elements = [element];
                        mails.push(structure_email);
                    }
                }
                else if(this.regroupement == 1){
                    var structure_email = structuredClone(this.structure_email);
                    structure_email.elements = this.elements;
                    mails.push(structure_email);
                }
                else{
                    var elements_regroupes = Object.groupBy(this.elements,(element) => element[this.regroupement]);

                    var modeles_cle_groupement = await $.post({
                        url : 'eden/elements/'+this.champs_libres.filter((champ) => champ.nom_sql == this.regroupement)[0].type_element_ajax,
                        data:{
                            filtrage:[
                                {
                                    champ : 'id',
                                    condition : 'whereIn',
                                    valeur : Object.keys(elements_regroupes),
                                },
                            ]
                        },
                        dataType : 'json',
                    });

                    for(cle_groupe in elements_regroupes){
                        var structure_email = structuredClone(this.structure_email);
                        structure_email.elements = elements_regroupes[cle_groupe];
                        structure_email.cle_groupement = modeles_cle_groupement.filter((modele) => modele.id == cle_groupe)[0] ?? null;
                        mails.push(structure_email);
                    }
                }

                this.informations_mails.mails = mails;
                this.chargement_destinataire();
                this.chargement_piece_jointe();
                this.gestion_publipostage(true);
            },

            chargement_destinataire : function(){

                $.post({
                    url : 'eden/email/chargement/destinataires',
                    dataType : 'json',
                    data : {
                        groupes_ids : this.informations_mails.mails.map((mail) => mail.elements.map((element) => element.id)),
                        parametres : {
                            niveau : [2,3],
                            ...this.parametres_destinataires,
                        }
                    },
                }).done((data) => {

                    this.informations_mails.destinataires_defaut = structuredClone(data.valeurs_globales);
                    this.informations_mails.destinataires = structuredClone(data.valeurs_globales);

                    for(groupe_id in this.informations_mails.mails){
                        for(type_destinataire in data.valeurs[groupe_id]){
                            this.informations_mails.mails[groupe_id].destinataires_defaut[type_destinataire] = structuredClone(data.valeurs[groupe_id][type_destinataire]);
                            this.informations_mails.mails[groupe_id].destinataires[type_destinataire] = structuredClone(this.informations_mails.mails[groupe_id].destinataires_defaut[type_destinataire]);
                        }
                    }

                    this.calcul_couleurs_groupes('destinataire');
                });
            },

            chargement_piece_jointe : function(){

                $.post({
                    url : 'eden/email/chargement/piece_jointe',
                    dataType : 'json',
                    data : {
                        groupes_ids : this.informations_mails.mails.map((mail) => mail.elements.map((element) => element.id)),
                        parametres : {
                            niveau : [2,3],
                            ...this.parametres_destinataires,
                        }
                    },
                }).done((data) => {

                    this.informations_mails.pieces_jointes_defaut = structuredClone(data.valeurs_globales);
                    this.informations_mails.pieces_jointes = structuredClone(data.valeurs_globales);

                    for(groupe_id in this.informations_mails.mails){
                        this.informations_mails.mails[groupe_id].pieces_jointes_defaut = structuredClone(data.valeurs[groupe_id]);
                        this.informations_mails.mails[groupe_id].pieces_jointes = structuredClone(this.informations_mails.mails[groupe_id].pieces_jointes_defaut);
                    }

                    this.calcul_couleurs_groupes('piece_jointe');
                });
            },

            calcul_couleurs_groupes : function(type){

                this.$set(this.couleurs_groupes,type,{});

                if(this.informations_mails.mails.length == 1)
                    return;

                var couleurs = this.couleurs[type];

                var index_couleur = 0;

                if(type == 'destinataire'){

                    for(type_destinataire in this.informations_mails.destinataires){

                        for(mail of this.informations_mails.destinataires[type_destinataire]){

                            if(index_couleur >= couleurs.length){
                                var couleur = null;

                                while(couleur == null || Object.values(this.couleurs_groupes[type]).includes(couleur))
                                    couleur = `rgb(${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} / 20%)`;
                            }
                            else
                                var couleur = couleurs[index_couleur];

                            this.$set(this.couleurs_groupes[type], type_destinataire+'_'+mail.groupe_id, couleur);
                            index_couleur++;
                        }
                    }
                }
                else if(type == 'piece_jointe'){
                    for(piece_jointe of this.informations_mails.pieces_jointes){

                        if(index_couleur >= couleurs.length){
                            var couleur = null;

                            while(couleur == null || Object.values(this.couleurs_groupes[type]).includes(couleur))
                                couleur = `rgb(${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} / 70%)`;
                        }
                        else
                            var couleur = couleurs[index_couleur];

                        this.$set(this.couleurs_groupes[type], piece_jointe.groupe_id, couleur);
                        index_couleur++;
                    }
                }
            },

            changement_destinataires_globaux : async function(type_destinataire, parametres){

                var mails_destinataires = parametres.mails;

                var suppressions = this.informations_mails.destinataires[type_destinataire].filter((mail) => !mails_destinataires.includes(mail));
                var ajouts = mails_destinataires.filter((mail) => !this.informations_mails.destinataires[type_destinataire].includes(mail));

                this.informations_mails.destinataires[type_destinataire] = mails_destinataires;

                for(suppression of suppressions){
                    for(mail of this.informations_mails.mails){
                        mail.destinataires[type_destinataire] = mail.destinataires[type_destinataire].filter((mail_destinataire) => mail_destinataire.groupe_id != suppression.groupe_id);
                        this.$delete(this.couleurs_groupes.destinataire, suppression.groupe_id);
                    }
                }

                if(ajouts.length == 0)
                    return;

                var mails_a_ajouter = [];
                var mails_a_ajouter_globaux = [];

                for(ajout of ajouts){

                    if(ajout.groupe_id == null){

                        var valeurs_negatives = Object.values(this.informations_mails.destinataires).flat().map(d => d.groupe_id).filter(d => d < 0);

                        ajout.groupe_id = valeurs_negatives.length > 0 ? Math.min(...valeurs_negatives) - 1 : -1;

                        mails_a_ajouter_globaux.push({
                            valeur : ajout.valeur,
                            groupe_id : ajout.groupe_id,
                        });
                    }
                    else{
                        mails_a_ajouter = (await $.post({
                            url : 'eden/email/chargement/destinataires',
                            data : {
                                groupes_ids : this.informations_mails.mails.map((mail) => mail.elements.map((element) => element.id)),
                                parametres : {
                                    id : ajout.groupe_id
                                }
                            },
                            dataType : 'json',
                        })).valeurs;
                    }

                    var couleur = this.couleurs.destinataire.filter(c => !Object.values(this.couleurs_groupes.destinataire).includes(c))[0] ?? null;

                    while(couleur == null || Object.values(this.couleurs_groupes.destinataire).includes(couleur))
                        couleur = `rgb(${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} / 20%)`;

                    this.$set(this.couleurs_groupes.destinataire, type_destinataire+'_'+ajout.groupe_id, couleur);
                    
                    for(index_mail in this.informations_mails.mails){

                        var mail = this.informations_mails.mails[index_mail];

                        var position = parametres.position;

                        var destinataires_presents = this.informations_mails.destinataires[type_destinataire]
                            .filter((mail_destinataire, index_destinataire) => index_destinataire < position)
                            .map(d => d.groupe_id);

                        var compte_destinataires = destinataires_presents.map(d => mail.destinataires[type_destinataire].filter((mail_destinataire) => mail_destinataire.groupe_id == d).length);
                        
                        compte_destinataires = compte_destinataires.reduce((a,b) => a+b, 0) - destinataires_presents.length;

                        position += compte_destinataires;

                        for(var i = 0; i < position; i++){
                            if(mail.destinataires[type_destinataire][i] && !destinataires_presents.includes(mail.destinataires[type_destinataire][i].groupe_id)){
                                position++;
                            }
                        }

                        var mails_ajout = mails_a_ajouter_globaux.concat(mails_a_ajouter[index_mail]?.[0] ?? []);

                        mails_ajout = mails_ajout.filter(m => !mail.destinataires[type_destinataire].map(d => d.valeur).includes(m.valeur));

                        if(mails_ajout.length > 0)
                            mail.destinataires[type_destinataire].splice(position, 0,
                                ...mails_ajout
                            );
                    }
                }
            },

            changement_pieces_jointes_globales : async function(parametres){

                var pieces_jointes = parametres.pieces_jointes;

                var suppressions = this.informations_mails.pieces_jointes.filter((piece_jointe) => !pieces_jointes.includes(piece_jointe));
                var ajouts = pieces_jointes.filter((piece_jointe) => !this.informations_mails.pieces_jointes.includes(piece_jointe));

                this.informations_mails.pieces_jointes = pieces_jointes;

                for(suppression of suppressions){
                    for(mail of this.informations_mails.mails){
                        mail.pieces_jointes = mail.pieces_jointes.filter((mail_piece_jointe) => mail_piece_jointe.groupe_id != suppression.groupe_id);
                        this.$delete(this.couleurs_groupes.piece_jointe, suppression.groupe_id);
                    }
                }

                if(ajouts.length == 0)
                    return;

                var pieces_jointes_a_ajouter = [];
                var pieces_jointes_a_ajouter_globaux = [];

                for(ajout of ajouts){

                    if(ajout.groupe_id == null){

                        var valeurs_negatives = Object.values(this.informations_mails.pieces_jointes).flat().map(d => d.groupe_id).filter(d => d < 0);

                        ajout.groupe_id = valeurs_negatives.length > 0 ? Math.min(...valeurs_negatives) - 1 : -1;

                        pieces_jointes_a_ajouter_globaux.push({
                            valeur : ajout.valeur,
                            affichage: ajout.affichage,
                            groupe_id : ajout.groupe_id,
                        });
                    }
                    else{
                        pieces_jointes_a_ajouter = (await $.post({
                            url : 'eden/email/chargement/pieces_jointes',
                            data : {
                                groupes_ids : this.informations_mails.mails.map((mail) => mail.elements.map((element) => element.id)),
                                parametres : {
                                    id : ajout.groupe_id
                                }
                            },
                            dataType : 'json',
                        })).valeurs;
                    }

                    var couleur = this.couleurs.piece_jointe.filter(c => !Object.values(this.couleurs_groupes.piece_jointe).includes(c))[0] ?? null;

                    while(couleur == null || Object.values(this.couleurs_groupes.piece_jointe).includes(couleur))
                        couleur = `rgb(${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} ${Math.floor(Math.random()*256)} / 70%)`;

                    this.$set(this.couleurs_groupes.piece_jointe, ajout.groupe_id, couleur);
                    
                    for(index_mail in this.informations_mails.mails){

                        var mail = this.informations_mails.mails[index_mail];

                        var pieces_jointes_ajout = pieces_jointes_a_ajouter_globaux.concat(pieces_jointes_a_ajouter[index_mail] ?? []);

                        pieces_jointes_ajout = pieces_jointes_ajout.filter(m => !mail.pieces_jointes.map(p => p.valeur).includes(m.valeur));

                        if(pieces_jointes_ajout.length > 0)
                            mail.pieces_jointes = mail.pieces_jointes.concat(pieces_jointes_ajout);
                    }
                }
            },

            gestion_publipostage : async function(forcer_rechargement = false){

                if(forcer_rechargement){
                    this.informations_mails.valeurs_publipostes = [];
                    for(groupe_id in this.informations_mails.mails){
                        this.informations_mails.mails[groupe_id].valeurs_publipostes = {};
                    }
                }

                var valeurs_a_publiposter_par_type = {};

                for(type of ['sujet','contenu']){

                    var regex = /\{\{\s*([a-zA-Z0-9_\s\[\]\#\/]+)\s*\}\}/g;
                    var matches = [];
                    let match;
                    while ((match = regex.exec(this.informations_mails[type])) !== null) {
                        matches.push(match[1]);
                    }

                    valeurs_a_publiposter_par_type[type] = matches.filter((valeur, index) => matches.indexOf(valeur) === index);
                }

                var valeurs_a_publiposter = Object.values(valeurs_a_publiposter_par_type).flat();

                if(valeurs_a_publiposter.length == 0){
                    for(groupe_id in this.informations_mails.mails){
                        this.informations_mails.mails[groupe_id].sujet = this.informations_mails.sujet;
                        this.informations_mails.mails[groupe_id].contenu = this.informations_mails.contenu;
                    }
                    return;
                }

                var valeurs_a_publiposter_manquantes = valeurs_a_publiposter.filter(x => !this.informations_mails.valeurs_publipostes.includes(x));

                if(valeurs_a_publiposter_manquantes.length > 0){
                    var valeurs_publipostes_manquantes = (await $.post({
                        url : 'eden/email/gestion_publipostage',
                        dataType : 'json',
                        data : {
                            type_element : this.type_element,
                            groupes_ids : this.informations_mails.mails.map((mail) => mail.elements.map((element) => element.id)),
                            valeurs_a_publiposter: valeurs_a_publiposter_manquantes,
                        },
                    })).valeurs_publipostes ?? {};

                    for(groupe_id in this.informations_mails.mails){
                        Object.assign(this.informations_mails.mails[groupe_id].valeurs_publipostes, valeurs_publipostes_manquantes[groupe_id]);
                    }

                    this.informations_mails.valeurs_publipostes = this.informations_mails.valeurs_publipostes.concat(valeurs_a_publiposter_manquantes);
                }

                for(groupe_id in this.informations_mails.mails){

                    var mail = this.informations_mails.mails[groupe_id];

                    for(type of ['sujet','contenu']){

                        var texte = this.informations_mails[type]; 
                        
                        for(valeur of valeurs_a_publiposter_par_type[type]){
                            var valeur_regex = valeur.replaceAll('[', '\\[').replaceAll(']', '\\]');
                            const regex = new RegExp(`\\{\\{\\s*${valeur_regex}\\s*\\}\\}`, 'g');
                            texte = texte.replaceAll(regex, mail.valeurs_publipostes[valeur]);
                        }

                        mail[type] = texte;
                    }
                }
            },

            changement_affichage_contenu_global : function(){

                var affichage_contenu_global = this.affichage_contenu_global;

                this.informations_mails.mails.map(m => m.affichage_contenu = !affichage_contenu_global)
            },

            envoyer_email : function(){

                loading(true);

                $.post({
                    url : 'eden/email/envoyer',
                    dataType : 'json',
                    data : {
                        expediteur : {
                            compte_email : this.informations_mails.compte_email,
                            alias_email : this.informations_mails.alias_email,
                        },
                        type_element : this.type_element,
                        mails : this.mails_pour_envoi(),
                        template_email : this.informations_mails.template_email,
                        enregistrer_echange : this.informations_mails.enregistrer_echange,
                    }
                }).done((retour) => {

                    loading(false);

                    if(retour.success !== true) {
                        erreur(retour.message);
                        return;
                    }

                    toastr.success(retour.message);
                    this.modale_email = false;
                });
            },

            mails_pour_envoi : function(){
                return this.informations_mails.mails.map((mail) => {

                    var mail_a_envoyer = structuredClone(mail);

                    delete mail_a_envoyer.destinataires_defaut;
                    delete mail_a_envoyer.pieces_jointes_defaut;
                    delete mail_a_envoyer.valeurs_publipostes;
                    delete mail_a_envoyer.affichage_contenu;

                    mail_a_envoyer.elements = mail_a_envoyer.elements.map(element => element.id);

                    for(type_destinataire in mail_a_envoyer.destinataires){
                        mail_a_envoyer.destinataires[type_destinataire] = mail_a_envoyer.destinataires[type_destinataire].map(d => d.valeur);
                    }

                    return mail_a_envoyer;
                });
            },

            ajout_compte_email : function(compte_email){

                if(compte_email.utilisateur_id != this.$root.moi.id)
                    return;

                this.comptes_emails.push(compte_email);

                this.informations_mails.compte_email = compte_email.id;
                this.changement_compte_email();

                var compte_microsoft_factice = this.comptes_emails.find(c => c.id == -1);

                if(compte_email.type_de_compte == 1 && compte_microsoft_factice)
                    this.comptes_emails.splice(this.comptes_emails.indexOf(compte_microsoft_factice), 1);
            },
        },

        computed:{

            champs_regroupements : function(){
                
                return this.champs_libres.filter((champ) => {

                    if(champ.type != 42)
                        return false;

                    if(this.elements.filter((element) => element[champ.nom_sql] == null).length > 0)
                        return false;

                    var valeurs = Object.values(Object.groupBy(this.elements,(element) => element[champ.nom_sql]));
                    return valeurs.length < this.elements.length && valeurs.length > 1; 
                });
            },

            affichage_regroupement : function(){

                var champ_regroupement = this.champs_regroupements.filter((champ) => champ.nom_sql == this.regroupement)[0] ?? null;

                if(champ_regroupement == null)
                    return this.$root.traduction('tables_libres.'+this.type_element+'.nom_table');

                return this.$root.traduction(champ_regroupement.index_traduction+'.nom');
            },

            parametres_destinataires : function(){

                var parametres = {};

                if(this.informations_mails.modele_email > 0)
                    parametres.modele_email_id = this.informations_mails.modele_email;
                else
                    parametres.type_element = this.type_element;

                return parametres;
            },

            affichage_contenu_global : function(){
                return this.informations_mails.mails.filter(m => m.affichage_contenu).length == this.informations_mails.mails.length;
            },

            creation_compte_email_microsoft : function(){
                return {
                    type_de_compte : this.$root.moi.id_microsoft != null ? 1 : 0,
                    utilisateur_id : this.$root.moi.id
                }
            },
        
        },

        mounted: function() {

            this.$root.$on('envoie_email',(parametres) => {
                this.initialisation_email(parametres);
            });

        },
    });
</script>
