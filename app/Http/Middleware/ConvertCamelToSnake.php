<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class ConvertCamelToSnake
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $this->snakeCase($request->all());

        $request->replace($data);

        return $next($request);
    }

    private function snakeCase(array $data): array
    {
        return collect($data)->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                $value = $this->snakeCase($value);
            }

            return [Str::snake($key) => $value];
        })->toArray();
    }
}
