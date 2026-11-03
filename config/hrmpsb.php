<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minimum board size for a vacancy
    |--------------------------------------------------------------------------
    | Principal members left on a vacancy's board (after anyone recused from it
    | is taken out) below which the portal warns that the board may no longer
    | have a quorum. Warning only; nothing is blocked.
    |
    | PLACEHOLDER: not taken from the HRMPSB rules. Set HRMPSB_MIN_MEMBERS to the
    | number HR confirms. 0 turns the warning off.
    */
    'min_members' => (int) env('HRMPSB_MIN_MEMBERS', 3),

];
