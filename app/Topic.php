<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'subject_id', 'status'
    ];

    /**
     * Get the subject using the subject_id.
     */
    public function subject()
    {
        return $this->hasOne('App\Subject', 'id', 'subject_id');
    }
}
