<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // This registers /broadcasting/auth
        Broadcast::routes([
            'middleware' => ['auth:web'], // or ['auth:sanctum'] or whatever you use
        ]);

        require base_path('routes/channels.php');
    }
}
