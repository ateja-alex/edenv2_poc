@php
    $version_composants = parametre('version_composants');
    $rapport_id = $module;
    $cle_etrangere = $rapports_sur_fiche[$module]->cle_etrangere;
    $cle_primaire = $rapports_sur_fiche[$module]->cle_primaire;
    $v_model = $rapports_sur_fiche[$module]->type_element_fiche;
    $modele_par_defaut = modele_par_defaut($rapports_sur_fiche[$module]->type_element);
@endphp
@if($rapports_sur_fiche[$module]->type_rapport == 'liste_libre' && !empty($rapports_sur_fiche[$module]->kanban))

    <kanban-libre
        :id_liste="{{ $rapports_sur_fiche[$module]->rapport_liste_libre->id }}"
        ref="kanban_libre_{{ $rapports_sur_fiche[$module]->rapport_liste_libre->id }}"

        :session="{}"
        :seulement_inactif="0"
        kanban="{{ $rapports_sur_fiche[$module]->kanban }}"

        :filtres_pour_fiche="filtres_pour_fiche_{{ $rapport_id }}"

        :modele_par_defaut="modele_par_defaut_{{ $rapport_id }}"

        @if(super_admin() || mode_parametrage())
            :mode_parametrage=1
        @endif
    >
    </kanban-libre>

@elseif($rapports_sur_fiche[$module]->type_rapport == 'liste_libre')
    @push('composants_vue')
        <script type="text/javascript" src="{{ '/storage/composants/liste_libre_'.$rapports_sur_fiche[$module]->rapport_liste_libre->id.'.js' }}?version={{$version_composants}}"></script>
    @endpush

    <liste-libre-{{ $rapports_sur_fiche[$module]->rapport_liste_libre->id }}
        ref="liste_libre_{{ $rapports_sur_fiche[$module]->rapport_liste_libre->id }}"

        :session="{}"
        :seulement_inactif="0"

        :filtres_pour_fiche="filtres_pour_fiche_{{ $rapport_id }}"

        :modele_par_defaut="modele_par_defaut_{{ $rapport_id }}"

        @if(super_admin() || mode_parametrage())
            :mode_parametrage=1
        @endif
    >
    </liste-libre-{{ $rapports_sur_fiche[$module]->rapport_liste_libre->id }}>
@else
<rapport
    id_rapport="{{$module}}"
    :filtres_pour_fiche="filtres_pour_fiche_{{ $rapport_id }}"
></rapport>
@endif

@push('donnees_pour_vuejs_data')

    filtres_pour_fiche_{{ $rapport_id }}: {},
    modele_par_defaut_{{ $rapport_id }}: {!! collect($modele_par_defaut) !!},

@endpush

@push('donnees_pour_vuejs_created')

    this.$set(this.modele_par_defaut_{{ $rapport_id }}, '{{ $cle_etrangere }}', this.{{ $v_model }}.{{ $cle_primaire }});

    this.$set(this.filtres_pour_fiche_{{ $rapport_id }}, '{{ $cle_etrangere }}', this.{{ $v_model }}.{{ $cle_primaire }})

@endpush
