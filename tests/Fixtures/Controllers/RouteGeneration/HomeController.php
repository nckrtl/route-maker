<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;

class HomeController extends Controller
{
    public function show(): Response
    {
        return inertia('Home');
    }

    public function index(): Response
    {
        return inertia('Home/Index');
    }
}