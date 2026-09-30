@extends('eden::intranet.modules.base_module')

@section('footer_boutons_'.$id)
    <span class="footer_boutons">
        <template v-if="etape_note_de_frais=='articles'">
            <div class="back" @click="etape_note_de_frais='informations'">
                <span class="glyphicon glyphicon-chevron-left"></span>
                <b>@traduction('interface.intranet.retour')</b>
            </div>
            <div class="back" @click="enregistrer_note_de_frais('{{$id}}')">
                <span class="glyphicon glyphicon-floppy-disk"></span>
                <b>@traduction('interface.intranet.enregistrer')</b>
            </div>
        </template>
        <div class="back" v-else @click="etape_note_de_frais='articles'">
            <span class="glyphicon glyphicon-chevron-right"></span>
            <b>@traduction('interface.intranet.suivant')</b>
        </div>
    </div>
@endsection

@section('options_nom_module_'.$id)
    <i @click="ajouter_article_note_de_frais" v-if="etape_note_de_frais == 'articles'" class="css_action_icon fa fa-fw fa-plus-square"></i>
@endsection

@section('contenu_'.$id)

    <form class="css_form formulaire_intranet" id="formulaire_{{$id}}" onsubmit="return false;" >
        <input type="hidden" name="ajout_intranet" value="true">
        <input type="hidden" name="{{$champ_utilisateur}}" value="{{moi()->id}}">

        <div class="scrollbar_intranet bloc_note_de_frais" v-show="etape_note_de_frais=='informations'">

                <dl class="dl-horizontal">
                    <dt><b>{!! management($type_element)->champ($champ_utilisateur)->nom_vue()!!}</b></dt>
                    <dd class="coupee">
                        <p style="color:white;font-size: 16px;line-height: 35px;">
                            {!! management($type_element)->champ($champ_utilisateur)->affiche(moi()->id)!!}
                        </p>
                    </dd>
                </dl>

                <formulaire ref="formulaire_{{$id}}" :form="false" nom_formulaire="intranet_{{$type_element}}"></formulaire>

                <input type="hidden" v-model="note_de_frais.montant_ht" name="montant_ht">
                <input type="hidden" v-model="note_de_frais.total_tva" name="total_tva">
                <input type="hidden" v-model="note_de_frais.montant_ttc" name="montant_ttc">
                <input type="hidden" v-model="note_de_frais.heure" name="heure">
        </div>
        <div class="scrollbar_intranet bloc_note_de_frais" v-show="etape_note_de_frais=='articles'">
            <div class="scrollbar_intranet">
                    <table class="liste_sur_bloc">
                        <thead>
                        <th style="width:45%;padding:0;"><b>@traduction('interface.intranet.article')</b></th>
                        <th v-if="utilisation_devise_etrangere()" style="width:15%;padding:0;"><b>@traduction('interface.intranet.ht_devise')</b></th>
                        <th style="width:15%;padding:0;"><b>@traduction('interface.intranet.ht')</b></th>
                        <th style="width:20%;padding:0;"><b>@traduction('interface.intranet.tva')</b></th>
                        <th style="width:15%;padding:0;"><b>@traduction('interface.intranet.ttc')</b></th>
                        <th style="width:5%;padding:0;"></th>
                        </thead>
                        <tbody>
                        <tr v-for="(ligne,index) in note_de_frais.articles">
                            <input type="hidden" :name="'articles['+index+'][id]'" v-model="ligne.id"
                                   v-if="ligne.id != undefined && ligne.id != '' && ligne.id != 0">
                            <input type="hidden" :name="'articles['+index+'][plafond]'"
                                   :value="articles_pour_note_de_frais[ligne.article_id] != undefined ? articles_pour_note_de_frais[ligne.article_id].plafond : '' ">
                            <input type="hidden" :name="'articles['+index+'][remboursement_plafonne]'"
                                   :value="articles_pour_note_de_frais[ligne.article_id] != undefined ? articles_pour_note_de_frais[ligne.article_id].remboursement_plafonne : '' ">

                            <td style="width:45%;padding:0;">
                                <select @change="mise_a_jour_article(ligne)" :name="'articles['+index+'][article_id]'"
                                    style="width: 100%;" v-model="ligne.article_id">
                                    <option value="0">Sans valeur</option>
                                    <option v-for="(article,id) in articles_pour_note_de_frais" :value="id">@{{
                                        article.nom }}
                                    </option>
                                </select>
                            </td>
                            <td v-if="utilisation_devise_etrangere()" style="width:15%;padding:0;">
                                <champ-montant
                                        :modele="ligne"
                                        nom_sql="montant_devise"
                                        :valeur_non_vide="true"
                                        style_input="width:100%;">
                                </champ-montant>
                            </td>
                            <td style="width:15%;padding:0;">
                                <champ-montant
                                        :modele="ligne"
                                        nom_sql="montant_ht"
                                        :lecture_seule="utilisation_devise_etrangere(false,'montant_ht')"
                                        :name="'articles['+index+'][montant_ht]'"
                                        :valeur_non_vide="true"
                                        style_input="width:100%;">
                                </champ-montant>
                                <input type="hidden" :name="'articles['+index+'][montant_ht]'"
                                       :value="ligne.montant_ht">
                            </td>
                            <td style="width:20%;padding:0;">
                                <select style="width:100%;" :name="'articles['+index+'][taux_tva]'"
                                        @change="mise_a_jour_montant_ligne_ht(ligne, false);" v-model="ligne.taux_tva">
                                    @foreach(champ_libre('note_de_frais_lignes','taux_tva')->champ->recuperation_options_select() as $id_tva => $tva)
                                        <option value="{{$id_tva}}">{{$tva}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="width:15%;padding:0;">
                                <champ-montant
                                        :title="montant_depasse_le_plafond(ligne) ? traduction('interface.intranet.montant_superieur_plafond') : ''"
                                        :modele="ligne"
                                        nom_sql="montant_ttc"
                                        :lecture_seule="utilisation_devise_etrangere(false,'montant_ttc')"
                                        :valeur_non_vide="true"
                                        :name="'articles['+index+'][montant_ttc]'"
                                        :style_input="montant_depasse_le_plafond(ligne) ? 'background : #ff6666;' : ''">
                                </champ-montant>
                                <input type="hidden" :name="'articles['+index+'][montant_ttc]'"
                                       :value="ligne.montant_ttc">
                            </td>
                            <td style="width:5%;padding:0;">
                                <i @click="note_de_frais.articles.splice(index,1);mise_a_jour_montant()"
                                   class="fas fa-times"></i>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                <br><br>
            </div>
        </div>
        <div class="bloc_total_note_de_frais" v-if="note_de_frais.articles.length > 0">
            <table>
                <tbody style="color:white;">
                    <tr v-if="utilisation_devise_etrangere()">
                        <td style="padding-right: 10px;">@traduction('interface.intranet.total_ht_devise') : </td>
                        <td>
                            @{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: code_devise }).format(note_de_frais.montant_devise)}}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-right: 10px;">@traduction('interface.intranet.total_ht') : </td>
                        <td>
                            @{{ note_de_frais.montant_ht | montant }}
                        </td>
                    </tr>
                    <tr v-for="(total_tva,tva) in note_de_frais.totaux_tva">
                        <td style="padding-right: 10px;">@traduction('interface.intranet.total_tva') @{{ tva }} : </td>
                        <td>
                            @{{ total_tva | montant }}
                        </td>
                    </tr>
                    @if(fonctionnalite('ecart_gestion_ttc')['note_de_frais'])
                        <tr>
                            <td style="padding-right: 10px;">@traduction('champs_libres.note_de_frais.ecart_gestion_ttc.nom') : </td>
                            <td>
                                <champ-montant
                                    style="width:45px;"
									:modele="note_de_frais"
									name="ecart_gestion_ttc"
									nom_sql="ecart_gestion_ttc">
								</champ-montant>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding-right: 10px;">@traduction('interface.intranet.total_ttc') : </td>
                        <td>
                            @{{ note_de_frais.montant_ttc | montant }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

@endsection

@push('donnees_pour_vuejs_data')
    {{$type_element}}: {!! modele_par_defaut($type_element) !!},
    {{$type_element}}_initial: {!! modele_par_defaut($type_element) !!},
    etape_note_de_frais: 'informations',
    articles_pour_note_de_frais: {},
    taux_de_tva: {},
    devises : [],
@endpush

@push('donnees_pour_vuejs_mounted')

    this.recuperation_informations_note_de_frais();

    this.$on('maj_champ_montant',(donnees) => {

        if(donnees.nom_sql == 'montant_ht')
            this.mise_a_jour_montant_ligne_ht(donnees.modele);
        else if(donnees.nom_sql == 'montant_ttc')
            this.mise_a_jour_montant_ligne_ttc(donnees.modele);
        else if(donnees.nom_sql == 'montant_devise'){
            var ligne = donnees.modele;
            ligne.montant_ht = Math.round(ligne.montant_devise * this.note_de_frais.taux_de_change *100,2)/100;
            this.mise_a_jour_montant_ligne_ht(ligne, false);
        }
        else if(donnees.nom_sql == 'ecart_gestion_ttc'){

            var seuil_ecart_gestion_ttc = {!! fonctionnalite('seuil_ecart_gestion_ttc') !!};

            if(this.note_de_frais.ecart_gestion_ttc > seuil_ecart_gestion_ttc){
                alerte_eden(this.$root.traduction('messages.js.ecart_gestion_ttc.trop_eleve',null,[this.$options.filters.montant(seuil_ecart_gestion_ttc)]));
                this.note_de_frais.ecart_gestion_ttc = seuil_ecart_gestion_ttc;
            }
            this.mise_a_jour_montant();
        }
    });

    if(this.utilisation_devise_etrangere(true))
        $.post({
            url : 'eden/elements/devise',
            dataType:'json',
            data:{
                filtrage:[
                    {
                        champ : 'disponible',
                        condition : 'where',
                        valeur : 1
                    },
                ]
            }
        }).done((elements) => {
            this.devises = elements;
        });
@endpush

@push('donnees_pour_vuejs_methods')

    ajouter_article_note_de_frais: function(){

        nouvelle_ligne = {!! modele_par_defaut('note_de_frais_lignes') !!};
        nouvelle_ligne.montant_ht = 0;
        nouvelle_ligne.montant_devise = 0;
        nouvelle_ligne.montant_ttc = 0;
        if(nouvelle_ligne.article_id == '')
            nouvelle_ligne.article_id = 0;

        if(nouvelle_ligne.article_id != 0)
            nouvelle_ligne.taux_tva = this.articles_pour_note_de_frais[nouvelle_ligne.article_id].code_tva;
        else
            nouvelle_ligne.taux_tva = 0;

        if(this.note_de_frais.articles == undefined)
            this.note_de_frais.articles = []

        this.note_de_frais.articles.push(nouvelle_ligne);

    },

    mise_a_jour_montant_ligne_ht : function(ligne, calcul_devise = true){

        var montant_ht = ligne.montant_ht;
        var tva = ligne.taux_tva;

        if(tva <= 0 || tva == undefined)
            tva = 0;
        else
            tva = this.taux_de_tva[ligne.taux_tva].taux

        var montant_ttc = 0;

        if(!isNaN(montant_ht))
            montant_ttc = montant_ht * (1 + tva/100);

        ligne.montant_ttc = Math.round(montant_ttc * 100,2)/100;

        if(this.utilisation_devise_etrangere() && calcul_devise)
            ligne.montant_devise = Math.round(ligne.montant_ht / this.note_de_frais.taux_de_change * 100,2)/100;

        this.mise_a_jour_montant();
    },

    mise_a_jour_montant_ligne_ttc : function(ligne){

        var montant_ttc = ligne.montant_ttc;
        var tva = ligne.taux_tva;

        if(tva <= 0 || tva == undefined)
            tva = 0;
        else
            tva = this.taux_de_tva[ligne.taux_tva].taux

        var montant_ht = 0;

        if(!isNaN(montant_ttc))
            montant_ht = montant_ttc / (1 + tva/100);

        ligne.montant_ht = Math.round(montant_ht * 100,2)/100;

        if(this.utilisation_devise_etrangere())
            ligne.montant_devise = Math.round(ligne.montant_ht / this.note_de_frais.taux_de_change * 100,2)/100;

        this.mise_a_jour_montant();
    },

    mise_a_jour_montant: function(){

        var taux_de_tva = {};

        if(typeof this.$root.taux_de_tva == 'object'){

            for(valeur of Object.values(this.$root.taux_de_tva)){
                taux_de_tva[valeur.id] = valeur.code;
            }
        }

        //On calcule les montant globaux

        var total_ht = 0;
        var total_ht_devise = 0;
        var total_ttc = 0;
        var totaux_tva = {};

        this.note_de_frais.articles.forEach(function(ligne){

            var montant_ht = parseFloat(ligne.montant_ht);
            var montant_devise = parseFloat(ligne.montant_devise);
            var montant_ttc = parseFloat(ligne.montant_ttc);

            if(!isNaN(montant_ht)){

                total_ht += parseFloat(montant_ht);
                total_ht_devise += parseFloat(montant_devise);
                total_ttc += montant_ttc;

                if(totaux_tva[ligne.taux_tva] == undefined)
                    totaux_tva[ligne.taux_tva] = 0;

                totaux_tva[ligne.taux_tva] += (montant_ttc - montant_ht);
            }

        });

        if(this.note_de_frais.ecart_gestion_ttc != 0 && !isNaN(this.note_de_frais.ecart_gestion_ttc))
            total_ttc += parseFloat(this.note_de_frais.ecart_gestion_ttc);

        this.$set(this.note_de_frais,'montant_ht',Math.round(total_ht * 100,2)/100);
        this.$set(this.note_de_frais,'montant_devise',Math.round(total_ht_devise * 100,2)/100);
        this.$set(this.note_de_frais,'montant_ttc',Math.round(total_ttc * 100,2)/100);

        this.note_de_frais.totaux_tva = {};

        for(cle_taux_tva in totaux_tva){

            if(taux_de_tva[cle_taux_tva] !== undefined)
                this.note_de_frais.totaux_tva[taux_de_tva[cle_taux_tva]] = Math.round(totaux_tva[cle_taux_tva] * 100,2)/100;
        }
    },

    recuperation_informations_note_de_frais(){

        var vue_instance = this;

        $.get({

            url: "{{URL::to('eden/note_de_frais/informations')}}",
            dataType: "json",
            method: 'GET'
        }).done((donnees) => {

            vue_instance.articles_pour_note_de_frais = donnees.articles_pour_note_de_frais;
            vue_instance.taux_de_tva = donnees.taux_de_tva;

            this.$emit('chargement_informations_note_de_frais');

        });
    },

    enregistrer_note_de_frais : function(module_id){

        var formulaire = $('#formulaire_'+module_id);
        var type_element = 'note_de_frais';

        var route = '/eden/element/'+type_element+'/creer';

        if(this[type_element].id !== undefined)
            route = '/eden/element/'+type_element+'/'+this[type_element].id+'/enregistrer';

        loading(true);

        var informations = this.$refs['formulaire_'+module_id].formulaire_donnees_renseignes({},formulaire);
        var erreur_affichage = '';
        var champ_non_remplis = this.$refs['formulaire_'+module_id].verification_champs_obligatoires(informations);

        if(champ_non_remplis.length > 0) {

            var nom_champ_non_remplis = champ_non_remplis.map(function(champ_obligatoire){
                return champ_obligatoire.nom;
            });

            if(champ_non_remplis.length == 1)
                erreur_affichage = this.$root.traduction('messages.php.champ_obligatoire')+nom_champ_non_remplis.join(', ');
            else
                erreur_affichage = this.$root.traduction('messages.php.champs_obligatoires')+nom_champ_non_remplis.join(', ');

            $('#alerte_erreur_'+module_id).html(erreur_affichage).show('fast').delay(5000).hide('fast');

            this.$refs['formulaire_'+module_id].indication_champs_obligatoires(champ_non_remplis,formulaire);

            loading(false);
            return false;
        }

        // On affiche une erreur si aucun article n'est renseigné
        if(this.note_de_frais.articles.length == 0) {

            erreur_affichage = this.$root.traduction('messages.js.note_de_frais.pas_d_articles');

            $('#alerte_erreur_'+module_id).html(erreur_affichage).show('fast').delay(5000).hide('fast');

            loading(false);
            return false;
        }
        else{

            //On vérifie si des articles ne sont pas remplis (pas liés à un article_note_de_frais)
            var compteur_articles_non_remplis = 0;

            for(article of this.note_de_frais.articles){

                if(article.article_id == 0)
                    compteur_articles_non_remplis++;
            }

            if(compteur_articles_non_remplis === 1)
                erreur_affichage = this.$root.traduction('messages.js.note_de_frais.article_non_rempli');
            else if(compteur_articles_non_remplis > 1)
                erreur_affichage = this.$root.traduction('messages.js.note_de_frais.articles_non_remplis');

            if(erreur_affichage !== ''){
                $('#alerte_erreur_'+module_id).html(erreur_affichage).show('fast').delay(5000).hide('fast');
                this.etape_note_de_frais = "articles";
                loading(false);
                return false;
            }
        }

		$.post({

			url: route,
			dataType: "json",
			data: formulaire.serialize(),

		}).done((donnees) => {

            loading(false);
			if(donnees.retour !== true) {

				$('#alerte_erreur_'+module_id).html(donnees.retour).show('fast').delay(1000).hide('fast');
				return;
			}

            $('.intranet').css('pointer-events','none');

            var liste_note_de_frais = this.$refs['liste_libre_' + this.listes_par_type_element.note_de_frais];

            if(liste_note_de_frais !== undefined)
                liste_note_de_frais.actualisation_filtres();

			$('#alerte_succes_'+module_id).html(
                this.traduction('messages.js.enregistrement_succes')
            ).show('fast').delay(5000).hide('fast');
			setTimeout(() => {
                this.bloc_affiche = '';
                $('.intranet').css('pointer-events','unset');
                this.etape_note_de_frais='informations';
            }, 500);
		});
    },

    mise_a_jour_article : function(ligne){

        if((ligne.taux_tva == 0 || ligne.taux_tva == null) &&
            this.articles_pour_note_de_frais[ligne.article_id] != undefined &&
            this.articles_pour_note_de_frais[ligne.article_id].code_tva != null
        )
            ligne.taux_tva = this.articles_pour_note_de_frais[ligne.article_id].code_tva;

        this.mise_a_jour_montant_ligne_ht(ligne);
    },

    utilisation_devise_etrangere(uniquement_fonctionnalite = false,champ = false){

		var fonctionnalite = "{{fonctionnalite('saisie_documents_devise_etrangere')}}" == 1 ? true : false;

		if(uniquement_fonctionnalite)
			return fonctionnalite === true;

		var griser_prix = "{{fonctionnalite('documents_devise_etrangere_griser_prix_converti')}}" == 1 ? true : false;

        if(champ !== false && griser_prix === false)
            return false;

        if(this.devise_euro_id === undefined)
            return false;

		if(this.note_de_frais.devise == 0 || this.note_de_frais.devise == null || this.note_de_frais.devise == undefined)
			this.note_de_frais.devise = this.devise_euro_id;

        return fonctionnalite === true && parseInt(this.note_de_frais.devise) !== parseInt(this.devise_euro_id);
    },

@endpush

@push('donnees_pour_vuejs_computed')
    devise_euro_id(){
        var devise_euro_id = 0;

        $.each(this.devises,(index,devise) => {

            if(devise.code == '{!! maquette('devise_application_iso') !!}'){
                devise_euro_id = devise.id;
                return;
            }
        });

        return devise_euro_id.toString();
    },
    code_devise(){

        var code_devise = '{!! maquette('devise_application_iso') !!}';

        $.each(this.devises,(index,devise) => {

            if(this.note_de_frais.devise == devise.id){
                code_devise = devise.code;
                return;
            }
        });

        if(code_devise != '')
            return code_devise;

        return '{!! maquette('devise_application_iso') !!}';
    },
@endpush