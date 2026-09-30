<div class="row" style="margin-bottom: 10px;">
	<div class="col-md-4 mb-3" v-for="parametre in orderedParametres">
		<div class="d-flex align-items-center">			
			<span class="fa fa-building" style="font-size: 55px; color: {{ maquette('background_menus') }}; float: left; margin-right: 5px;"></span>
			<div>
				<a :href="'eden/fiche/entite/'+parametre.id+'/afficher'"><b><big># @{{ parametre.id }} : @{{ parametre.nom }}</big></b></a><br/>
				@{{ parametre.adresse }}, @{{ parametre.code_postal }} @{{ parametre.ville }}<br/>
				@{{ parametre.numero_telephone }}, @{{ parametre.adresse_email }}<br/>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	parametres: {!! $parametres !!},
@endpush

@push('donnees_pour_vuejs_computed')
	orderedParametres: function () {
	return _.orderBy(this.parametres, 'nom')
	},
@endpush