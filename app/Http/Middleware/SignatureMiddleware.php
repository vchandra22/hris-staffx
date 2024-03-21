<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!empty($request->header("signature"))) {
            $privateKey = file_get_contents(storage_path() . "/auth/private.pem");
            if (openssl_private_decrypt(base64_decode($request->header("signature")), $decrypted, $privateKey)) {
                if (json_encode($request->all()) != $decrypted) {
                    return response()->failed(['Data tidak valid']);
                }
            } else {
                return response()->failed(['Gagal decrypt data']);
            }
        }

        return $next($request);
    }
}
