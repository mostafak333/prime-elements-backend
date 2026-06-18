<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //role automatically overrides any missing permissions without having to manually assign them every time you create a new permission
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('SuperAdmin', 'api-admin')) {
                return true;
            }
        });

        // 2. Global Scramble Config for APIDog Optimization
        Scramble::configure()
            // Automatically append headers to every discovered endpoint operation
            ->withOperationTransformers(function (Operation $operation) {

                // Explicitly set up the string schemas with defaults for the headers
                $acceptSchema = new StringType();
                $acceptSchema->default('application/json');
                $acceptSchema->example('application/json');

                $contentTypeSchema = new StringType();
                $contentTypeSchema->default('application/json');

                $operation->addParameters([
                    Parameter::make('Accept', 'header')
                        ->required(true)
                        ->description('Forces backend to handle transactions using JSON payloads.')
                        ->setSchema(Schema::fromType($acceptSchema)),

                    Parameter::make('Content-Type', 'header')
                        ->required(true)
                        ->description('Declares structural request format.')
                        ->setSchema(Schema::fromType($contentTypeSchema)),
                ]);
            })
            // Attach the global Bearer security protocol scheme to the structural document
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT Token')
                );
            });
    }
}
