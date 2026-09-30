<template v-if="modale_{!! $action !!}">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modale_action_en_masse" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @traduction('interface.listes.{{$action}}')
                        </h5>
                        <button type="button"  class="close" @click="modale_{!! $action !!} = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body css_form">
                        @yield('texte_modale_'.$action)
                        @yield('post_bouton_modale')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_{!! $action !!} = false">@traduction('interface.listes.fermer')</button>
                        <template v-if="{{$condition_affichage_bouton ?? true}}">
                            <button class="btn btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="{{$action}}_elements_selectionnes">
                                @traduction('interface.listes.{{$action}}.action_elements_selectionnes')
                                (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)
                            </button>

                            @if(empty($desactiver_lignes_liste))
                                <button class="btn btn-primary" @click="{{$action}}_tous_elements">
                                    @traduction('interface.listes.{{$action}}.action_tous_elements')
                                </button>
                            @endif
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')
    modale_{!! $action !!} : false,
@endpush
