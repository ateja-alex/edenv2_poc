@extends('eden::templates.template')

@section('title') Licence @stop

@section('content')

<div class="content-wrapper" >
    <div id="base-content" class="container-fluid">
        @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                array('nom' => 'Licence')
            )])
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center header_licence">
                        <h4> Vos licences </h4>
                        @if(editeur())
                            <div>
                                <span class="css_ajouter_element css__lien" @click="modale_licence = true" data-toggle="tooltip" data-placement="top" title="Ajouter une licence">
                                    <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="groupe_licences">
                            <div v-for="(licence,index_licence) in licences" :key="index_licence">
                                <div class="bloc_licence">
                                    <div class="image_licence">
                                        <img src="{{asset("eden/images/image-licence.png")}}">
                                    </div>
                                    <div class="nom_licence" v-html="licence.nom"></div>
                                    <div ref="contenu_licence" class="contenu_licence" :style="'max-height:'+(elements_deployes.includes(licence.id) ? 'unset' : '100px')+';'">
                                        <div class="ensemble_licence" v-for="ensemble in licence.ensembles" >
                                            <i class="fas fa-check"></i>
                                            <span v-html="ensemble.nom"></span>
                                        </div>
                                        <div v-if="mounted && parseInt($refs.contenu_licence[index_licence].scrollHeight) > 100" class="contenu_licence_bouton_deploiement" @click="elements_deployes.includes(licence.id) ? elements_deployes.splice(elements_deployes.indexOf(licence.id),1) : elements_deployes.push(licence.id)">
                                            <i :class="'fas fa-'+(elements_deployes.includes(licence.id) ? 'arrow-up' : 'ellipsis-h')"></i>
                                        </div>
                                    </div>
                                    @if(editeur())
                                        <a target="_blank" :href="'{{URL::to('eden/fiche/licence')}}/'+licence.id" class="contenu_licence_parametrage">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                    @endif
                                    @if(!env('BASE_MODELE'))
                                        <div class="contenu_licence_utilisation">
                                            @{{ licence.nombre_utilises == null ? 0 : licence.nombre_utilises }} / @{{ licence.nombre == null ? 0 : licence.nombre }} licences déjà utilisées
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template v-if="modale_licence">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" >Informations principales de la licence</h5>
                    </div>
                    <div class="modal-body css_form js_selection_element" >
                        <formulaire ref="formulaire" nom_formulaire="licence"></formulaire>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_licence = false">@traduction('interface.modales.fermer')</button>
                        <div class="btn btn-primary" @click="enregistrer_licence()">@traduction('interface.modales.enregistrer')</div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@endsection

@push('donnees_pour_vuejs_data')

    licences : {!! collect($licences) !!},
    elements_deployes : [],
    modale_licence : false,
    mounted : false,
@endpush

@push('donnees_pour_vuejs_mounted')

    this.mounted = true;

@endpush

@push('donnees_pour_vuejs_methods')

    enregistrer_licence : async function(){

        var vue_composant = this;

        // On afficher le loader
        loading(true);

        var donnees = await vue_composant.$refs.formulaire.enregistrer();

        loading(false);

        if(donnees.retour === true)
            document.location = '{{URL::to('eden/fiche/licence')}}/'+donnees.element.id;
    },
@endpush

