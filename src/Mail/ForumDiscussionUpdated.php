<?php

namespace Innoboxrr\Forum\Mail;

use Innoboxrr\Forum\Models\Discussion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForumDiscussionUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $discussion;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view(config('forum.email.view'))
                    ->with([
                        'discussion' => $this->discussion,
                    ]);
    }
}
