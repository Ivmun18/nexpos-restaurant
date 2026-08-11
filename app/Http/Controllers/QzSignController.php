<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class QzSignController extends Controller
{
    public function sign(Request $request)
    {
        $message = $request->input('message');
        $privateKey = file_get_contents(public_path('qz-private-key.pem'));
        $pkeyid = openssl_pkey_get_private($privateKey);
        openssl_sign($message, $signature, $pkeyid, OPENSSL_ALGO_SHA512);
        return base64_encode($signature);
    }
}
