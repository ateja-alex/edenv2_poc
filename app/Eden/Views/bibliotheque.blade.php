@extends('eden::templates.template')

@section('title')
    Bibliothèque
@endsection

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

            @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                    array('nom' => traduction('interface.bibliotheque.titre'))
                )])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header css_flex_header_liste">
							<h4>
                                @traduction('interface.bibliotheque.titre')
							</h4>
                            @include('eden::fiches.include.bibliotheque.boutons')
						</div>
						@include('eden::fiches.include.bibliotheque.fil_ariane')
						<div class="card-body droppable" id="drop_zone" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" ondragleave="dragLeaveHandler(event);">
							<div class="row">

                                @include('eden::fiches.include.bibliotheque.retour')
                                @include('eden::fiches.include.bibliotheque.dossiers')
                                @include('eden::fiches.include.bibliotheque.fichiers')

							</div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>

    @include('eden::fiches.include.bibliotheque.modal_previsualisation_fichier')

    <!-- Modal ajout dossier -->
    <div class="modal fade" id="modal_gestion_dossier" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@traduction('interface.bibliotheque.modale.titre')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body css_form">
                    <div class="row">
                        <div class="col-sm-12 css_form_ligne_titre">@traduction('interface.bibliotheque.modale.titre_categorie.information_generale')</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('champs_libres.dossier_bibliotheque.nom.nom')</div>
                        <div class="col-sm-10"><input type="text" v-model="dossier.nom" /></div>
                    </div>
                    <div class="row" v-if="!dossier_parent || !dossier_parent.gdrive">
                        <div class="col-sm-2">@traduction('champs_libres.dossier_bibliotheque.confidentiel.nom')</div>
                        <div class="col-sm-10">
                            <label class="switch"><input type="checkbox" v-model="dossier.confidentiel"  ><span class="slider round"></span></label>
                        </div>
                    </div>
                    @if(fonctionnalite('gestion_droit_entites_repertoire') && admin())
                        <div class="row">
                            <div class="col-sm-2">@traduction('champs_libres.dossier_bibliotheque.droit_entites.nom')</div>
                            <div class="col-sm-10">
                                <div>
                                    @foreach(modele('entite')->get() as $entite)
                                        <label style="margin-right: 20px;" >
                                            <input type="checkbox" v-model="dossier.droit_entites[{{$entite->id}}]" name="droit_entites[]" style="display:none;"/>
                                            <div class="badge" :class="{'badge-success': dossier.droit_entites[{{$entite->id}}] === true, 'badge-default': dossier.droit_entites[{{$entite->id}}] === false}">
                                                {{$entite->nom}}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="dossier.id !=null ? modifier_dossier() : enregistrer_dossier()" >@traduction('interface.bibliotheque.modale.bouton_enregistrer')</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.bibliotheque.modale.bouton_annuler')</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
	<script>

        $("body").on("click", ".js_dossier_bibliotheque", function () {

            vue_instance.deplacement_dans_un_dossier($(this).attr('id_dossier'));
        });

        function envoi_fichier_dans_bibliotheque(file) {

            var formData = new FormData();

            vue_instance.fichiers_attente.push({});

            formData.append('image', file, file.name);

            formData.append('dossier_parent', JSON.stringify(vue_instance.dossier_parent));

            var xhr = new XMLHttpRequest();

            xhr.open('POST', "{{ route('bibliotheque.ajouter_fichier') }}", true);

            // Set up a handler for when the request finishes.
            xhr.onload = async function (resultat) {

                reponse=JSON.parse(resultat.currentTarget.response);

                if (xhr.status === 201 && typeof reponse.retour == 'undefined') {

                    vue_instance.fichiers.push(JSON.parse(resultat.currentTarget.response));

                    vue_instance.fichiers_attente.pop();
                }
                else if(typeof reponse.retour!== 'undefined') {

                    vue_instance.fichiers_attente.pop();

                    await alerte_eden(reponse.retour);
                }
                else{

                    toastr.error('An error occurred!');
                }
            };

            xhr.send(formData);
        }

        function dropHandler(ev) {

            if(vue_instance.moi_extranet !== null)
                return;

            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();

            if (ev.dataTransfer.items) {

                // Use DataTransferItemList interface to access the file(s)
                for (var i = 0; i < ev.dataTransfer.items.length; i++) {

                    // If dropped items aren't files, reject them
                    if (ev.dataTransfer.items[i].kind === 'file') {

                        var file = ev.dataTransfer.items[i].getAsFile();
                        envoi_fichier_dans_bibliotheque(file);
                    }
                }
            } else if(ev.dataTransfer.files.length) {

                // Use DataTransfer interface to access the file(s)
                for (var i = 0; i < ev.dataTransfer.files.length; i++) {

                    var file = ev.dataTransfer.files[i].getAsFile();
                    envoi_fichier_dans_bibliotheque(file);
                }
            }

            // Pass event to removeDragData for cleanup
            removeDragData(ev)
        }

        function dragOverHandler(ev) {

            if(vue_instance.moi_extranet !== null)
                return;

            $('#drop_zone').css('border', "2px dashed red");

            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
        }

        function dragLeaveHandler(ev) {

            if(vue_instance.moi_extranet !== null)
                return;

            $('#drop_zone').css('border', "2px dashed rgb(142, 142, 142)");

            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
        }

        function removeDragData(ev) {

            if(vue_instance.moi_extranet !== null)
                return;

            if (ev.dataTransfer.items) {
                // Use DataTransferItemList interface to remove the drag data
                ev.dataTransfer.items.clear();
            } else {
                // Use DataTransfer interface to remove the drag data
                ev.dataTransfer.clearData();
            }

            $('#drop_zone').css('border', "");
        }

        function creation_chemin(dossier){

            if(dossier != null) {

                creation_chemin(dossier.dossier_parent);

                document.getElementById("chemin_accces").innerHTML +='/ <span class="css__lien js_dossier_bibliotheque" id_dossier="'+dossier.id+'" id="lien_chemin_dossier_'+dossier.id+'" >'+dossier.nom+'</span> ';

            }

        }

        $('#input_fichier').on('change', function (event){

            input_file = document.getElementById('input_fichier');

            for (var i = 0; i < input_file.files.length; i++) {

                var file = input_file.files[i];

                envoi_fichier_dans_bibliotheque(file);

            }

        });

	</script>
@endsection

@push('donnees_pour_vuejs_data')
    fichiers: {!! $fichiers !!},
    fichiers_attente: [],
    dossiers: {!! $dossiers !!},
    dossier: {
        droit_entites : {
            @foreach(modele('entite')->get()->pluck('id') as $id)
               {{ $id }} : false,
            @endforeach
        },
    },
    dossier_parent: {},
    element_piece_jointe: false,
    element_affichage_options : null,
@endpush

@push('donnees_pour_vuejs_methods')

    dispo_extranet: function(valeur,type_element, id_element, index){

        $.post({

        url: "{{ URL::to('eden/element') }}/"+ type_element + "/" + id_element + "/enregistrer",
        dataType: "json",
        method: "post",
        data: {disponible_extranet: valeur}
        }).done(async (donnees) => {

            if(donnees.retour !== true) {

                await toastr.error(donnees.retour);
                return;
            }

            if(valeur)
                toastr.success(this.$root.traduction('messages.js.bibliotheque.element_dispo_extranet'));
            else
                toastr.success(this.$root.traduction('messages.js.bibliotheque.element_non_dispo_extranet'));

            if(type_element == 'fichier_bibliotheque')
                this.fichiers[index] = donnees.element;
            else
                this.dossiers[index] = donnees.element;
        });
    },

    modal_gestion_dossier: function(dossier) {

        this.dossier = dossier;
        $('#modal_gestion_dossier').modal('show');
    },

    enregistrer_dossier() {

        loading(true);

        var vue_app = this;
        var dossier  = this.dossier;
        dossier.dossier_parent = vue_app.dossier_parent;

        if(dossier.confidentiel == true)
            dossier.confidentiel = 1;
        else
            dossier.confidentiel = 0;

        dossier.creation_dossier_confidentiel = dossier.confidentiel;

        // on enregistre les infos du champ libre
        $.post({

        url: "{{ route('bibliotheque.ajouter_dossier') }}",
        dataType: "json",
        data:	{
            'dossier': dossier,
        }
        }).done(async function(donnees) {

        loading(false);

        if(donnees.retour !== true) {

        await erreur(donnees.retour);
        return;
        }

        $('#modal_gestion_dossier').modal('hide');

        vue_app.dossiers= donnees.dossiers;
        });

    },

    modifier_dossier(){

        loading(true);

        var vue_app = this;
        var dossier  = this.dossier;

        if(dossier.confidentiel == true)
            dossier.confidentiel = 1;
        else
            dossier.confidentiel = 0;

        dossier.creation_dossier_confidentiel = dossier.confidentiel;

        // on enregistre les infos du champ libre
        $.post({

        url: "{{ route('bibliotheque.modifier_dossier') }}",
        dataType: "json",
        data:	{
        'dossier': dossier,
        }
        }).done(async function(donnees) {

        loading(false);

        if(donnees.retour !== true) {

        await erreur(donnees.retour);
        return;
        }

        $('#modal_gestion_dossier').modal('hide');

        vue_app.dossiers= donnees.dossiers;

        vue_app.reinitialisation_chemin();

        });
    },

    deplacement_dans_un_dossier : function (id_dossier){

        vue_app = this;

        dossier_parent = (
                                vue_instance.dossier_parent &&
                                vue_instance.dossier_parent.dossier_parent &&
                                vue_instance.dossier_parent.dossier_parent.id == id_dossier
                            ?
                                vue_instance.dossier_parent.dossier_parent.dossier_parent
                            :
                                vue_instance.dossier_parent
                         );

        loading(true);


        if(id_dossier == null){

            id_dossier = 0
        }


        $.post({

            url: "{{ URL::to('/eden/bibliotheque/recuperer_bibliotheque_parametrer') }}",
            dataType: "json",
            data:	{
            'dossier_parent_id': id_dossier,
            'dossier_parent': dossier_parent,
            }
            }).done(async function(donnees) {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            vue_app.dossiers = donnees.dossiers;

            vue_app.fichiers = donnees.fichiers;

            vue_app.dossier_parent = donnees.nouveau_dossier_parent;

            vue_app.reinitialisation_chemin();

            loading(false);

            return false;

        });

    },

    supprimer_dossier: async function(dossier){

        if(await confirm_eden(vue_instance.traduction('Êtes-vous certain, cela entrainera la suppression de tous les dossiers et fichiers contenus dans ce dossier ?'))){

            vue_app = this;

            // on enregistre les infos du champ libre
            $.ajax({

            url: "{{ URL::to('/eden/bibliotheque/dossier') }}/"+dossier.id+"/supprimer",
            dataType: "json"
            }).done(async function(donnees) {

            if(donnees.retour !== true) {

            await erreur(donnees.retour);
            return;
            }

            if(vue_app.dossier_parent.dossier_parent!=null){

                vue_app.deplacement_dans_un_dossier(vue_app.dossier_parent.dossier_parent.id);
            }
            else{
                vue_app.deplacement_dans_un_dossier(null);
            }

            return false;

            });
        }
    },

    reinitialisation_chemin(){

        vue_app =  this;

        document.getElementById("chemin_accces").innerHTML = null;

        creation_chemin(vue_app.dossier_parent);

    },

    deplacer_element_dans_un_dossier: function(id_element,type_element,id_nouveau_dossier_parent){

        vue_app=this;

        // on enregistre les infos du champ libre
        $.post({
        url: "{{ route('bibliotheque.deplacer_element_dans_un_dossier') }}",
        dataType: "json",
        data:	{
            'id_nouveau_dossier_parent': id_nouveau_dossier_parent,
            'type_element': type_element,
            'id_element' : id_element,
            'dossier_parent' : vue_app.dossier_parent,
        },
        }).done(async function(donnees) {

        if(donnees.retour !== true) {

        await erreur(donnees.retour);
        return;
        }

        if(vue_app.dossier_parent!=null){

        vue_app.deplacement_dans_un_dossier(vue_app.dossier_parent.id);
        }
        else{
        vue_app.deplacement_dans_un_dossier(null);
        }

        return false;

        });

    },


    startDrag: (evt, item, type_element) => {

        if(this.moi_extranet !== null)
            return;

        $('.droppable').css('border', '2px solid red');
        evt.dataTransfer.setData('id',item.id);
        evt.dataTransfer.setData('type_element',type_element);
    },

    endDrag(){

        if(this.moi_extranet !== null)
            return;

        $('.droppable').css('border', '');
    },

    onDrop (evt,dossier) {

        if(this.moi_extranet !== null)
            return;

        id_element = evt.dataTransfer.getData('id');
        type_element = evt.dataTransfer.getData('type_element');

        id_nouveau_dossier_parent=null;

        if(dossier!=null){

            id_nouveau_dossier_parent = dossier.id;
        }

        vue_instance.deplacer_element_dans_un_dossier(id_element,type_element,id_nouveau_dossier_parent);
    },

@endpush
