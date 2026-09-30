@extends('eden::intranet.modules.base_module')

@php
    $nom_formulaire = !empty($nom_formulaire) ? $nom_formulaire : 'intranet_'.$type_element;
    $management_champ = management($type_element)->champ($champ_utilisateur);
@endphp

@section('footer_boutons_'.$id)
    <span class="footer_boutons">
        <div class="back" @click="enregistrer_formulaire('{{$id}}','{{$nom_formulaire}}','{{$champ_utilisateur}}',{{$management_champ->modele->type}})">
            <span class="glyphicon glyphicon-floppy-disk"></span>
            <b>@traduction('interface.intranet.enregistrer')</b>
        </div>
    </div>
@endsection

@section('contenu_'.$id)

    <div class="formulaire_intranet">
        <dl class="dl-horizontal">
            <dt><b>{!! management($type_element)->champ($champ_utilisateur)->nom_vue()!!}</b></dt>
            <dd class="coupee">
                <span style="color:white;font-size: 16px;line-height: 35px;" 
                    v-if="$refs.formulaire_{{$id}} && $refs.formulaire_{{$id}}.element.{{$champ_utilisateur}}">
                    @if($management_champ->modele->type == 10)
                        {!! $management_champ
                            ->vmodel(true,'$refs.formulaire_'.$id.'.element')
                            ->attr('valeur_obligatoire',moi()->id)
                            ->cree() !!}
                    @else
                        {!! $management_champ->affiche(moi()->id)!!}
                    @endif
                </span>
            </dd>
        </dl>

        <formulaire ref="formulaire_{{$id}}" nom_formulaire="{{$nom_formulaire}}"></formulaire>
    </div>
@endsection

@push('donnees_pour_vuejs_mounted')
    @if($management_champ->modele->type == 10)
        this.$on('affichage_formulaire_{{$id}}',(nom_formulaire) => {
            if(!this.$refs.formulaire_{{$id}}.element.{{$champ_utilisateur}}.includes(this.$root.moi.id)){
                this.$refs.formulaire_{{$id}}.element.{{$champ_utilisateur}}.push(this.$root.moi.id);
            }
        });
    @endif
@endpush
