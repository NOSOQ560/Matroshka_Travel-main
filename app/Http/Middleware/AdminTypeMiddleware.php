<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next, bool $includeAdmins = false)
    {
        if (auth()->user()->type != UserTypeEnum::admin->value && auth()->user()->type != UserTypeEnum::super_admin->value) {
            return ResponseHelper::unauthorizedResponse();
        }

        return $next($request);
    }
}
