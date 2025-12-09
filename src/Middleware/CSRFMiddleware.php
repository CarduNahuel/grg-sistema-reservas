<?php

namespace App\Middleware;

use App\Services\CSRFProtection;

class CSRFMiddleware
{
    public function handle()
    {
        CSRFProtection::validateRequest();
        return true;
    }
}
