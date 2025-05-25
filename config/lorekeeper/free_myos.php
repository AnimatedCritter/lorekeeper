<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Free MYO Settings
    |--------------------------------------------------------------------------
    |
    | A collection of settings regarding species, rarity, tradeability,
    | and other such limitations for free MYOs.
    |
    */

    // Optional limit to the number of free MYOs a user can create.
    // Enter "0" to allow users infinite free MYOs.
    'free_myos_max_number'   => 0,

    // ID of the max rarity a free MYO allows.
    // Enter "0" for no limitations.
    'free_myos_rarity'   => 0,

    // 0: Subtypes are optional for free MYOs,
    // 1: Subtypes are mandatory for free MYOs. 
    'free_myos_require_subtype'   => 0,

    // 0: MYOs cannot be gifted,
    // 1: MYOs can be gifted.
    'free_myos_is_giftable'   => 1,

    // 0: MYOs cannot be traded,
    // 1: MYOs can be traded.
    'free_myos_is_tradeable'   => 1,

    // 0: MYOs cannot be resold,
    // 1: MYOs can be resold.
    'free_myos_is_resellable'   => 0,

];
