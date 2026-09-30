@yield('liste_annuaire_facturation')

<div v-if="synchros_annuaire_facturation.length > 0" class="mt-2 text-right">
    <template v-if="synchros_annuaire_facturation.length === 1">
        <button
            type="button"
            class="btn btn-sm btn-info"
            @click="relancer_synchro_annuaire_facturation(synchros_annuaire_facturation[0].id)">
            <i class="fa fa-sync-alt"></i> Relancer la synchronisation
        </button>
    </template>
    <template v-else>
        <div class="d-inline-flex align-items-center">
            <select v-model="synchro_annuaire_facturation_selectionnee" class="form-control form-control-sm mr-2">
                <option v-for="synchro in synchros_annuaire_facturation" :key="synchro.id" :value="synchro.id">
                    @{{ synchro.affichage_pour_recherche }}
                </option>
            </select>
            <button
                type="button"
                class="btn btn-sm btn-info"
                @click="relancer_synchro_annuaire_facturation(synchro_annuaire_facturation_selectionnee)">
                <i class="fa fa-sync-alt"></i> Relancer
            </button>
        </div>
    </template>
</div>

@push('donnees_pour_vuejs_data')
    synchros_annuaire_facturation: [],
    synchro_annuaire_facturation_selectionnee: null,
@endpush

@push('donnees_pour_vuejs_methods')
    charger_synchros_annuaire_facturation: function() {
        $.post({
            url: '{{ route("base_eden.element.recuperer_liste", "synchronisation_service_element", false) }}',
            dataType: 'json',
            data: {
                filtrage: [
                    { champ: 'type_element_destination', condition: 'where', valeur: 'annuaire_facturation' },
                    { champ: 'type_element', condition: 'where', valeur: this.type_element },
                    { champ: 'type_synchronisation', condition: 'where', valeur: 3 },
                    { champ: 'desactive', condition: 'where', valeur: 0 },
                ]
            }
        }).done((retour) => {
            this.synchros_annuaire_facturation = retour.filter(s => !s.desactive);
            if (this.synchros_annuaire_facturation.length > 0)
                this.synchro_annuaire_facturation_selectionnee = this.synchros_annuaire_facturation[0].id;
        });
    },
    relancer_synchro_annuaire_facturation: function(id) {
        loading(true);
        $.ajax({
            url: '{{ url("eden/synchronisation_service/lancer") }}/' + id,
            method: 'POST',
            dataType: 'json',
            data: {
                type_element : this.type_element,
                element_id : this.element_id,
            },
            success: (retour) => {
                loading(false);
                if (retour.succes)
                    toastr.success(this.$root.traduction('messages.php.synchronisation_service_element.synchronisation_effectuee'));
                else
                    toastr.error(retour.message || this.$root.traduction('interface.listes.erreur_inattendue_survenue'));

                Object.values(this.$refs).filter(ref => ref.type_element == 'annuaire_facturation')[0].actualisation_filtres();
            },
            error: () => {
                loading(false);
                toastr.error(this.$root.traduction('interface.listes.erreur_inattendue_survenue'));
            }
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.charger_synchros_annuaire_facturation();
@endpush
