<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\Post;
use App\Services\Newsletter\InlineCss;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Eén Mailable voor zowel testmail (sync, vanuit controller) als
     * dispatch-job (queued, blok f). De caller bepaalt to-adres via
     * ->to($email). Niet ShouldQueue: laat de Job in blok f queueing doen.
     */
    public function __construct(
        public Newsletter $newsletter,
        public string $unsubscribeUrl,
        public bool $isTest = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isTest
            ? '[TEST] '.$this->newsletter->subject
            : $this->newsletter->subject;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $view = 'emails.newsletter.templates.'.$this->newsletter->template;

        $html = view($view, [
            'newsletter' => $this->newsletter,
            'unsubscribeUrl' => $this->unsubscribeUrl,
            'posts' => $this->digestPosts(),
        ])->render();

        return new Content(
            htmlString: InlineCss::inline($html),
        );
    }

    /**
     * Levert de N meest recente gepubliceerde posts voor de digest-template.
     * Voor andere templates leeg om Post-query te vermijden.
     */
    private function digestPosts()
    {
        if ($this->newsletter->template !== Newsletter::TEMPLATE_DIGEST) {
            return collect();
        }

        $count = config('westein.newsletter.digest_post_count', 5);

        return Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit($count)
            ->get();
    }
}
