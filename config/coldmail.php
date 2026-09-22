<?php

return [
    /*
    | How many times a failed email is automatically requeued before it is
    | left as failed and can only be retried manually.
    */
    'auto_retry_limit' => (int) env('AUTO_RETRY_LIMIT', 5),

    /*
    | Rolling cooldown window before a Gmail account can send again.
    | Based on the account's LAST send time — no emails can be sent until
    | this many hours have elapsed since then.
    */
    'rolling_window_hours' => (int) env('ROLLING_WINDOW_HOURS', 48),
];