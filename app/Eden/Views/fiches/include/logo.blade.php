<div class="css_img_utilisateur" >
     <img @click="modifier_logo" :src="logo_a_afficher" alt="">
</div>
@push('modales')
    <template v-if="modal_modification_logo">
        <transition name="modal">
            <div id="modal_modifier_logo" class="modal-mask">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                @traduction('interface.fiche.modal_modifier_logo.titre')
                            </h5>
                            <button @click="modal_modification_logo = false" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    {!! $champ_logo->nom_vue() !!}
                                </div>
                                <div class="col-md-6">
                                    {!! $champ_logo->cree() !!}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modal_modification_logo = false">@traduction('interface.modales.fermer')</button>

                            <button type="button" class="btn btn-primary" @click="enregistrer_logo">@traduction('interface.modales.enregistrer')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modal_modification_logo: false,
@endpush

@push('donnees_pour_vuejs_computed')
    logo_a_afficher : function() {

        if(this.{{$type_element}}.{{$champ_logo->modele->nom_sql}} == '' || this.{{$type_element}}.{{$champ_logo->modele->nom_sql}} == null) {

            return 'eden/images/no_avatar.jpg';
        }

        if(
            this.{{$type_element}}.{{$champ_logo->modele->nom_sql}}.indexOf('http://') > -1
            || this.{{$type_element}}.{{$champ_logo->modele->nom_sql}}.indexOf('https://') > -1
        )
            return this.{{$type_element}}.{{$champ_logo->modele->nom_sql}};

        return 'storage/' + this.{{$type_element}}.{{$champ_logo->modele->nom_sql}};
    },

@endpush
@push('donnees_pour_vuejs_methods')

    modifier_logo : function() {
            
        this.modal_modification_logo = true;
    },

    enregistrer_logo : () => {

        $.ajax({
            
			method: 'POST',
			url: '{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id], false)}}',
			dataType: "json",
			data: {
                {{$champ_logo->modele->nom_sql}} : vue_instance.{{$type_element}}.{{$champ_logo->modele->nom_sql}}
            }

		}).done(async (donnees) => {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

            info("{{traduction('module_sur_fiche.logo.enregistrement_ok')}}");
            this.modal_modification_logo = false;

		});
    },

@endpush
