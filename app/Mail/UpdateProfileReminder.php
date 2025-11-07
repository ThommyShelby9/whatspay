<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class UpdateProfileReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * L'utilisateur diffuseur.
     *
     * @var User
     */
    public $diffuseur;

    /**
     * Le lien vers le profil.
     * 
     * @var string
     */
    public $profileUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $diffuseur)
    {
        $this->diffuseur = $diffuseur;
        // Générer un URL sécurisé vers la page de profil
        $this->profileUrl = URL::route('influencer.profile', [], true, config('app.url'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Mise à jour de votre profil obligatoire')
            ->view('emails.profile-update-reminder')
            ->with([
                'prenom' => $this->diffuseur->firstname,
                'profileUrl' => $this->profileUrl
            ]);
    }
}