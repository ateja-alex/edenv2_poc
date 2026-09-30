<div style="position:relative">
    <div style="position: absolute;top: -10px;left: -10px;z-index: 500" @click="contenu_principale = true;triggers = false">
        <div class="bulle_option css_pointer">
            <i class="fas fa-chevron-left"></i>
        </div>
    </div>
    @php
        $id_liste_trigger = \App\Eden\Models\Liste_libre::where('type_element','trigger_eden')->first()->id;
    @endphp
    <liste-libre-{{$id_liste_trigger}}
        ref="liste_libre_{{$id_liste_trigger}}"

        :filtres_pour_fiche="{'type_element_id' : {{$table_libre->id}}}"
        :modele_par_defaut="modele_par_defaut_trigger"

        :mode_parametrage=1
    >
    </liste-libre-{{$id_liste_trigger}}>
</div>

@push('composants_vue')
    <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste_trigger.'.js') }}"></script>
@endpush

@push('donnees_pour_vuejs_data')

    modele_par_defaut_trigger : {!! modele_par_defaut('trigger_eden') !!},
@endpush

@push('donnees_pour_vuejs_mounted')
    this.modele_par_defaut_trigger.type_element_id = {{$table_libre->id}};
@endpush