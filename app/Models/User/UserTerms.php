<?php

namespace App\Models\User;

use App\Models\Model;

class UserTerms extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url', 'text', 'parsed_text'
    ];

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_terms';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the user these terms belong to.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User');
    }
}