<div class="row">
    <div class="col-md-12">
        <div class="card-header mb-3">
            <div class="row">
                <div class="col-md-2"><p class="css__commentaire"><b>@traduction('document.entete.commentaires') : </b></p></div>
                <div class="col-md-10" >
                    <textarea-wysiwyg-vue gestion_mise_a_jour_valeur="Change Undo Redo" :modele="document" nom_sql="commentaire_fiche" ></textarea-wysiwyg-vue>
                </div>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_watch')

    'document.commentaire_fiche': function(){

        var vue_instance = this;

        loading(true);

        // on met à jour le commentaire
        $.post({

            url: "{{ URL::to("/eden/element/".$management->_type_element."/".$management->modele->id."/enregistrer_commentaires_fiche") }}",
            data: {

                commentaire_fiche: vue_instance.document.commentaire_fiche,
            },
            dataType: "json"
        }).done(function(donnees) {

            if(donnees.retour !== true) {

                erreur(donnees.retour);
                return;
            }

            loading(false);
        });
    },
@endpush