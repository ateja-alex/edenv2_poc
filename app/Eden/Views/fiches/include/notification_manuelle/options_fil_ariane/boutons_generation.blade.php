<i @click="generer_notifications_manuelles" class="css_action_icon primaire fa fa-refresh" title="{{traduction('module_sur_fiche.fiche.notification_manuelle.generer_les_notifications')}}" data-placement="left" data-toggle="tooltip"></i>
<i @click="envoyer_notifications_manuelles" class="css_action_icon primaire fas fa-paper-plane" title="{{traduction('module_sur_fiche.fiche.notification_manuelle.envoyer_les_notifications')}}" data-placement="left" data-toggle="tooltip"></i>

@php
    $id_liste_libre = $listes_sur_fiche['unitaire']['fiche_notification_manuelle_notification_manuelle_element']['liste_libre']['id'] ?? 0;
@endphp

@push('donnees_pour_vuejs_methods')

    generer_notifications_manuelles(){

        var component = this;

        $.ajax({
            url : '{{route('base_eden.fiche.index',['type_element' => 'notification_manuelle','id'=>$management_element->modele->id,'methode'=>'generer_notifications_manuelles'])}}',
            dataType:'json',
        }).done(function(donnees){

            if(donnees.retour == true){

                info("{{traduction('module_sur_fiche.fiche.notification_manuelle.generer_ok')}}");

                @if($id_liste_libre != 0)
                    component.$refs['liste_libre_{{$id_liste_libre}}'].actualisation_filtres();
                @endif
            }
        });
    },

    envoyer_notifications_manuelles(){

        var component = this;

        $.ajax({
            url : '{{route('base_eden.fiche.index',['type_element' => 'notification_manuelle','id'=>$management_element->modele->id,'methode'=>'envoyer_notifications_manuelles'])}}',
            dataType:'json',
        }).done(function(donnees){

            if(donnees.retour == true)
                info("{{traduction('module_sur_fiche.fiche.notification_manuelle.envois_ok')}}");
            else
                toastr.error(donnees.retour);

                @if($id_liste_libre != 0)
                    component.$refs['liste_libre_{{$id_liste_libre}}'].actualisation_filtres();
                @endif
        });
    },
@endpush