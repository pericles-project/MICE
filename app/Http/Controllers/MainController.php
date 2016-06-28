<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MainController extends BaseController
{
    /**
     * Display change impact
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $action = Input::get("action");
        $uuid = Input::get("uuid");
        $repository_name = Input::get("repository_name");
        $resource_uri = Input::get("resource_uri");
        $change = Input::get("change");

        if (!$uuid) {
            $case = Input::get("case") ? : 1;
            $request->session()->put('case', $case);
        }
        return view('home');
    }

    /**
     * Get the dependency graph
     *
     * @return Response
     */
    public function graph(Request $request)
    {
        $case = $request->session()->get('case');
        $filename = 'data' . $case . '.json';
        
        header('Content-Type: application/json');
        echo file_get_contents($filename);
    }
}
