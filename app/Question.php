<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject_id', 'exam_id', 'topic_id', 'title', 'available_answers', 'right_answer'
    ];

    /**
     * Get the Subject using the subject_id.
     */
    public function subject()
    {
        return $this->hasOne('App\Subject', 'id', 'subject_id');
    }

    /**
     * Get the Topic Name using the subject_id.
     */
    public function topic()
    {
        return $this->hasOne('App\Topic', 'id', 'topic_id');
    }

    /**
     * Get the Subject using the subject_id.
     */
    public function answer()
    {
        return $this->hasMany('App\Answer', 'question_id');
    }

}
