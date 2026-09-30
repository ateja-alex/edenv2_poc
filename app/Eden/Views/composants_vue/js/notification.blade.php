const notification = Vue.component('notification', {
    template: ` <div>
            <div class="css_zone_notifications_navbar" v-click_outside>

                <div class="bouton" @click="affichage_notification = !affichage_notification">
                    <span class="fa fa-bell"></span>

                    <template v-if="chargement_initiale">
                        <span class="badge badge-default" v-if="notifications_navbar_notifications.length == undefined || notifications_navbar_notifications.length == 0">0</span>
                        <span class="badge badge-danger" v-else >@{{ notifications_navbar_notifications.length }}</span>
                    </template>
                </div>

                <div v-if="affichage_notification" class="css_zone_notifications_navbar_panel">
                    <div class="notifications text-center" v-if="notifications_navbar_notifications.length == undefined || notifications_navbar_notifications.length == 0">@traduction('interface.eden_notifications.pas_de_notifications')</div>
                    <transition-group v-else name="slide-fade" class="notifications" tag="div">
                        <div v-for="(notification,index) in notifications_avec_lien" :key="notification.id" class="notification" @mouseover="affichage_options = notification.id" @mouseleave="affichage_options = null">
                            <img alt="image" onerror="this.onerror=null; this.src='eden/images/no_avatar.jpg'" class="rounded-circle" :src="notification.cree_par | affiche_utilisateur_avatar" />
                            <div class="contenu">
                                <div class="titre">
                                    <b>@{{ notification.date | datetime_relatif }}</b>
                                    <div class="options" v-if="affichage_options == notification.id">
                                        <i class="css_pointer fas fa-bell-slash" :title="$root.traduction('composant.notification.boutons.supprimer_notification')" @click="considere_notification_vue($event,notification, index)"></i>
                                        <template v-if="notification.liens.length > 0">
                                            <i class="css_pointer fas fa-eye" :title="$root.traduction('composant.notification.boutons.voir_notification')"  @click="redirection_liens(notification.liens)"></i>
                                            <i class="css_pointer fas fa-check" :title="$root.traduction('composant.notification.boutons.voir_et_supprimer_notification')"  @click="considere_notification_vue($event,notification, index);redirection_liens(notification.liens)"></i>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-default" v-show="notification.tag !== null && notification.couleur_tag === null">@{{ notification.tag }}</span>
                                    <span class="badge badge-default" :style="'background: '+notification.couleur_tag+'; color: '+notification.couleur_tag_police+';'" v-show="notification.tag !== null && notification.couleur_tag !== null">@{{ notification.tag }}</span>
                                    <span v-html="notification.contenu_html"></span>
                                </div>
                            </div>
                        </div>
                    </transition-group>
                    <div class="bandeau_bas">
                        <a href="{{ route('base_eden.liste.index', ['notification'], false) }}" v-html="$root.traduction('interface.eden_notifications.historique_notifications')"></a>
                        <span v-if="notifications_navbar_notifications.length > 0" class="css__lien css_pointer" @click="considere_toutes_notification_vue($event,notifications_navbar_notifications)">OK pour tous</span>
                    </div>
                </div>
            </div>

            <template v-if="modale_notification">
                <transition name="modal" >
                    <div class="modal-mask modale_notification">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('interface.notifications_modales.titre')</h5>
                                </div>
                                <div class="modal-body">
                                    <template v-for="notification in notifications_modal_notifications">
                                        <div class="row">
                                            <div class="col-md-1">
                                                <span class="fa" :class="[notification.icone]"></span>
                                            </div>
                                            <div class="col-md-11" v-html="notification.contenu_html"></div>
                                        </div><br/>
                                    </template>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" @click="enregistrer_notifications_vues()" >@traduction('interface.notifications_modales.ok_vu')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </template>
        </div>`,
        data : function(){
            return {
                affichage_notification: false,
                modale_notification : false,
                notifications_modal_notifications: [],
                notifications_navbar_notifications: [],
                affichage_options: null,
                chargement_initiale : false,
            };
        },
        created : function(){
            this.maj_notification_modales();
            setInterval(() => {
                this.maj_notification_modales();
            }, 30000);
        },
        computed : {
            notifications_avec_lien : function(){

                var notifications_avec_lien = structuredClone(this.notifications_navbar_notifications);

                for(notification_avec_lien of notifications_avec_lien){

                    var contenu_html = document.createElement('div');
                    contenu_html.innerHTML = notification_avec_lien.contenu_html.trim();

                    var balises_a = contenu_html.querySelectorAll('a');
                    const liens = [];

                    for(balise_a of balises_a){
                        liens.push({
                            lien : balise_a.href,
                            blank : balise_a.target == '_blank'
                        });
                    }

                    notification_avec_lien.liens = liens;
                }

                return notifications_avec_lien;
            },
        },
        methods : {
            maj_notification_modales: function() {

                $.ajax({

                    method: 'POST',
                    dataType: 'json',
                    data: {
                        zone: ['modal_notifications', 'navbar_notifications'],
                    },
                    url: '{{ route('base_eden.notifications.recuperer', [], false) }}'
                }).done((notifications) => {

                    this.notifications_modal_notifications = notifications.modal_notifications;
                    this.notifications_navbar_notifications = notifications.navbar_notifications;

                    this.chargement_initiale = true;

                    if(this.notifications_modal_notifications.length > 0)
                        this.modale_notification = true;
                });
            },

            enregistrer_notifications_vues: function() {

                var notifications = [];

                this.notifications_modal_notifications.forEach(function(notification) {

                    notifications.push(notification.id);
                });

                // on doit enregistrer les notifications comme vues en bdd
                $.ajax({

                    method: 'POST',
                    dataType: 'json',
                    data: {

                        notifications: notifications,
                    },
                    url: '{{ route('base_eden.notifications.enregistrer_comme_vues', [], false) }}'
                }).done((retour) => {
                    if(retour === true)
                        this.modale_notification = false;
                });
            },

            considere_notification_vue: function(event,notification, index) {

                event.stopPropagation();

                var notifications = [];

                notifications.push(notification.id);

                // on doit enregistrer les notifications comme vues en bdd
                $.ajax({

                    method: 'POST',
                    dataType: 'json',
                    data: {

                        notifications: notifications,
                    },
                    url: '{{ route('base_eden.notifications.enregistrer_comme_vues', [], false) }}'
                }).done((retour) => {
                    if(retour === true)
                        this.notifications_navbar_notifications.splice(index, 1);
                });
            },

            considere_toutes_notification_vue: function(event,notifications) {

                event.stopPropagation();

                var notifications_id = [];

                notifications.forEach(function(notification,index){
                notifications_id.push(notification.id);
                });

                // on doit enregistrer les notifications comme vues en bdd
                $.ajax({

                    method: 'POST',
                    dataType: 'json',
                    data: {

                        notifications: notifications_id,
                    },
                    url: '{{ route('base_eden.notifications.enregistrer_comme_vues', [], false) }}'
                }).done((retour) => {
                    if(retour === true)
                        this.notifications_navbar_notifications.splice(0,notifications.length);
                });;
            },

            redirection_liens : function(liens){

                if(liens.length > 1){
                    for(lien of liens){
                        window.open(lien.lien, '_blank');
                    }
                }
                else{

                    lien = liens[0];

                    if(lien.blank)
                        window.open(lien.lien, '_blank');
                    else
                        window.location = lien.lien;

                }
            },
        },

        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target))) {
                            vnode.context.affichage_notification = false;
                        }
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
});
