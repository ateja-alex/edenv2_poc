<span>
	<i class="css_action_icon primaire fas fa-fw fa-paper-plane" @click="$refs.gestion_cycle_de_vie_facturation.ouvrir(type_element+'_id', document.id)"
	   :title="traduction('interface.listes.poser_statut_cycle_de_vie')"
	   data-toggle="tooltip"></i>
</span>

@push('modales')
<gestion-cycle-de-vie-facturation ref="gestion_cycle_de_vie_facturation"></gestion-cycle-de-vie-facturation>
@endpush
