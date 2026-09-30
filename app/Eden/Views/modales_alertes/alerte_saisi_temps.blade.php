<div class="modal fade" id="alerte_saisi_temps" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<img src="{{ asset('eden/images/main_stop.png') }}" style="width:50px; float: left; margin-right: 10px;" /> 
				@traduction('interface.modales_alertes_saisie_des_temps.heure_non_saisie')
				<br/>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
				<a href="{{ route('saisie_des_temps.index') }}" class="btn btn-primary">@traduction('interface.modales_alertes_saisie_des_temps.saisir_mes_temps')</a>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
	$(window).on('load',function(){
		$('#alerte_saisi_temps').modal('show');
	});
</script>
@endpush