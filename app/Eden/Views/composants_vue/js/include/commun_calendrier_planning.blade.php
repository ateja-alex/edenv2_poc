@push('donnees_pour_vuejs_directives')
    tooltip_tache: {
        bind: (el, binding, vnode) => {

            let composant_parent = vnode.context;

            el.clickEvent = async (event) => {

                let tache = composant_parent.recuperer_tache_dom(event.target);

                if(composant_parent.selection_tache)
                    return;

                if(tache == undefined)
                    return;

                const conteneur_tache = composant_parent.recuperer_conteneur_tache_dom(event.target, tache);
                let tooltip_sur_page = document.querySelector('.tooltip-vue-tache-container');

                if(conteneur_tache == undefined || (tooltip_sur_page != undefined && binding.value.id == tooltip_sur_page.dataset.tacheId))
                    return;

                const tooltip = document.createElement("div");
                tooltip.classList.add("tooltip-vue-tache-container");
                tooltip.setAttribute('data-tache-id', el.dataset.tacheId);

                const emplacementTache = tache.getBoundingClientRect();
                const emplacementConteneur = conteneur_tache.getBoundingClientRect();
                const emplacementCalendrier = event.target.closest(".card-body").getBoundingClientRect();

                if((emplacementConteneur.left - emplacementCalendrier.left) < 400) {
                    tooltip.style.left = (emplacementTache.right - emplacementConteneur.left + 20 ) + "px";
                    tooltip.classList.add("right");
                } else {
                    tooltip.style.right = (emplacementConteneur.right - emplacementTache.left + 20 ) + "px";
                }

                if((emplacementTache.top + (emplacementTache.height / 2)) - emplacementCalendrier.top < 200) {
                    tooltip.style.top = (emplacementTache.bottom - emplacementConteneur.top - 40) + "px";
                    tooltip.classList.add("bottom");
                } else if((emplacementTache.bottom - (emplacementTache.height / 2)) > emplacementCalendrier.bottom) {
                    tooltip.style.bottom = "calc(100% - 40px)";
                    tooltip.classList.add("top");
                } else {
                    tooltip.style.top = (emplacementTache.top - emplacementConteneur.top + emplacementTache.height / 2) + "px";
                    tooltip.style.transform = "translateY(-50%)";
                }

                conteneur_tache.appendChild(tooltip);

                tache = await composant_parent.retourne_tache_avec_details(binding.value);

                const instance_composant = new tooltip_tache({
                    parent : composant_parent,
                    $ref : 'tooltip_tache',
                    propsData: {
                        tache: tache,
                    },
                })

                const composant_monte = instance_composant.$mount();
                composant_parent.composants_tooltip[binding.value.id] = composant_monte;

                tooltip.appendChild(composant_monte.$el);
            };

            el.suppression_tooltip = (event) => {

                let tache_id = binding.value.id;
                let tooltip = composant_parent.composants_tooltip[tache_id];
                let tache_dom = el;

                if(!tooltip || tooltip.$el.contains(event.target) || tache_dom.contains(event.target))
                    return;

                container_tooltip = document.querySelector('.tooltip-vue-tache-container[data-tache-id="' + tache_id + '"]');

                tooltip.$destroy();
                delete composant_parent.composants_tooltip[tache_id];
                container_tooltip.remove();
            };

            el.addEventListener('click', el.clickEvent);
            document.addEventListener('click', el.suppression_tooltip)
        },
        unbind: function (el) {

            document.removeEventListener('click', el.suppression_tooltip)
            el.removeEventListener('click', el.clickEvent)
        },
    },
@endpush

@push('donnees_pour_vuejs_methods')

    retourne_tache_avec_details : async function(tache){

        await $.post({
            url: "eden/tache/" + tache.id + "/recuperer_details",
            dataType: "json"
        }).done((donnees) => {

            tache = {...tache, ...donnees};
        });

        return tache;
    },
@endpush