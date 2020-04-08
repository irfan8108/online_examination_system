<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_id', 'user_id', 'remaining_time'
    ];
}
