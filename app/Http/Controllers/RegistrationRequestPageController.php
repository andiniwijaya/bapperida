<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use Illuminate\View\View;

class RegistrationRequestPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', RegistrationRequest::class);

        return view('registration-requests.index');
    }
}
