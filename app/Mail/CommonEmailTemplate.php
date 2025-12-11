<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $settings;
    public $pdfPath; // ← NUEVO

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $settings, $pdfPath = null)
    {
        $this->template = $template;
        $this->settings = $settings;
        $this->pdfPath = $pdfPath; // ← NUEVO
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from(
            $this->settings['company_email'],
            $this->template->from
        )->markdown('email.common_email_template')
         ->subject($this->template->subject)
         ->with('content', $this->template->content);

        // ← NUEVO: Adjuntar PDF si se proporcionó y existe
        if (!empty($this->pdfPath) && file_exists($this->pdfPath)) {
            $email->attach($this->pdfPath, [
                'as' => 'transferencia.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}
