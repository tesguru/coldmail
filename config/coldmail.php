<?php

return [
    /*
    | How many times a failed email is automatically requeued before it is
    | left as failed and can only be retried manually.
    */
    'auto_retry_limit' => (int) env('AUTO_RETRY_LIMIT', 5),
];