<div class="modal fade" id="modal_notifications" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('interface.notifications_modales.titre')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
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
				<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" @click="enregistrer_notifications_vues()" >@traduction('interface.notifications_modales.ok_vu')</button>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	
	notifications_modal_notifications: {!! service('notifications')->notifications_pour_zone('modal_notifications') !!},
	notifications_navbar_notifications: {!! service('notifications')->notifications_pour_zone('navbar_notifications') !!},
	
@endpush

@push('donnees_pour_vuejs_created')
	
	if(this.notifications_modal_notifications.length > 0) {
		
		setTimeout(function() {
			
			$('#modal_notifications').modal('show');
		}, 2000);
	}
	
	
@endpush

@push('donnees_pour_vuejs_methods')
	
	maj_notification_modales: function() {
		
		$.ajax({

			method: 'POST',
			dataType: 'json',
			data: {
				
				zone: ['modal_notifications', 'navbar_notifications'],
			},
			url: '{{ route('base_eden.notifications.recuperer') }}'
		}).done(function(notifications) {
			
			vue_instance.notifications_modal_notifications = notifications.modal_notifications;
			vue_instance.notifications_navbar_notifications = notifications.navbar_notifications;
			
			if(notifications.length > 0) {
				
				$('#modal_notifications').modal('show');
			}
			
			setTimeout(function() {
				
				vue_instance.maj_notification_modales();
			}, 300000);
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
			url: '{{ route('base_eden.notifications.enregistrer_comme_vues') }}'
		});
	},
	
	considere_notification_vue: function(notification, index) {
		
		var notifications = [];
		
		notifications.push(notification.id);
		
		// on doit enregistrer les notifications comme vues en bdd
		$.ajax({

			method: 'POST',
			dataType: 'json',
			data: {
				
				notifications: notifications,
			},
			url: '{{ route('base_eden.notifications.enregistrer_comme_vues') }}'
		});
		
		vue_instance.notifications_navbar_notifications.splice(index, 1);
	},

	considere_toutes_notification_vue: function(notifications) {

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
			url: '{{ route('base_eden.notifications.enregistrer_comme_vues') }}'
		});

		vue_instance.notifications_navbar_notifications.splice(0,notifications.length);
	},
	
@endpush

@push('donnees_pour_vuejs_mounted')
	
	setTimeout(function() {
		
		vue_instance.maj_notification_modales();
	}, 300000);
	
@endpush