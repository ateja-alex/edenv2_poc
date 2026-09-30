@extends('eden::authentification.template')

@section('title')
Interface Fournisseur
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<form id="ajout_facture_form" class="form-horizontal" method="POST" action="{{ route('interface_fournisseur.enregistre_facture_achat', [$fournisseur->modele->id, $fournisseur->genere_clef_interface_fournisseur($fournisseur->modele)]) }}" enctype="multipart/form-data">
	{{ csrf_field() }}

	<h3 class="text-center">Bienvenue, {{ $fournisseur->modele->nom }}</h3> <br>
	
	<div class="alert alert-danger" style="display: none"></div>
	<div class="alert alert-success" style="display: none"></div>


	<div class="modal fade" id="modalLoading" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel">
	  <div class="modal-dialog modal-sm" role="document">
	    <div class="modal-content">
	      <div class="modal-body text-center">
	        <div class="css_loader"></div>
	        <div clas="loader-txt">
	          <p>Envoi de la facture en cours, veuillez patienter...</p>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>	

	<div class="form-group">

		<div class="row">
			<div class="col-md-offset-4 col-md-6 control-label" style="text-align: left"><h4>Ajouter une facture</h4></div>
		</div>


		<div class="row">
			<label class="col-md-4  control-label" for="pdf_fournisseur">PDF</label>
			<div class="col-md-6">
				<input type="file" required="required" name="pdf_fournisseur" id="pdf_fournisseur" class="form-control">
			</div>
		</div>

		<div class="row" style="margin-top:5px">
			<label class="col-md-4  control-label" for="date">Date de facture</label>
			<div class="col-md-6">
				<input type="date" required="required" name="date" id="date" class="form-control" placeholder="YYYY-mm-dd">
			</div>
		</div>
		
		<div class="row" style="margin-top:5px">
			<label class="col-md-4  control-label" for="montant_document_ht">Montant HT</label>
			<div class="col-md-6">
				<input type="number" required="required" name="montant_document_ht" id="montant_document_ht" class="form-control" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
			</div>
		</div>
		
		<div class="row" style="margin-top:5px">
			<label class="col-md-4  control-label" for="reference_fournisseur">Référence Facture</label>
			<div class="col-md-6">
				<input type="text" name="reference_fournisseur" id="reference_fournisseur" class="form-control">
			</div>
		</div>

	</div>

	<div class="row">
		<div class="form-group">
			<div class="col-md-6 col-md-offset-4">
				<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
					Valider
				</button>
			</div>
		</div>
	</div>
	
	<!-- liste des factures transmises -->
	@if(!empty($factures_enregistreees))
		<hr>
		<div class="row">
			<div class="col-sm-12">
				<h3 class="text-center">Factures transmises</h3> <br>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-2"><b></b></div>
			<div class="col-sm-3"><b>Date</b></div>
			<div class="col-sm-3"><b>Référence</b></div>
			<div class="col-sm-3"><b>Statut</b></div>
		</div>
		
		@foreach($factures_enregistreees as $facture) 
			<div class="row">
				<div class="col-sm-2"><b></b></div>
				<div class="col-sm-3">{{ formate_date('d/m/Y',$facture->date) }}</div>
				<div class="col-sm-3">{{ $facture->reference_document }} / {{ $facture->reference_fournisseur }}</div>
				<div class="col-sm-3">
					@if($facture->regle == 1)
						<span class="label label-success">Réglée</span>
					@elseif($facture->validee_finance == 1)
						<span class="label label-warning">En attente de paiement</span>
					@else
						<span class="label label-default">En attente de validation</span>
					@endif
				</div>
			</div>
		@endforeach
	@endif
	
</form>

@endsection

@section('scripts')
	<script type="text/javascript">

		$( "#ajout_facture_form" ).on( "submit", function( event ) {

			event.preventDefault();

			loading(true);

			$('.alert-success').hide();
			$('.alert-danger').hide();

			var file_data = $('#pdf_fournisseur').prop('files')[0];   
			var form_data = new FormData();                  

			$("#ajout_facture_form input").each(function(){
				form_data.append(this.name, this.value);
			});

			form_data.append('pdf_fournisseur', file_data);

			$.ajax({
			    type: "POST",
			    url: "{{ route('facture_achat_enregistre_interface_fournisseur', [$fournisseur->modele->id, $fournisseur->genere_clef_interface_fournisseur($fournisseur->modele)]) }}",
			    data: form_data,
			    processData: false,
				contentType: false,
			    dataType: "json",
			    success: function(data) {

			    	loading(false);

			        if (data.retour === true) {
			        	$('.alert-success').html("Facture ajoutée !").show('fast');

						$("input").val("");			        	
			        } else {

			        	$('.alert-danger').html(data.retour).show('fast');
			        }
			    }
			});

		});


		function loading(afficher) {
			if (afficher) {
				$("#modalLoading").modal({
				  backdrop: "static", //remove ability to close modal with click
				  keyboard: false, //remove option to close with keyboard
				  show: true //Display loader!
				});
			} else {
				$("#modalLoading").modal('hide');
			}
		}

	</script>

	<style type="text/css">

		.css_loader {
		  position: relative;
		  text-align: center;
		  margin: 15px auto 35px auto;
		  z-index: 9999;
		  display: block;
		  width: 80px;
		  height: 80px;
		  border: 10px solid rgba(0, 0, 0, .3);
		  border-radius: 50%;
		  border-top-color: #000;
		  animation: spin 1s ease-in-out infinite;
		  -webkit-animation: spin 1s ease-in-out infinite;
		}

		@keyframes spin {
		  to {
		    -webkit-transform: rotate(360deg);
		  }
		}

		@-webkit-keyframes spin {
		  to {
		    -webkit-transform: rotate(360deg);
		  }
		}
	</style>
@endsection