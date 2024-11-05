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
php artisan make:model -mrc Zettel

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

~~~

Route::resource('zettels', ZettelController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);`
~~~

> Esto creará las siguientes rutas:
	* GET /zettels index zettels.index
	* POST /zettels store zettels.store

## Probando nuestras rutas y controladores 

Retornando un mensaje del metodo **index** de nuestra nueva clase [ZettelController]:

~~~

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
}

~~~

## Blade

> Actualicemos nuestro método **index** de nuestra clase **ZettelController** para renderizar una vista Blade

~~~

<?php
 ...
use Illuminate\View\View;
 
class ZettelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    public function index(): View
    {
        return response('Hello, World!');
        return view('zettels.index');
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

La única cosa que nos hace falta es las columnas extras en nuestra base de datos para guardar la relación entre un Zettel y su usuario. 

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


# Mostrando los Zettels

Ahora es momento de mostrarlos en nuestra página:

## Recuperando los Zettels

Actualicemos el método **index** en nuestra clase **ZettelController** para pasar Zettels de cada usuario a nuestra página:

~~~

<?php
 ...
namespace App\Http\Controllers;
 
use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
 
class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        return view('zettels.index', [
            'zettels' => Zettel::with('user')->latest()->get(),
        ]);
    }
 ...
}

~~~

> Aquí hemos utilizado el método de Eloquent **with** para cargar cada usuario asociado al Zettel. También usamos el ámbito **latest** para retonar los registros en orden cronológico inverso.

## Conectando los usuarios a los Zettels

Hemos instruido a Laravel que retorne la relación de **user** para mostrar el nombre del autor del Zettel. Pero, la relación de **user** del Zettel no ha sido definida aún. Así que agregaremos una nueva relación **" belongs to"** a nuestro modelo **Zettel**:

~~~

<?php

 ...
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class Zettel extends Model
{
 ...

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

~~~

> Esta relación es lo inverso a la relación "has many" que creamos en el modelo **User** anteriormente.


## Actualizando nuestra vista


Actualicemos nuestro componente **zettels.index** para mostrar los Zettels abajo de nuestro formulario.

~~~

<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('zettels.store') }}">
            @csrf
            <div>
                <label for="title">{{ __('Title') }}</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    placeholder="{{ __('Enter the title') }}"
                    value="{{ old('title') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <div class="mt-4">
                <textarea
                    name="body"
                    placeholder="{{ __('What\'s on your mind?') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">{{ old('body') }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div class="mt-4">
                <label for="reference">{{ __('Reference') }}</label>
                <input
                    type="text"
                    name="reference"
                    id="reference"
                    placeholder="{{ __('Enter a reference (optional)') }}"
                    value="{{ old('reference') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>
            <x-primary-button class="mt-4">{{ __('Zettel') }}</x-primary-button>
        </form>

        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @foreach ($zettels as $zettel)
            <div class="p-6 flex space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-gray-800">{{ $zettel->user->name }}</span>
                            <small class="ml-2 text-sm text-gray-600">{{ $zettel->created_at->format('j M Y, g:i a') }}</small>
                        </div>
                    </div>
                    <h2 class="mt-4 text-lg text-gray-900">{{ $zettel->title }}</h2>
                    <p class="mt-2 text-gray-800">{{ $zettel->body }}</p>
                    @if ($zettel->reference)
                    <small class="mt-2 text-sm text-gray-600">Referencia: {{ $zettel->reference }}</small>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

    </div>
</x-app-layout>

~~~

> Ahora podemos ver en nuestro navegador el Zettel que ingresamos anteriormente!

# Editando los Zettels

Vamos a agregar la característica de editar los Zettels.

## Routing

Primero actualizaremos el archivo de las rutas para habilitar las rutas **zettels.edit** y **zettels.update** para nuestro controlador. La ruta **zettels.edit** desplegara la forma para editar el Zettel, mientras que la ruta **zettels.update** aceptará los datos del formulario y actualizará el modelo

~~~

<?php
 ...
Route::resource('zettels', ZettelController::class)
    ->only(['index','store','edit','update'])
    ->middleware(['auth','verified']);
 ...

 ~~~

 ## Enlazando la página edit

 Ahora, enlacemos nuestra nueva ruta **zettels.edit**. Usaremos el componente **x-dropdown** que viene con Breeze, el cuál mostrará solo el autor del Zettel. También mostraremos una indicación si el Zettel ha sido editado comparando la fecha del campo **created_at** del Zettel con su fecha **updated_at**:

 ~~~



 ~~~

 ## Creando el formulario edit

 Vamos a crear una nueva vista Blade con un formulario para editar un Zettel. Este es similar al formulario para crear Zettels, excepto que publicaremos a la ruta **zettels.update** y usaremos la directiva **@method** para especificar que estamos haciendo un pedido o request "PATCH". También pre-completaremos el campo con el mensaje existente del Zettel:

 ~~~

<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('zettels.update', $zettel) }}">
            @csrf
            @method('patch')

            <!-- Campo Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $zettel->title) }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <!-- Campo Body -->
            <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-700">{{ __('Body') }}</label>
                <textarea
                    name="body"
                    id="body"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >{{ old('body', $zettel->body) }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <!-- Campo Reference -->
            <div class="mb-4">
                <label for="reference" class="block text-sm font-medium text-gray-700">{{ __('Reference') }}</label>
                <input
                    type="text"
                    name="reference"
                    id="reference"
                    value="{{ old('reference', $zettel->reference) }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>

            <div class="mt-4 space-x-2">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <a href="{{ route('zettels.index') }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>


 ~~~

 ## Actualizando nuestro controlador

 Actualizemos el método **edit** en nuestro **ZettelController** para mostrar nuestro formulario. Laravel automáticamente cargará el modelo Zettel desde la base de datos usando el enlace de ruta del modelo (route model binding) para que podamos pasarlo directamente a la vista.

 Después, actualizaremos el método **update** para validar el request y actualizar la base de datos.

 Aunque solo estamos mostrando el botón de editar al autor de el Zettel, aún necesitamos asegurarnos que el usuario que accese estas rutas esta autorizado:

 ~~~

<?php
 ...
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
 
class ZettelController extends Controller
{
 ...
    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Zettel $zettel): View
    {
        Gate::authorize('update', $zettel);

        return view('zettels.edit', [
            'zettel' => $zettel,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zettel $zettel): RedirectResponse
    {
        Gate::authorize('update', $zettel);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $zettel->update($validated);

        return redirect(route('zettels.index'));
    }
 ...
}

 ~~~

 ## Autorización

 Por defecto, el método **authorize** prevendrá que cualquiera sea capaz de actualizar el Zettel. Podemos especificar quien tiene permitido actualizarlo creando una política del Modelo (**Model Policy**) con el siguiente comando:

 ~~~

 php artisan make:policy ZettelPolicy --model=Zettel

 ~~~

 Esto creará una clase de política en **app/Policies/ZettePolicy.php**, la cuáal podemos actualizar para especificar que solo el autor esta autorizado para actualizar un Zettel:

 ~~~

 <?php
 ...
class ZettelPolicy
{
 ...
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Zettel $zettel): bool
    {
        //
        return $zettel->user()->is($user);
    }
 ...
}

~~~

## Probándolo

Es hora de probarlo. Vamos y editemos algunos Zettels usando el menu desplegable. Si está registrado con otra cuenta de usuario, verás que solo el autor del Zettel podrá editarlo.