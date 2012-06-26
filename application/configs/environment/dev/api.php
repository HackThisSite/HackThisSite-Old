<?php
return array(
    'api:whitelist' => array( // Array of allowable functions to call.
        'articles/getNewPosts',
        'articles/get',
        'articles/getScore',
        'articles/getForUser',
        
        'bugs/getNew',
        'bugs/get',
        
        'comments/getForId',
    ),
    'api:clients' => array( // Array of MD5 hashes of clients's certificates.
        '47ac7692bad7d1781c2394f642e7cec2', // Bren2010 on dev
    ),
    
);
