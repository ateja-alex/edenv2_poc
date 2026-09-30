<a v-if="liste.modele_liste_libre.id_rapport == 'rapprochement_facture_achat'"
   @click="choisir_facture_achat_pour_rapprochement(ligne.element.id)"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-primary css_icone_option_dans_liste">
        <span class="fas fa-link"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.choisir')">
        </span>
    </span>
</a>

@push('donnees_pour_vuejs_methods')

    choisir_facture_achat_pour_rapprochement : async function(id_facture_achat){

        loading(true);

        var url = "{{ route('base_eden.element.enregistrer', ['facturation_electronique_achat', '__ID__'], false) }}"
            .replace('__ID__', this.$root.id_facturation_electronique_achat_a_rapprocher);

        $.post({
            url: url,
            data: { facture_achat_id: id_facture_achat },
            dataType: 'json',
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true){
                await erreur(donnees.retour);
                return;
            }

            this.$root.$emit('rapprochement_facture_achat_effectue');
        }).fail(() => loading(false));
    },
@endpush
