<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // <--- C'est cette ligne qui est importante

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
