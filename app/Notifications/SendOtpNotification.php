<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;
    
    public $otp;
    public $purpose;

    public function __construct($otp, $purpose = 'verification')
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->purpose === 'password_reset') {
            return (new MailMessage)
                ->subject('Password Reset OTP')
                ->line('You requested to reset your password.')
                ->line('Your OTP is: ' . $this->otp)
                ->line('This OTP will expire in 10 minutes.')
                ->line('If you did not request this, please ignore this email.');
        }

        return (new MailMessage)
            ->subject('Email Verification OTP')
            ->line('Thank you for registering!')
            ->line('Your OTP is: ' . $this->otp)
            ->line('This OTP will expire in 10 minutes.');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}