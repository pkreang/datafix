<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Organization vertical (terminology)
    |--------------------------------------------------------------------------
    |
    | factory — default UI copy: องค์กร / สาขา (TH), Organization / Branch (EN).
    | school  — school-oriented copy: โรงเรียน / สาขา (TH), School / Campus (EN).
    |
    | Set ORG_VERTICAL=school in .env for school deployments. Override PHP files
    | live under resources/lang/verticals/school/{locale}/ (merged after base lang).
    |
    */

    'vertical' => env('ORG_VERTICAL', 'factory'),

];
