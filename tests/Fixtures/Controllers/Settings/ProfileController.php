<?php

namespace Tests\Fixtures\Controllers\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NckRtl\RouteMaker\Get;

class ProfileController
{
    #[Get(uri: '/settings/profile', middleware: 'auth')]
    public function edit(Request $request): Response
    {
        return response('Profile edit page');
    }
}
