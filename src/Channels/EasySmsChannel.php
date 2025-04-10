<?php

/*
 * This file is part of the leonis/easysms-notification-channel.
 * (c) yangliulnn <yangliulnn@163.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Leonis\Notifications\EasySms\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Leonis\Notifications\EasySms\Exceptions\CouldNotSendNotification;

class EasySmsChannel
{
    /**
     * Send the given notification.
     *
     * @param $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        if ($notifiable instanceof Model) {
            $to = $notifiable->routeNotificationForEasySms($notification);
        } elseif ($notifiable instanceof AnonymousNotifiable) {
            $to = $notifiable->routes[__CLASS__];
        }

        $message = $notification->toEasySms($notifiable);

        try {
            $app = app()->make('easysms');
            $result = $app->send($to, $message);
            $config = $app->getConfig();

            if ($config->get('debug')) {
                Log::channel($config->get('gateways.errorlog.channel'))->debug('send message result', [
                    'to' => $to,
                    'message' => $message,
                    'result' => $result,
                ]);
            }
            return $result;
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        }
    }
}
