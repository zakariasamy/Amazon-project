<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncExtensionAuth
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
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if token exists in session and is still valid in the database
            $token = session('chrome_extension_token');
            $tokenExists = false;
            
            if ($token) {
                $parts = explode('|', $token);
                if (count($parts) === 2) {
                    $tokenId = $parts[0];
                    $tokenExists = $user->tokens()->where('id', $tokenId)->exists();
                }
            }
            
            if (!$token || !$tokenExists) {
                // To prevent DB bloat, delete old chrome-extension tokens for this user
                $user->tokens()->where('name', 'chrome-extension')->delete();
                $token = $user->createToken('chrome-extension')->plainTextToken;
                session(['chrome_extension_token' => $token]);
            }

            // Set unencrypted cookies for the extension
            // 120 minutes lifetime, path '/', not HTTP-only (so extension and JS can read it), secure if HTTPS (set to false for local dev)
            $response->headers->setCookie(cookie('extension_auth_token', $token, 120, '/', null, false, false));
            $response->headers->setCookie(cookie('extension_user_info', json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subscription_tier' => $user->subscription_tier ?? 'free',
            ]), 120, '/', null, false, false));
        } else {
            // User is guest, clear cookies
            $response->headers->setCookie(cookie('extension_auth_token', '', -1, '/', null, false, false));
            $response->headers->setCookie(cookie('extension_user_info', '', -1, '/', null, false, false));
        }

        return $response;
    }
}
