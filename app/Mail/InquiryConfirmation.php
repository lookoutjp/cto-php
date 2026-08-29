<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** 問い合わせ者本人あての受付確認メール（旧 otoi3.asp のサイトへmail相当）。 */
class InquiryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string $siteName,
        public ?string $siteDomain,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->siteName} - お問い合わせ（#{$this->inquiry->ticket_number}）を受け付けました",
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.inquiry-confirmation');
    }
}
