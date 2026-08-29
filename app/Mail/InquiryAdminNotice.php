<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** サイト管理者あての新着問い合わせ通知（旧 otoi3.asp の問合せ者へmail相当）。 */
class InquiryAdminNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string $siteName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->siteName} - 新しいお問い合わせ（#{$this->inquiry->ticket_number}）",
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.inquiry-admin-notice');
    }
}
