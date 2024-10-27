# Creando una app para manejar mi Zettelkasten

## Tecnologías

+ Lenguaje: PHP
+ framework: Laravel Herd
+ databse: SQLite

## Instalación

-  Crear nueva aplicación en el Commnd Prompt (CMD)

~~~
cd %USERPROFILE%\Herd
laravel new my-project
cd my-project
herd open
herd edit
~~~

- usando Blade
- con soporte para Black theme
- git


## Prelims

+ registrar  usuario
+ loguear usuario

## Crear Zettels

+ crear un modelo, migracion y controlador de recursos para nuestros Zettels con el siguiente comando:

~~~
php artisan make:model -mrc Chirp
~~~
> Este comando creará 3 archivos para nosotros:
* app/Models/Zettel.php - El modelo Eloquent
* database/migrations/<timestamp>_create_zettels_table.php - La migración de la base de datos que creará nuestra tabla
* app/Http/Controllers/ZettelController.php - El controlador HTTP que tomará los request y regresará las respuestas.


## Routing

+ Crearemos las URLs para nuestro controlador.
+ Para comenzar, habilitaremos dos rutas:
	- La ruta **index** mostrará nuestra formulario y una lista de Zettels
	- La ruta **store** se usará para guardar nuevos Zettels

+ Colocaremos también dos rutas detras de dos **middleware**:
	- El middleware **auth** asegurará que solo los usuarios logueados puedan accesar la ruta.
	- El middleware **verified** se utilizará si decidimos habilitar **verificacion de email**.

`Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);`

> Esto creará las siguientes rutas:
	* GET /zettels index zettels.index
	* POST /zettels store zettels.store

## Probando nuestras rutas y controladores retornando un mensaje del metodo **index** de nuestra nueva clase [ZettelController]:

`
<?php

namespace App\Http\Controllers;

...
use Illuminate\Http\Response;

class ZettelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
        return response('Hello, World!');
    }
...
}`

## Blade

> Actualicemos nuestro método **index** de nuestra clase **ZettelController** para renderizar una vista Blade

~~~
<?php
 ...
use Illuminate\View\View;
 
class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    public function index(): View
    {
        return response('Hello, World!');
        return view('chirps.index');
    }
 ...
}
~~~

> Podemos crear nuestra plantilla de vista **Blade** con un formulario para crear nuevos Zettels:

~~~
<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('zettels.store') }}">
            @csrf
            <textarea
                name="message"
                placeholder="{{ __('What\'s on your mind?') }}"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >{{ old('message') }}</textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <x-primary-button class="mt-4">{{ __('Zettel') }}</x-primary-button>
        </form>
    </div>
</x-app-layout>
~~~

## Menú de Navegación

Tomemos un momento para agregar un enlace al menú de navegación. Actualicemos el componente **navigation.blade.php** para agregar un elemento del menú:

~~~
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    <x-nav-link :href="route('zettels.index')" :active="request()->routeIs('zettels.index')">
        {{ __('Zettels') }}
    </x-nav-link>
</div>
~~~


## Guardando el Zettel

Nuestro formulario ha sido configurado para publicar notas a la ruta **zettels.store** que fue create anteriorment. Actualicemos el metodo **store** en nuestra clase **ZettelController** para validar los datos y crear un nuevo zettel:

~~~
<?php
 ...
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
 
class ZettelController extends Controller
{
 ...
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $request->user()->zettels()->create($validated);

        return redirect(route('zettels.index'));
    }
 ...
}

~~~

## Creando una relación

Hemos llamado un metodo zettels en el objeto **$request->user()**. necesitamos crear este método en nuestro model **User** para definir una relación **"has many"**:

~~~
<?php
 ...
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 
class User extends Authenticatable
{
 ...
    public function zettels(): HasMany
    {
        return $this->hasMany(Zettel::class);
    }
}
~~~

## Protección de asignación masiva

Pasar todo los datos desde un pedido (request) a nuestro modelo puedo ser riesgoso, Agreguemos la propiedad $fillable a nuestro modelo Zettel para habilitar la asignación masiva para los atributos **title**, **body** y **reference**


## Actualizando la migración

> La única cosa que nos hace falta es las columnas extras en nuestra base de datos para guardar la relación entre un Zettel y su usuario. 

~~~
<?php
 ...
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zettels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }
 ...
};
~~~

No hemos migrado la base de datos aún, así que lo haremos ahora:

~~~
php artisan migrate
~~~

