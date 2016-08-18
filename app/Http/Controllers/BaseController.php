<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller {

    /**
     * Redirect to page not found page
     */
    protected function redirectNotFound()
    {
        return Response::view('errors.404', array('pageTitle'=> Lang::get('messages.Error')), 404);
    }
}
