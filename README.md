## Laravel + Inertia + GTM

This is a simple example of how to use Inertia with Laravel and Google Tag Manager - Specifically how to push events to the [window.dataLayer](https://developers.google.com/tag-platform/tag-manager/web/datalayer). I've created this repo to share my solution.

The project is created with the following commands:

```bash
composer create-project laravel/laravel laravel-inertia-gtm
cd laravel-inertia-gtm
composer require inertiajs/inertia-laravel
php artisan jetstream:install inertia
npm install
php artisan migrate
```

Basically I'm pushing events from te backend, through props on the page, into dataLayer. 

In the [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) I've changed the `boot()` method, and added macros to the `Store` and `RedirectResponse` classes:

```php
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Store::macro('addDataLayer', function ($data) {
            $this->push('data_layer', $data);

            return $this;
        });

        RedirectResponse::macro('addDataLayer', function ($data) {
            Session::addDataLayer($data);

            return $this;
        });
    }
```

I'm then reading these data_layer events in the `share` function of [HandleInertiaRequests](app/Http/Middleware/HandleInertiaRequests.php)
```php
    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'data_layer' => fn () => $request->session()->pull('data_layer', []),
        ]);
    }
```

And lastly I've created a [DataLayer](resources/js/Components/DataLayer.vue) component that pushes the data into the `window.dataLayer`:

```vue
<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const dataLayer = computed(() => usePage().props.data_layer);

watch(dataLayer, (nextDataLayer) => {
    if (!nextDataLayer) {
        return;
    }
    const nextEvents = JSON.parse(JSON.stringify(nextDataLayer));
    window.dataLayer = window.dataLayer || [];
    nextEvents.forEach((event) => {
        window.dataLayer.push(event);
    });
}, { immediate: true });
</script>
<template></template>
```

In this example I've implemented the [event of a login](app/Events/UserEvent.php), hooking into Fortify's `LoginResponse`. In [app/Providers/FortifyServiceProvider.php](app/Providers/FortifyServiceProvider.php):

```php
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ...

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request): RedirectResponse
            {
                return redirect(route('dashboard'))
                    ->addDataLayer(
                        UserEvent::login($request->user())->build()
                    );
            }
        });

        // ...
    }
```
