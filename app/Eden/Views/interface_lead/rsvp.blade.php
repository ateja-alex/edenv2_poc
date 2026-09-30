@extends('eden::authentification.template')

@section('title')
Interface Lead
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
	<h3 class="text-center">Bienvenue</h3> <br>
	
	<div class="alert alert-success">{!! $texte !!}</div>
	
	@if(isset($reponse) && $reponse == 'non')
		Si vous acceptez de prendre 30 secondes pour nous dire la raison, ça serait cool !<br/><br/>
		<form action="{{ route('interface_lead.rsvp_maj_reponse') }}" method="post">
			<input type="hidden" name="lead_id" value="{{ $lead_id }}" />
			<div class="row">
				<div class="col-sm-12">
					<select name="cause" style="width: 100%; padding: 8px;">
						<option value="">Veuillez choisir</option>
						<option value="4024">Besoin inadapté</option>
						<option value="4025">Localisation</option>
						<option value="4026">Prix</option>
					</select>
				</div>
				<div class="col-sm-12"><br/>Détails :</div>
				<div class="col-sm-12"><input type="text" name="raison" placeholder="Un petit commentaire ?" style="width: 100%; padding: 6px;" /></div>
			</div>
				
			
			<div class="row">
				<div class="form-group">
					<div class="col-md-6 col-md-offset-4">
						<br/>
						<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
							Envoyer
						</button>
					</div>
				</div>
			</div>
		</form>
	@endif
	
	@if(isset($reponse) && $reponse == 'plustard')
		Pas de souci ! Nous allons revenir vers vous le mois prochain !<br/><br/>
		<b>Vous préférez définir une date pour que l'on revienne vers vous ?</b>
		<form action="{{ route('interface_lead.rsvp_maj_reponse') }}" method="post">
			<input type="hidden" name="lead_id" value="{{ $lead_id }}" />
			<div class="row">
				<div class="col-sm-12"><br/>Choisissez la date :</div>
				<div class="col-sm-12"><input type="text" class="js_datepicker" name="a_relancer_le" style="width: 100%; padding: 6px;" /></div>
			</div>
				
			
			<div class="row">
				<div class="form-group">
					<div class="col-md-6 col-md-offset-4">
						<br/>
						<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
							Envoyer
						</button>
					</div>
				</div>
			</div>
		</form>
		
		@section('scripts')
			<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js?v=0.1"></script>
			<script type="text/javascript">
			
				$(function(){


				   $('.js_datepicker').datepicker({
						format: "dd/mm/yyyy",
						language: 'fr'
					});
				
				});
			</script>
		@endsection
	@endif
</form>

@endsection