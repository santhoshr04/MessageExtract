<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeEntry extends Model
{
    protected $fillable = [
        'question', 'answer', 'category', 'confidence',
        'question_id', 'answer_id', 'media_path', 'remarks'
    ];

    public function questionMessage()
    {
        return $this->belongsTo(Message::class, 'question_id');
    }

    public function answerMessage()
    {
        return $this->belongsTo(Message::class, 'answer_id');
    }
}

