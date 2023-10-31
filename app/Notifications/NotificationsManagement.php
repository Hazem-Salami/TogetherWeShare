<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationsManagement extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data){
        $this->data=$data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable){
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable){

        $variable = $this->data['notification_type'];

        switch ($variable) {
            case 'Like':
                $this->data['notification'] = 'Liked on your post';
                break;

            case 'Unlike':
                $this->data['notification'] = 'Disliked on your post';
                break;

            case 'EditReact':
                $this->data['notification'] = 'Edit his reaction to your post';
                break;    

            case 'CreateComment':
                $this->data['notification'] = 'Commented on your Post';
                break;

            case 'EditComment':
                $this->data['notification'] = 'Edit his comment on your post';
                break;    

            case 'SendOrder':
                $this->data['notification'] = 'Sent you a friend request';
                break;

            case 'AcceptOrder':
                $this->data['notification'] = 'Agreed to a friend request';
                break;

            case 'CreatePost':
                $this->data['notification'] = 'Added New Post';
                break;
        }
        return $this->data;
    }
}
