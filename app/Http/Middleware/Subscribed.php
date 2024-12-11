<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 有料プランに登録済みであるかを確認
        if (!$request->user()?->subscribed('premium_plan')) {
            // 認可に失敗したときのリダイレクト先
            return redirect('subscription/create');
        }
        return $next($request);
    }
}
