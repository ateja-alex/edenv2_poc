
@extends('eden::templates.template')

@section('title')Rapprochements @stop
@section('styles')

<style>

td{
    font-size:12px;
}

.css_option_active{

    color:#20B50F;
    font-weight:bold;
}

.gras{
    font-weight:bold;
    cursor : pointer;
}

.ligne_cochee_invisible.ligne_cochee  {

	display: none;
}

.ligne_cochee{

    text-decoration: line-through;
	color: #c7c7c7;
}
</style>
@endsection

@section('content')

<div class="content-wrapper" >
@include("eden::includes.treso_menu")
    <div id="base-content" class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="css_flex_header_liste">
                                    <h4>@traduction('interface.tresorerie.rapprochement_saisie.titre_module.charges_mensuelles_recurrentes')</h4>
                                    <span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <div class="dropdown-header">@traduction('interface.tresorerie.rapprochement_saisie.liste_des_entites')</div>
                                        <a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12"><b>@traduction('interface.tresorerie.rapprochement_saisie.charges_a_venir_ce_mois')</b></div>
                                    <br>
                                    <div class="col-md-12" v-for="charge_recurrente_actuelle in charges_recurrentes_actuelles" :key="charge_recurrente_actuelle.id" :id_charge_recurrente="charge_recurrente_actuelle.id"  onmouseover="affichage_au_passage(this)" onmouseout="affichage_au_passage(this)" :montant="charge_recurrente_actuelle.montant" :nom="charge_recurrente_actuelle.charge" @click="enregistre_rapprochement_charge">
                                        <span class="col-md-12" >
                                        <i class="fas fa-check" ></i>
                                            @{{ charge_recurrente_actuelle.charge }} : <span class="badge">@{{ charge_recurrente_actuelle.montant }}{!! maquette('devise_application_symbole') !!} TTC</span>
                                        </span>
                                        <br />
				                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12" id="charges_deja_rapprochees"><b>@traduction('interface.tresorerie.rapprochement_saisie.charges_deja_decaissee_ce_mois')</b></div>
                                        <span class='col-md-12' v-for="charge_recurrente_decaissee_actuelle in charges_recurrentes_decaissees_actuelles" :key="charge_recurrente_decaissee_actuelle.id">
                                            <s>@{{ charge_recurrente_decaissee_actuelle.charge }} : @{{ charge_recurrente_decaissee_actuelle.montant }}{!! maquette('devise_application_symbole') !!} TTC</s>
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="css_flex_header_liste">
                                    <h4>@traduction('interface.tresorerie.rapprochement_saisie.titre_module.releve_bancaire')</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div  id="js_ne_pas_afficher_les_transactions_selectionnees" :class="{ 'css_option_active' : affichage_transaction == 1}" onmouseover="affichage_au_passage(this)" onmouseout="affichage_au_passage(this)" @click="ne_pas_afficher_les_transactions_selectionnees">
                                    <i class="fas fa-check" ></i>
                                    @traduction('interface.tresorerie.rapprochement_saisie.ne_pas_afficher_transactions_deja_cochees')
                                </div>
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tableau_recapitulatif_mouvement" width="100%" cellspacing="0" >
                                        <thead>
                                            <tr class="css_table_titre">
                                                <td style="text-transform: uppercase">@traduction('interface.tresorerie.rapprochement_saisie.tableau_recap.colonne.date')</td>
                                                <td style="text-transform: uppercase">@traduction('interface.tresorerie.rapprochement_saisie.tableau_recap.colonne.nom')</td>
                                                <td style="text-align: right;text-transform: uppercase">@traduction('interface.tresorerie.rapprochement_saisie.tableau_recap.colonne.montant')</td>
                                            </tr>

                                        </thead>
                                        <tbody >
                                            <tr v-for="transaction in transactions" :key="transaction.id" :id="transaction.id" :class="{ 'ligne_cochee' : transaction.rapprochement_treso == 1}" class="js_ligne_transaction" @click="barre_ligne_releve_bancaire" onmouseover="affichage_au_passage(this)" onmouseout="affichage_au_passage(this)">
                                                <td>@{{transaction.date}}</td>
                                                <td>@{{transaction.original_wording}}</td>
                                                <td style="text-align: right;">@{{transaction.formatted_value}}</td>
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
    </div>
</div>
@endsection

@section('donnees_pour_vuejs_data')

charges_recurrentes_actuelles : {!! json_encode($charges_recurrentes_actuelles) !!},
charges_recurrentes_decaissees_actuelles : {!! json_encode($charges_recurrentes_decaissees_actuelles) !!},
transactions : {!! json_encode($transactions) !!},
affichage_transaction : {!! $affichage_transaction !!},
type : 'rapprochement',
entite_choisi : {!! json_encode($entite_choisi) !!},
entites : {!! json_encode($entites) !!},

@endsection

@section('donnees_pour_vuejs_mounted')

    var vue_instance = this;
    if(vue_instance.affichage_transaction == 1){

        $('.js_ligne_transaction').addClass('ligne_cochee_invisible');
    }
@endsection
@section('donnees_pour_vuejs_methods')

    ne_pas_afficher_les_transactions_selectionnees() {

        var affichage_transaction;
        var vue_instance = this;
		if($('.ligne_cochee_invisible').length == 0) {

			$('.js_ligne_transaction').addClass('ligne_cochee_invisible');
			$('#js_ne_pas_afficher_les_transactions_selectionnees').addClass('css_option_active');
            affichage_transaction = 1;

		}
		else {

			$('.js_ligne_transaction').removeClass('ligne_cochee_invisible');
            $('#js_ne_pas_afficher_les_transactions_selectionnees').removeClass('css_option_active');
            affichage_transaction = 0;

        }

        // on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/enregistrer_affichage_transaction')}}",
			dataType: "json",
			data: {

				affichage_transaction: affichage_transaction,
			},

		})
		.done(function(donnees){



        });

    },

    // pour enlever une ligne sur le relevé bancaire
    barre_ligne_releve_bancaire(event) {

        var vue_instance = this;
        var ligne = $(event.target).closest('tr');
        var id_transaction = ligne.attr('id');
        var rapprochement_treso;

        if(ligne.hasClass('ligne_cochee')) {

            ligne.removeClass('ligne_cochee');
            rapprochement_treso = 0;
        }
        else {

            ligne.addClass('ligne_cochee');
            rapprochement_treso = 1;

        }

        // on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/enregistrer_etat_transaction')}}",
			dataType: "json",
			data: {

				id_transaction: id_transaction,
				rapprochement_treso: rapprochement_treso,
			},

		})
		.done(async function(donnees){

			if(donnees !== true) {

				await erreur(donnees);
				return;
			}

		});
    },

    enregistre_rapprochement_charge(event) {

		var ligne = $(event.target).closest('div');

        if(ligne.attr('montant')>0){

		    $('#charges_deja_rapprochees').after("<span class='col-md-12 ligne_barree'><s>"+ligne.attr('nom')+" : "+ligne.attr('montant')+"{!! maquette('devise_application_symbole') !!} TTC</s></span>");
        }

        else{

            $('#charges_deja_rapprochees').after("<span class='col-md-12 ligne_barree'><s>"+ligne.attr('nom')+" : {!! maquette('devise_application_symbole') !!} TTC</s></span>");
        }
		ligne.fadeOut(500);

        // on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/charge_decaissee/enregistrer')}}",
			dataType: "json",
			data: {

				id_charge_recurrente: ligne.attr('id_charge_recurrente'),
				date: '{{$date}}',
			},

		})
		.done(async function(donnees){

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}


		});
	},

    changement_entite(event){

        var vue_instance = this;
        var	event = $(event.target);
        entite_id = event.attr('id');

        // on lance un appel ajax
            $.post({
                url : "{{ URL::to('/eden/tresorerie/rapprochement/changement_entite')}}",
                dataType: "json",
                data: {

                    entite_id : entite_id,
                },
            })
            .done(function(donnees){

                vue_instance.charges_recurrentes_actuelles = donnees.charges_recurrentes_actuelles;
                vue_instance.charges_recurrentes_decaissees_actuelles = donnees.charges_recurrentes_decaissees_actuelles;
                vue_instance.transactions = donnees.transactions;
                vue_instance.entites = donnees.entites;
                vue_instance.entite_choisi = donnees.entite_choisi;

                $('.ligne_barree').remove();
                setTimeout(function(){
                    if($('#js_ne_pas_afficher_les_transactions_selectionnees').hasClass('css_option_active')){
                            $('.js_ligne_transaction').addClass('ligne_cochee_invisible');
                    }
                },50);
            });
        },


@endsection

@section('scripts')

<script type="text/javascript">

function affichage_au_passage(ligne) {
    if($('.gras').length == 0) {

			$(ligne).addClass('gras');
	}
	else {

		$(ligne).removeClass('gras');
	}
}




</script>

@endsection





