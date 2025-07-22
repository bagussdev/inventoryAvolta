<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveHtmlComments
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $response instanceof \Illuminate\Http\Response &&
            str_contains($response->headers->get('Content-Type'), 'text/html')
        ) {
            $output = $response->getContent();

            // Hapus komentar HTML: <!-- ... -->
            $output = preg_replace('/<!--(.|\s)*?-->/', '', $output);

            $response->setContent($output);
        }

        return $response;
    }
}
