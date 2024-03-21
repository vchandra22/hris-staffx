<?php

namespace App\Http\Controllers\Web;

use App\Helpers\Angular;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AppController extends Controller
{
    /**
     * Our custom service provider is going to make sure
     * $ng is a singleton
     */
    public function index(Angular $ng)
    {
        return view('welcome');
    }

    public function log()
    {
        return view('log');
    }

    public function test() {
        try {
            Log::info('tes log info', ['name' => 'wahyu']);
            Log::warning('tes log warning', ['name' => 'wahyu agung']);
            Log::error('tes log error', ['name' => 'wahyu agung tribawono']);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
