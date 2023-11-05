<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Http\Request;

class BaseRequest extends Request
{
    public function expectsJson()
    {
        return true;
    }

    public function wantsJson()
    {
        return true;
    }
}
