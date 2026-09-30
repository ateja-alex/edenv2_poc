<a
   v-if="![209, 210, 211, 212, 213, 501].includes(Number(ligne.element.statut_facturation_electronique))"
   @click="$refs.gestion_cycle_de_vie_facturation.ouvrir('facturation_electronique_achat_id', ligne.element.id)"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-paper-plane"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.poser_statut_cycle_de_vie')">
        </span>
    </span>
</a>

@push('modales')
<gestion-cycle-de-vie-facturation ref="gestion_cycle_de_vie_facturation"></gestion-cycle-de-vie-facturation>
@endpush
