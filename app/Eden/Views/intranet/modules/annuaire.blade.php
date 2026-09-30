@extends('eden::intranet.modules.base_module')

@section('contenu_'.$id)
	<table class="consultEmploye" style="overflow:scroll;">
		<thead>
		<th><b>@traduction('interface.intranet.nom_prenoms')</b></th>
		<th><b>@traduction('interface.intranet.telephone')</b></th>
		<th><b>@traduction('interface.intranet.email')</b></th>
		</thead>
		<tbody>
			<tr v-for="employe in employes_annuaires" score="row">
				<td v-html="employe.nom+' '+employe.prenom"></td>
				<td v-html="employe.telephone"></td>
				<td v-html="employe.email"></td>
			</tr>
		</tbody>
	</table>
	<br><br>
@endsection

@push('donnees_pour_vuejs_data')
	employes_annuaires : [],
@endpush

@push('donnees_pour_vuejs_mounted')

	this.recuperer_employes_annuaires();
@endpush

@push('donnees_pour_vuejs_methods')

	recuperer_employes_annuaires : function(){

		var vue_instance = this;

        $.get({

            url: "{{URL::to('eden/intranet/employes_annuaires')}}",
            dataType: "json",
        }).done(function(donnees) {

            vue_instance.employes_annuaires = donnees.employes_annuaires;

        });
	},

@endpush