<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DBTransactionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->isMethod('get')) {
            return $next($request);
        }

        return DB::transaction(function () use ($next, $request) {
            $response = $next($request);

            if ($response->getStatusCode() >= 400 && $response->getStatusCode() !=422) {
                DB::rollBack();
                $message = json_decode($response->getContent(), true);

                if(!app()->environment('local'))
                {
                    return response()->json([
                        'message' => $message['message'] ?? 'An error occurred , please try again later.',
                    ], $response->getStatusCode());
                }

            }

            return $response;
        });
    }
}
