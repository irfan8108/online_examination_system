<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'exam_date', 'exam_duration', 'marks', 'subjects', 'positive_marking', 'negative_marking', 'live_scoring', 'status'
    ];
}
