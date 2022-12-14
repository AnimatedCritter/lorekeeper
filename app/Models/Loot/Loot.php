<?php

namespace App\Models\Loot;

use Config;
use App\Models\Model;

class Loot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loot_table_id', 'rewardable_type', 'rewardable_id',
        'quantity', 'weight', 'data'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loots';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type' => 'required',
        'rewardable_id' => 'required',
        'quantity' => 'required|integer|min:1',
        'weight' => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type' => 'required',
        'rewardable_id' => 'required',
        'quantity' => 'required|integer|min:1',
        'weight' => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward()
    {
        switch ($this->rewardable_type)
        {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'rewardable_id');
            case 'ItemRarity':
                return $this->belongsTo('App\Models\Item\Item', 'rewardable_id');
            case 'Currency':
                return $this->belongsTo('App\Models\Currency\Currency', 'rewardable_id');
            case 'LootTable':
                return $this->belongsTo('App\Models\Loot\LootTable', 'rewardable_id');
            case 'ItemCategory':
                return $this->belongsTo('App\Models\Item\ItemCategory', 'rewardable_id');
            case 'ItemCategoryRarity':
                return $this->belongsTo('App\Models\Item\ItemCategory', 'rewardable_id');
            case 'None':
                // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
                return $this->belongsTo('App\Models\Loot\Loot', 'rewardable_id', 'loot_table_id')->whereNull('loot_table_id');
        }
        return null;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        if (!$this->attributes['data']) return null;
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Display the loot item and link to it's encylopedia entry.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        // Adds spaces to the rewardable_type between capital letters
        $displayType = preg_replace('/(?<!\ )[A-Z]/', ' $0', $this->rewardable_type);

        if($this->rewardable_type == 'None')
            return 'No Loot Drop';
        else
            return $displayType.' : <a href="'.$this->reward->url.'">'.$this->reward->name.'</a>';
    }

    /**
     * Displays the drop rate of a loot.
     *
     * @return string
     */
    public function getDropRateAttribute()
    {
        $totalWeight = Loot::where('loot_table_id', $this->loot_table_id)->sum('weight');
        $dropRate = $this->weight / $totalWeight * 100;
        return number_format((float)$dropRate, 2, '.', '').'%';
    }
}
