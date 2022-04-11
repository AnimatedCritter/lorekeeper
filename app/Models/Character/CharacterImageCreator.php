<?php

namespace App\Models\Character;

use Config;
use Settings;
use DB;
use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserTerms;
use App\Models\Character\CharacterCategory;

class CharacterImageCreator extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'type', 'url', 'alias', 'character_type', 'user_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_image_creators';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;
    
    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the image associated with this record.
     */
    public function image() 
    {
        return $this->belongsTo('App\Models\Character\CharacterImage', 'character_image_id');
    }

    /**
     * Get the user associated with this record.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }

    /**********************************************************************************************
    
        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays a link using the creator's URL.
     * 
     * @return string
     */
    public function displayLink()
    {
        if($this->user_id)
        {
            $user = User::find($this->user_id);
            return $user->displayName;
        }
        else if ($this->url)
        {
            return prettyProfileLink($this->url);
        }
        else if($this->alias)
        {
            $user = User::where('alias', trim($this->alias))->first();
            if($user) return $user->displayName;
            else return '<a href="https://www.deviantart.com/'.$this->alias.'">'.$this->alias.'@dA</a>';
        }
    }

    /**
     * Displays a designer's terms.
     * 
     * @return string
     */
    public function displayTerms()
    {
        if($this->user_id)
        {
            $user = User::find($this->user_id);
            $terms = UserTerms::find($this->user_id);
            if ($terms->url != null && $terms->parsed_text != null)
            {
                if(Settings::get('external_terms') == 0)
                    return $terms->parsed_text;
                else if(Settings::get('external_terms') == 1)
                    return '<a href="'.$terms->url.'" target="_blank" onclick="return confirm(\''
                    .config('lorekeeper.settings.site_name', 'Lorekeeper').
                     '&nbsp;has no control over and is not responsible for the content on external sites. Do you still want to visit this site?\')">'.$terms->url.'</a>'.$terms->parsed_text;
                else
                    return '<a href="'.$terms->url.'" target="_blank">'.$terms->url.'</a>'.$terms->parsed_text;
            }
            else if ($terms->url == null && $terms->parsed_text != null)
            {
                return $terms->parsed_text;
            }
            else if ($terms->url != null && $terms->parsed_text == null)
            {
                if(Settings::get('external_terms') == 0)
                    return '<i>'.$user->displayName.'&nbsp;has not submitted their terms to the site.</i>';
                else if(Settings::get('external_terms') == 1)
                    return '<a href="'.$terms->url.'" target="_blank" onclick="return confirm(\''
                    .config('lorekeeper.settings.site_name', 'Lorekeeper').
                     '&nbsp;has no control over and is not responsible for the content on external sites. Do you still want to visit this site?\')">'.$terms->url.'</a>';
                else
                    return '<a href="'.$terms->url.'" target="_blank">'.$terms->url.'</a>';
            }
            else if ($terms->url == null && $terms->parsed_text == null)
            {
                return '<i>'.$user->displayName.'&nbsp;has not submitted their terms to the site.</i>';
            }
        }
        else if ($this->url)
        {
            return '<i>'.prettyProfileLink($this->url).'&nbsp;is an off-site user and is unable submit their terms to the site.</i>';
        }
        else if($this->alias)
        {
            $user = User::where('alias', trim($this->alias))->first();
            return '<i><a href="https://www.deviantart.com/'.$this->alias.'">'.$this->alias.'@dA</a>&nbsp;is an off-site user and cannot submit their terms to the site.</i>';
        }
    }

    /**
     * Displays the last time a designer's terms were updated.
     * 
     * @return string
     */
    public function lastUpdatedTerms()
    {
        if($this->user_id)
        {
            $terms = UserTerms::find($this->user_id);
            if ($terms->url != null || $terms->parsed_text != null)
            {
                return '<i>as of '.$terms->last_updated.'</i>';
            }
        }
    }
}
