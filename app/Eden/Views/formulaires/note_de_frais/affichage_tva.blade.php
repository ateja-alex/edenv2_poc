<template v-if="note_de_frais.id != null && note_de_frais.id != ''">
    <div class="row" v-for="tva in note_de_frais_tva">
        <div class="col-sm-4">
            @{{ tva.nom }}
        </div>
        <div class="col-sm-8">
            <input type="number" disabled v-model="tva.montant" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')
	note_de_frais_tva : {},
@endpush

@push('donnees_pour_vuejs_mounted')
    var composant = this;

	if(composant.note_de_frais.id != null && composant.note_de_frais.id != ''){

		$.ajax({

            url: "{{ URL::to('/eden/note_de_frais/') }}/"+composant.note_de_frais.id+"/valeurs_tva",
            dataType: "json"
            }).done(function(donnees) {

            composant.note_de_frais_tva = donnees.taux_de_tva;

		});
	}
@endpush