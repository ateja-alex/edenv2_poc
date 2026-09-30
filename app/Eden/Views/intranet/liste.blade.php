<section v-show="bloc_affiche == '{{$id}}'">
    @if(isset($module_intranet['liste']))
        @include('eden::listes.includes.liste', [

            'type_element' => $type_element,
            'id_liste' => $module_intranet['liste'],
            'modele_par_defaut' => modele_par_defaut($type_element),
        ])
    @else
        <span class="intranet_liste_indisponible">
            <i class="fas fa-ban"></i>
            @traduction('intranet.liste.liste_indisponible')
        </span>
    @endif
</section>