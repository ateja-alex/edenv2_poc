<?php

namespace App\Eden\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class Slack extends Notification {
	
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($parametres = array()) {
        
		$this->parametres = $parametres;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
		
        return ['slack'];
    }

    public function toSlack($notifiable) {
		
		$message = new SlackMessage();
		
		$parametres = $this->parametres;
		
		if(isset($this->parametres['attachment']))
			$message->attachment(function ($attachment) use ($parametres) {
                    $attachment->title($parametres['attachment']['title'], $parametres['attachment']['url'])->fields($parametres['attachment']['fields']);
                });
				
		$message->content($parametres['content']);
				
		return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            //
        ];
    }
}
