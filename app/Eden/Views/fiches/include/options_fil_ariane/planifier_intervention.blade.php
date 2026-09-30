<!-- planifier une tache -->
<a :href="url_calendrier"><i class="css_action_icon primaire fas fa-calendar-plus" title="{{traduction('module_sur_fiche.fiche.projet.planifier_intervention')}}" data-toggle="tooltip"></i></a>

@push('donnees_pour_vuejs_computed')

    url_calendrier : function(){

        var vue_composant = this;

        var ajout_url = [

            {
                'type_element' : vue_composant.$root.type_element + '_id',
                'element_id' : vue_composant.$root.element_id,
            }

        ];

        if(vue_composant.$root.type_element == 'ticket_client')
            ajout_url.push({'type_element' : 'client_id', 'element_id' : vue_composant.$root.ticket_client.client_id});

        var url = '{{ route('calendrier.afficher') }}?';

        for(const [cle, donnee] of Object.entries(ajout_url)){

            if(donnee.type_element == undefined || donnee.element_id == undefined)
                continue;

            if(cle > 0)
                url += '&';

            url += donnee.type_element + "=" + donnee.element_id;

        }

        return url;

    },

@endpush