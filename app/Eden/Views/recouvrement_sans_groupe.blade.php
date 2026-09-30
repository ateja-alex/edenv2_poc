@extends('eden::templates.template')

@section('content')
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-3" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">@{{ new Intl.NumberFormat("fr-FR").format(indicateurs.montant_du.toFixed()) }}{{ maquette('devise_application_symbole') }}</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement_sans_groupe.titre_module.montant_du')</span>
					</div>
				</div>
				<div class="col-md-3" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">@{{indicateurs.nombre_documents}}</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement_sans_groupe.titre_module.documents')</span>
					</div>
				</div>

                <div class="col-md-3" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">@{{creances_retard.toFixed(2)}} jours</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement_sans_groupe.titre_module.creances_en_retard')</span>
					</div>
				</div>
               
                <div class="col-md-3" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">@{{ca_des_30_derniers_jours.toFixed(2)}} %</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement_sans_groupe.titre_module.ca_30_dernier_jours')</span>
					</div>
				</div>
				
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
                            <span style="display:flex; justify-content:space-between;">
                                <h4>@traduction('interface.recouvrement_sans_groupe.titre_liste')  </h4>
                                <span class="css__lien" @click.prevent="eden_envoyer_par_email_elements_selectionnes_recouvrement()"> @traduction('interface.recouvrement_sans_groupe.lien_envoi_mail') </span>

                            </span>

                            <div style="display:flex; justify-content:space-between;" class="mt-2 mb-2">
                                <div>
                                    <span class="badge mr-2" :class="{'badge-success' : filtres_relances.zero_a_trente}" @click="changement_filtres_jours('zero_a_trente')">@traduction('interface.recouvrement_sans_groupe.filtre.zero_trentes_jours')</span>
                                    <span class="badge mr-2" :class="{'badge-success' : filtres_relances.trente_a_soixante}" @click="changement_filtres_jours('trente_a_soixante')">@traduction('interface.recouvrement_sans_groupe.filtre.trentes_soixante_jours')</span>
                                    <span class="badge mr-2" :class="{'badge-success' : filtres_relances.soixante_quatre_vingt_dix}" @click="changement_filtres_jours('soixante_quatre_vingt_dix')">@traduction('interface.recouvrement_sans_groupe.filtre.soixante_quatre_vingt_dix_jours')</span>
                                    <span class="badge mr-2" :class="{'badge-success' : filtres_relances.plus_de_quatre_vingt_dix}" @click="changement_filtres_jours('plus_de_quatre_vingt_dix')">@traduction('interface.recouvrement_sans_groupe.filtre.plus_quatre_vingt_dix_jours')</span>

                                </div>
                                <div>
                                    @traduction('interface.recouvrement_sans_groupe.tout')
                                    <a href="" class="css__link" @click.prevent="selectionner_tout()">@traduction('interface.recouvrement_sans_groupe.selectionner')</a> /
                                    <a href="" class="css__link" @click.prevent="deselectionner_tout()">@traduction('interface.recouvrement_sans_groupe.deselectionner') </a>
                                </div>
                                <div>
                                    <input type="text" :placeholder="traduction('interface.recouvrement_sans_groupe.placeholder.recherche')" v-model="filtres_relances.recherche" @change="mise_a_jour"/>
                                </div>
                                <div>
                                    <select  v-model="filtres_relances.entite" @change="changement_entite">
                                        <option value="0" v-html="traduction('interface.valeurs_select.toutes_les_entites')"></option>
                                        <option :value="entite.id" v-for="entite in entites">@{{entite.nom}}</option>
                                    </select>
                                </div>
                            </div>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th>@traduction('interface.recouvrement_sans_groupe.colonnes.document')</th>
                                            <th>@traduction('interface.recouvrement_sans_groupe.colonnes.retard')</th>
											<th>@traduction('interface.recouvrement_sans_groupe.colonnes.solde')</th>
                                            <th>@traduction('interface.recouvrement_sans_groupe.colonnes.date')</th>

											<th>@traduction('interface.recouvrement_sans_groupe.colonnes.options')</th>
                                            @foreach($type_relance_recouvrement as $type)
											    <th>{{$type->nom}}</th>
                                            @endforeach
											<th>@traduction('interface.recouvrement_sans_groupe.colonnes.commentaires')</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="document in factures_a_afficher" :element_id_pour_mail="document.id" class="js_liste_ligne_selectionnable">
											<td>
												<a :href="'{{ URL::to('/eden/document/') }}/'+document.type_document+'/'+document.id">@{{ document.reference_document }}</a><br/>
												<span v-html="document.client"></span><br/>
												  @{{document.client_nom}} @{{document.client_prenom}} <span v-html="document.client_tags"></span>
											</td>
                                            <td>@{{document.jours_de_retard}} @traduction('interface.recouvrement_sans_groupe.jours')</td>
											<td>@{{ new Intl.NumberFormat("fr-FR").format(document.solde_document_ttc) }}{{ maquette('devise_application_symbole') }}</td>
											<td> @{{document.date | date}}</td>
                                            <td><span class="fa fa-envelope" @click="envoyer_par_mail(document.id, document.client_id, document.type_document)" >
                    
                                            </span></td>
                                            <template v-for="type in type_relance_recouvrement">
                                                <td>
                                                    <div v-for="relance in document.relances" style="display:flex">
                                                        <span class="mb-1" v-if="relance.type_relance == type.id">

                                                            <!-- Boutons "a faire" -->
                                                            <span class="fas fa-exclamation-triangle" style="color:red;cursor:pointer;" v-if="relance.a_faire == 1" @click="relance_a_faire(relance, 0)"></span>
                                                            <span class="fas fa-check" style="color:green;" v-else></span>

                                                            <span class="badge badge-success" >@{{ relance.date | date }}</span>
                                                            <span class="badge badge-danger" @click="supprimer_date(relance.id)">X</span>
                                                            <span class="js_suppression_" style="display:none;">
                                                                <img src="{{asset('/images/ajax_loader.gif')}}" height="30"/>

                                                            </span>
                                                        </span>
                                                    </div>

                                                    <span class="css__lien js_ajouter_relance" :ajouter_relance="'ajouter_relance'+document.id+'_'+type.id" @click="ajouter_une_relance(document.id, type.id, document.type_document)">@traduction('interface.recouvrement_sans_groupe.ajouter_relance')</span>
                                                    <span class="js_chargement" :chargement="'chargement_'+document.id+'_'+type.id" style="display:none;">
                                                        <img src="{{asset('/images/ajax_loader.gif')}}" height="30"/>

                                                    </span>
                                                </td>
                                            </template>
											<td style="padding: 0px;"><textarea style="border:0px; width: 100%; height: 100%;" v-model="document.commentaires_recouvrement" :placeholder="traduction('interface.recouvrement_sans_groupe.placeholder.commentaires')" @change="enregistre_commentaire(document)"></textarea></td>
											
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
@endsection

@push('scripts')
	<script>
	/*
	$('body').on('click', '.js_liste_ligne_selectionnable', function() {
		
		console.log('ok selection');

		$(this).toggleClass('css_liste_ligne_selectionnee');
		$(this).toggleClass('js_liste_ligne_selectionnee');
		
		if($(this).hasClass('js_liste_ligne_selectionnee')) {
			
			$(this).find('td').eq(0).find('input[type=checkbox]').attr('checked', true);
			$(this).find('td').eq(0).find('input[type=checkbox]').prop('checked', true);
		}
		else {
			
			$(this).find('td').eq(0).find('input[type=checkbox]').attr('checked', false);
			$(this).find('td').eq(0).find('input[type=checkbox]').prop('checked', false);
		}
		
		$('.js_nombre_lignes_selectionnees').text($('.js_liste_ligne_selectionnee').length);
		
		if($('.js_liste_ligne_selectionnee').length == 0) {
			
			$('.js_action_lignes_selectionnees').addClass('disabled');
		}
		else {
			
			$('.js_action_lignes_selectionnees').removeClass('disabled');
		}
	});
	*/
	</script>
@endpush

@push('donnees_pour_vuejs_data')
	groupes_recouvrement: {!! $groupes_recouvrement !!},
    type_relance_recouvrement: {!! $type_relance_recouvrement !!},
    entites: {!! $entites !!},
    filtres_relances : {

        recherche : '',
        entite : {!! $entite_par_defaut !!},
        zero_a_trente : false,
        trente_a_soixante : false,
        soixante_quatre_vingt_dix: false,
        plus_de_quatre_vingt_dix : false

    },
    id_documents_a_ajouter_par_email : [],
    ajouter_les_relances : false,
    indicateurs : {!! $indicateurs !!},
    creances_retard: {!! $creances_retard !!},
    ca_des_30_derniers_jours : {!! $ca_des_30_derniers_jours !!},
    factures_a_afficher: {!! $groupes_recouvrement !!},
        
@endpush


@push('donnees_pour_vuejs_watch')

    ajouter_les_relances: {
		handler: function(newVal, oldVal) {

			@if(empty(fonctionnalite('numero_de_la_ligne_de_relance_par_email')))
                return '';
            @endif

			var type = '{{ fonctionnalite('numero_de_la_ligne_de_relance_par_email') }}';

			if(newVal == true) {
                
                var id_documents = Object.keys(this.id_documents_a_ajouter_par_email);
                var context = this;

                id_documents.map(function(index){

                    context.ajouter_une_relance(context.id_documents_a_ajouter_par_email[index], type);
                })

                // on supprime la selection des lignes
                $('.js_liste_ligne_selectionnable').removeClass('css_liste_ligne_selectionnee  js_liste_ligne_selectionnee');
                this.ajouter_les_relances = false;
            }
        }
    },
@endpush

{{-- <script> --}}

@push('donnees_pour_vuejs_methods')

    relance_a_faire(relance, valeur) {

        relance.a_faire = valeur;

        $.ajax({
            method: 'POST',
            url: "/eden/element/relance_recouvrement/"+relance.id+"/enregistrer",
            dataType: "json",
            data: {
                
                a_faire: valeur
            }
        }).done(async function(donnees) {
                
            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }
        });

    },

    mise_a_jour() {

        this.factures_a_afficher = [];
    
        var nombre_documents = 0;
        var montant_du = 0;
		
		// 
		window.setTimeout(function() {
			
			vue_instance.groupes_recouvrement.map(function(document){

				if(vue_instance.afficher_ligne_en_fonctions_des_filtres(document)) {

					nombre_documents++;
					montant_du += document.solde_document_ttc;
					vue_instance.factures_a_afficher.push(document)
				}
			})
		}, 250);

		// mise à jour des indicateurs
        $.post({

            url: "{{ route('recouvrement.calcule_indicateurs_par_periode') }}",
            data: {montant_du : montant_du}
        })
        .done(function(donnees) {
        
            vue_instance.indicateurs.nombre_documents = nombre_documents;
            vue_instance.indicateurs.montant_du = montant_du;
            vue_instance.creances_retard = donnees.creances_retard;
            vue_instance.ca_des_30_derniers_jours = donnees.ca_des_30_derniers_jours;   
        });
        
    },

    changement_filtres_jours(periode) {

        this.filtres_relances[periode] = !this.filtres_relances[periode];
        this.mise_a_jour();
    },

    envoyer_par_mail(document_id, client_id, type_element) {

        this.$root.$emit('envoie_email',{
            type_element: type_element, id_element: document_id,
            documents: [{type_element: type_element, id_element: document_id}],
            variables: {client: {type_element: 'client', id_element: client_id },document: {type_element: type_element, id_element: document_id},
            },
            ids_emails_recouvrement : [document_id],

        });

    },
    
	
	eden_envoyer_par_email_elements_selectionnes_recouvrement: function() {
		
		this.envoyer_email_avec_le_type();
	},

    envoyer_email_avec_le_type() {

        var ids = [];
        var ids_documents = [];

        $('.js_liste_ligne_selectionnee').each(function() {
            
            ids.push($(this).attr('element_id_pour_mail'));
            
            ids_documents.push({type_element: 'facture_vente', id_element: $(this).attr('element_id_pour_mail')});
        });

        if(ids.length == 0)
            return;

        this.$root.$emit('envoie_email',{ids_elements: ids, type_element: 'facture_vente', documents: ids_documents, ids_emails_recouvrement : ids,});
    },

    inverser_selection() {

        $( "body .js_liste_ligne_selectionnable" ).each(function( index ) {

            $(this).toggleClass('css_liste_ligne_selectionnee');
            $(this).toggleClass('js_liste_ligne_selectionnee');
            
            if($(this).hasClass('js_liste_ligne_selectionnee')) {
                
                $(this).find('td').eq(0).find('input[type=checkbox]').attr('checked', true);
                $(this).find('td').eq(0).find('input[type=checkbox]').prop('checked', true);
            }
            else {
                
                $(this).find('td').eq(0).find('input[type=checkbox]').attr('checked', false);
                $(this).find('td').eq(0).find('input[type=checkbox]').prop('checked', false);
            }
            
            $('.js_nombre_lignes_selectionnees').text($('.js_liste_ligne_selectionnee').length);
            
            if($('.js_liste_ligne_selectionnee').length == 0) {
                
                $('.js_action_lignes_selectionnees').addClass('disabled');
            }
            else {
                
                $('.js_action_lignes_selectionnees').removeClass('disabled');
            }
        });
        
    },

    deselectionner_tout() {

        $( "body .js_liste_ligne_selectionnable" ).each(function( index ) {

            $(this).removeClass('css_liste_ligne_selectionnee');
            $(this).removeClass('js_liste_ligne_selectionnee');
                
            $(this).find('td').eq(0).find('input[type=checkbox]').attr('checked', false);
            $(this).find('td').eq(0).find('input[type=checkbox]').prop('checked', false);
            
            $('.js_nombre_lignes_selectionnees').text($('.js_liste_ligne_selectionnee').length);
                
            $('.js_action_lignes_selectionnees').addClass('disabled');
        });
        
    },

    selectionner_tout() {

        vue_instance.deselectionner_tout();

        vue_instance.inverser_selection();
        
    },

    supprimer_date(id) {

        loading(true);

        var parametres = {  id : id  };
        var context = this;

        $.post({
                    
            url: "{{ route('recouvrement.supprimer_relance') }}",
            data: parametres
        })
        .done(function(donnees) {

            loading(false);

            if(donnees.success) {

                context.factures_a_afficher = donnees.groupes_recouvrement;
                context.groupes_recouvrement = donnees.groupes_recouvrement;
                context.mise_a_jour();
            }
            else {
                toastr.error('Une erreur est survenue');
            }       
        });
    },

    afficher_ligne_en_fonctions_des_filtres(document) {

        // client référent et montant

        var afficher_ligne_date = false;
        var afficher_ligne_recherche = false;

        // si aucun filtre date est choisi, on affiche tout
        if(this.filtres_relances.zero_a_trente == false && this.filtres_relances.trente_a_soixante == false
        && this.filtres_relances.soixante_quatre_vingt_dix == false && this.filtres_relances.plus_de_quatre_vingt_dix == false) {

            afficher_ligne_date = true;

        }
        else {

            if(this.filtres_relances.plus_de_quatre_vingt_dix) {

                if(document.jours_de_retard >= 90) {

                    afficher_ligne_date = true;
                }
            }

            if(this.filtres_relances.soixante_quatre_vingt_dix) {

                if(document.jours_de_retard >= 60 && document.jours_de_retard < 90) {

                    afficher_ligne_date = true;
                }
            }  

            if(this.filtres_relances.trente_a_soixante) {

                if(document.jours_de_retard >= 30 && document.jours_de_retard < 60) {

                    afficher_ligne_date = true;
                }
            } 

            if(this.filtres_relances.zero_a_trente) {

                if(document.jours_de_retard > 0 && document.jours_de_retard < 30) {

                    afficher_ligne_date = true;
                }
            }  
        }

        var client = document.client_nom+' '+document.client_prenom 


        if(document.nom_entreprise == null )  {

            if(this.filtres_relances.recherche == "") {

                afficher_ligne_recherche = true;
            }
            else {

                var recherche_montant = document.solde_document_ttc.toString().toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())
                var recherche_reference = document.reference_document.toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())
                var recherche_client = client.toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())

                if(recherche_montant != -1 || recherche_reference != -1 || recherche_client != -1) {

                    afficher_ligne_recherche = true;
                }
            }
        }
        else {

            if(this.filtres_relances.recherche == "") {

                afficher_ligne_recherche = true;
            }
            else {

                var recherche_montant = document.solde_document_ttc.toString().toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())
                var recherche_reference = document.reference_document.toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())
                var recherche_client = client.toLowerCase().indexOf(this.filtres_relances.recherche.toLowerCase())

                if(recherche_montant != -1 || recherche_reference != -1 || recherche_client != -1) {

                    afficher_ligne_recherche = true;
                }
            }
        }
		
		if(afficher_ligne_date  && afficher_ligne_recherche) {

            return true;
        }

        return false;
    },

    ajouter_une_relance(id, type, type_element) {

        var loader = id+'_'+type;

        $('.js_ajouter_relance[ajouter_relance="ajouter_relance'+loader+'"]').hide()
        $('.js_chargement[chargement="chargement_'+loader+'"]').show()

        var parametres = {  id : id,  type : type, type_element : type_element };

        var context = this;

        $.post({
					
            url: "{{ route('recouvrement.ajoute_relance') }}",
            data: parametres
        })
        .done(function(donnees) {
            $('.js_ajouter_relance[ajouter_relance="ajouter_relance'+loader+'"]').show()

            $('.js_chargement[chargement="chargement_'+loader+'"]').hide()

            if(donnees.success) {

                context.factures_a_afficher = donnees.groupes_recouvrement;
                context.groupes_recouvrement = donnees.groupes_recouvrement;
				
				// @note Frédéric : je bloque cela, car ça pose problème quand on envoie plusieurs dizaines de mails,
				// la mise à jour se lance avant que toutes les alertes soient créées, du coup certaines alertes ne s'enregistrent pas
                // context.mise_a_jour();

            }
            else {
                toastr.error('Une erreur est survenue');
            }       
        });

    },

    enregistre_commentaire: function(document) {
		
        //console.log(document.commentaires_recouvrement)
		$.post({
			
			url: "{{ URL::to("eden/element/facture_vente/") }}/"+document.id+"/enregistrer",
			dataType: "json",
			data: {
				
				commentaires_recouvrement: document.commentaires_recouvrement
			}
		}).done(function() {
			
			
		});
	},

    changement_entite: function() {


        var parametres = { entite : this.filtres_relances.entite };

        var context = this;
        loading(true)

        $.post({
					
            url: "{{ route('recouvrement.changement_entite') }}",
            data: parametres
        })
        .done(function(donnees) {
			
			//console.log(donnees);
			
            loading(false)
			
			// return;

            context.factures_a_afficher = donnees.groupes_recouvrement;
            context.groupes_recouvrement = donnees.groupes_recouvrement;
            context.indicateurs = donnees.indicateurs;
            context.creances_retard = donnees.creances_retard;
            context.ca_des_30_derniers_jours = donnees.ca_des_30_derniers_jours;
            context.mise_a_jour();

        });
    },

  
@endpush

{{-- </script> --}}