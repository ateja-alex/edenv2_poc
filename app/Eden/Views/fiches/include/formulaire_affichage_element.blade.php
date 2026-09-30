<formulaire-fiche :route="''" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}' ref="formulaire_affichage_element" @if(admin() && mode_parametrage() === true && isset($type_element) && table_libre($type_element) !== null) :mode_parametrage=1 @endif>
    <template slot="titre">
        <?php
        // temps_execution('formulaire_edition_element header : debut');
        ?>

        {!! $management_element->affiche() !!}

        <?php
        // temps_execution('formulaire_edition_element header : fin');
        ?>
    </template>
</formulaire-fiche>

@push('donnees_pour_vuejs_data')
    @if(isset(${$management_element->_type_element}))
        {{ $management_element->_type_element }}: {!! ${$management_element->_type_element} !!},
    @else
        {{ $management_element->_type_element }}: {!! $management_element->modele !!},
    @endif
@endpush
