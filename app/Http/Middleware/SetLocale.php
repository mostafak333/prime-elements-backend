<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales the API is able to serve.
     */
    public const SUPPORTED_LOCALES = ['en', 'ar'];

    /**
     * Locale used when the request has no (or an unsupported) Accept-Language header.
     */
    public const DEFAULT_LOCALE = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolveLocale($request));

        return $next($request);
    }

    /**
     * Negotiate the locale from the Accept-Language header.
     */
    protected function resolveLocale(Request $request): string
    {
        $header = $request->header('Accept-Language');

        if (! is_string($header) || trim($header) === '') {
            return self::DEFAULT_LOCALE;
        }

        foreach ($this->acceptedLanguages($header) as $language) {
            $locale = strtolower(explode('-', $language)[0]);

            if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
                return $locale;
            }
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Parse the header into a list of languages ordered by quality factor.
     *
     * @return array<int, string>
     */
    protected function acceptedLanguages(string $header): array
    {
        $languages = [];

        foreach (explode(',', $header) as $part) {
            $segments = explode(';', $part);
            $language = strtolower(trim($segments[0]));

            if ($language === '') {
                continue;
            }

            $languages[] = [
                'language' => $language,
                'quality' => $this->quality($segments),
            ];
        }

        usort($languages, fn (array $a, array $b) => $b['quality'] <=> $a['quality']);

        return array_column($languages, 'language');
    }

    /**
     * Read the "q" quality factor of a single language segment.
     *
     * @param  array<int, string>  $segments
     */
    protected function quality(array $segments): float
    {
        foreach (array_slice($segments, 1) as $segment) {
            if (preg_match('/q\s*=\s*([0-9]*\.?[0-9]+)/i', $segment, $matches)) {
                return (float) $matches[1];
            }
        }

        return 1.0;
    }
}
