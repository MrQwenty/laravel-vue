<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Mail\MailQueueMessage;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\ResetPasswordQueued;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SimpleMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmailQueued::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject(__('mail.verifyMailSubject'))
                ->line(__('mail.verifyMailLine'))
                ->action(__('mail.verifyMailAction'), $url);
        });

        ResetPasswordQueued::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject(__('mail.resetPasswordSubject'))
                ->line(__('mail.resetPasswordLine1'))
                ->action(__('mail.resetPasswordAction'), $url)
                ->line(__('mail.resetPasswordLine2'));
        });
    }
}
