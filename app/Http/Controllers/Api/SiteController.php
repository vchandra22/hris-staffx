<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SiteController extends Controller
{
    public function index() {
        return response()->success([
            'name' => 'wahyu agung tribawono',
            'email' => 'wahyuagung26@gmail.com'
        ], 'success get data');
    }
}