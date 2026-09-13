<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * PHP only populates $_POST/$_FILES for POST requests, so PUT/PATCH/DELETE
 * requests carrying form data (multipart/form-data or
 * application/x-www-form-urlencoded) arrive at the framework with no input.
 * This middleware re-parses the raw body for those methods so that e.g.
 * `PUT /api/admin/admins/{admin}` updates work when sent as form data.
 */
class HandleFormDataOnNonPostRequests
{
    private static array $tempFiles = [];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getRealMethod(), ['PUT', 'PATCH', 'DELETE'], true)) {
            $contentType = (string) $request->header('Content-Type');

            if (str_starts_with($contentType, 'multipart/form-data')) {
                $this->parseMultipart($request);
            } elseif (str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
                $this->parseUrlEncoded($request);
            }
        }

        return $next($request);
    }

    private function parseUrlEncoded(Request $request): void
    {
        $fields = [];
        parse_str($request->getContent(), $fields);

        if ($fields) {
            $request->request->add($fields);
        }
    }

    private function parseMultipart(Request $request): void
    {
        $content = $request->getContent();
        $contentType = (string) $request->header('Content-Type');

        if ($content === '' || ! preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return;
        }

        $boundary = trim($matches[1], '"');

        $parts = preg_split('/--'.preg_quote($boundary, '/').'/', $content);

        $fields = [];
        $files = [];

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");

            if ($part === '' || trim($part) === '--') {
                continue;
            }

            if (! str_contains($part, "\r\n\r\n")) {
                continue;
            }

            [$headers, $body] = explode("\r\n\r\n", $part, 2);
            $body = rtrim($body, "\r\n");

            if (! preg_match('/name="([^"]*)"/', $headers, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];

            if (preg_match('/filename="([^"]*)"/', $headers, $fileMatch)) {
                $files[$name] = $this->buildUploadedFile($fileMatch[1], $headers, $body);
            } else {
                if (isset($fields[$name])) {
                    $fields[$name] = is_array($fields[$name]) ? $fields[$name] : [$fields[$name]];
                    $fields[$name][] = $body;
                } else {
                    $fields[$name] = $body;
                }
            }
        }

        if ($fields) {
            $request->request->add($fields);
        }

        if ($files) {
            $request->files->replace(array_merge($request->files->all(), $files));
        }

        if (self::$tempFiles) {
            register_shutdown_function(function () {
                foreach (self::$tempFiles as $path) {
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            });
        }
    }

    private function buildUploadedFile(string $originalName, string $headers, string $body): UploadedFile
    {
        $mime = null;

        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $mimeMatch)) {
            $mime = trim($mimeMatch[1]);
        }

        $path = tempnam(sys_get_temp_dir(), 'putfile_');
        file_put_contents($path, $body);

        self::$tempFiles[] = $path;

        return new UploadedFile($path, $originalName, $mime, null, true);
    }
}
