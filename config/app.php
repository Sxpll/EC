<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nazwa aplikacji
    |--------------------------------------------------------------------------
    |
    | Ta wartość jest nazwą Twojej aplikacji. Ta wartość jest używana, gdy
    | framework musi umieścić nazwę aplikacji w powiadomieniu lub
    | innym elemencie interfejsu użytkownika.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Środowisko aplikacji
    |--------------------------------------------------------------------------
    |
    | Ta wartość określa "środowisko" Twojej aplikacji, w którym obecnie działa.
    | Może to określać sposób, w jaki wolisz konfigurować różne usługi
    | używane przez aplikację. Ustaw to w pliku ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Tryb debugowania aplikacji
    |--------------------------------------------------------------------------
    |
    | Gdy Twoja aplikacja jest w trybie debugowania, szczegółowe komunikaty
    | o błędach z informacjami o stosie będą wyświetlane przy każdym błędzie,
    | który wystąpi w aplikacji. Jeśli wyłączone, wyświetlany jest prosty
    | ogólny komunikat o błędzie.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL aplikacji
    |--------------------------------------------------------------------------
    |
    | Ten URL jest używany przez konsolę do poprawnego generowania URL podczas
    | korzystania z narzędzia wiersza poleceń Artisan. Powinieneś ustawić to
    | na główny URL swojej aplikacji, aby było poprawnie używane przez Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Strefa czasowa aplikacji
    |--------------------------------------------------------------------------
    |
    | Tutaj możesz określić domyślną strefę czasową dla swojej aplikacji, która
    | będzie używana przez funkcje daty i czasu PHP. Domyślnie ustawiono na "UTC".
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Lokalizacja aplikacji
    |--------------------------------------------------------------------------
    |
    | Lokalizacja aplikacji określa domyślną lokalizację, która będzie używana
    | przez usługę tłumaczeń. Możesz ustawić tę wartość na dowolną lokalizację
    | obsługiwaną przez aplikację.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Lokalizacja zapasowa
    |--------------------------------------------------------------------------
    |
    | Lokalizacja zapasowa determinuje lokalizację, która będzie używana, gdy
    | bieżąca nie jest dostępna. Możesz zmienić wartość na dowolną z folderu
    | językowego aplikacji.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | Ta lokalizacja zostanie użyta przez bibliotekę Faker PHP, gdy będzie
    | generować dane losowe dla seedingu bazy danych. Na przykład, to będzie
    | używane do generowania numerów telefonów odpowiedniego formatu.
    |
    */

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Klucz szyfrowania
    |--------------------------------------------------------------------------
    |
    | Ten klucz jest używany przez usługę szyfrowania i powinien być ustawiony
    | na losowy, 32-znakowy ciąg. Upewnij się, że ustawiłeś to przed
    | uruchomieniem aplikacji!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'previous_keys' => [],

    /*
    |--------------------------------------------------------------------------
    | Tryb konserwacji
    |--------------------------------------------------------------------------
    |
    | Te opcje konfiguracji określają sterownik używany do określenia i
    | zarządzania stanem "trybu konserwacji" Laravel. Sterownik "cache"
    | pozwoli na kontrolę trybu konserwacji na wielu maszynach.
    |
    | Obsługiwane sterowniki: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dostawcy usług automatycznego ładowania
    |--------------------------------------------------------------------------
    |
    | Dostawcy usług wymienieni tutaj zostaną automatycznie załadowani przy
    | uruchomieniu Twojej aplikacji. Umieść tutaj dowolnego dostawcę usług,
    | który chcesz załadować na żądanie do aplikacji.
    |
    */

    'providers' => [

        /*
         * Dostawcy usług Laravel Framework...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,


        /*
         * Dostawcy usług pakietów...
         */

        // Dodaj ten wpis dla Darryldecode Cart
        Darryldecode\Cart\CartServiceProvider::class,

        /*
         * Dostawcy usług aplikacji...
         */
        App\Providers\AppServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Aliasy klas
    |--------------------------------------------------------------------------
    |
    | Ta tablica aliasów klas będzie zarejestrowana, gdy aplikacja zostanie
    | uruchomiona. Chociaż aliasy są "leniwe" ładowane, nie wpływają na
    | wydajność aplikacji.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        


        // Dodaj ten wpis dla Darryldecode Cart
        'Cart' => Darryldecode\Cart\Facades\CartFacade::class,

    ],

];
