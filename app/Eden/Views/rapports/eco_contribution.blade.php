
<liste-libre-{{$rapports['rapport_eco_contribution']->id}}
        ref="liste_ec"

@if(super_admin() || mode_parametrage())
    :mode_parametrage=1
@endif
>
</liste-libre-{{$rapports['rapport_eco_contribution']->id}}>
<liste-libre-{{$rapports['rapport_facture_vente_lignes']->id}}
        ref="liste_fvl"

        @if(super_admin() || mode_parametrage())
            :mode_parametrage=1
        @endif
        >

</liste-libre-{{$rapports['rapport_facture_vente_lignes']->id}}>