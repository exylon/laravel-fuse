<?php

return [

    /*
    |----------------------------------------
    | Repository Configuration
    |----------------------------------------
    */
    'repository' => [
        /*
        |----------------------------------------
        | Query Name for Pagination
        |----------------------------------------
        */
        'page_name'         => 'page',

        /*
        |----------------------------------------
        | Pagination Method
        |----------------------------------------
        | More details here: (https://laravel.com/docs/5.5/pagination#basic-usage)
        | Options
        |    length_aware - Makes use of LengthAwarePaginator (default)
        |    simple       - Makes use of Paginator
        */
        'pagination_method' => 'length_aware'
    ]
];
