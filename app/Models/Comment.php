<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage; // Import Storage facade
use RyanChandler\Comments\Models\Comment as BaseComment;

class Comment extends BaseComment
{
    public function attachments()
    {
        return $this->hasMany(CommentAttachment::class);
    }
}
