<?php

return [
    // If true: when 1 support reads, it clears unread for ALL supports (shared inbox)
    // If false: unread is per-support user (recommended)
    'shared_support_inbox' => env('SUPP_SHARED_SUPPORT_INBOX', false),
];
