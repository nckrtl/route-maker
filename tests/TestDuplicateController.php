<?php

namespace NckRtl\RouteMaker\Tests;

use Illuminate\Routing\Controller;

class TestDuplicateController extends Controller
{
    public function index()
    {
        return 'Index';
    }

    public function show()
    {
        return 'Show';
    }
}
