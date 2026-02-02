<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WelcomeDashboardController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptDashboardController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\CompartidoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\PromptUsoController;
use App\Http\Controllers\AiChatController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPromptController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\CheckAdmin;

/*
|--------------------------------------------------------------------------
| PUBLIC / WELCOME
|--------------------------------------------------------------------------
| La página principal SIEMPRE será el welcome-dashboard (modo lectura).
| Se verá tanto invitado como logeado.
*/
Route::get('/', [PromptDashboardController::class, 'index'])->middleware('auth')
    ->name('welcome');

/*
|--------------------------------------------------------------------------
| Login personalizado
|--------------------------------------------------------------------------
*/
Route::get('/login-custom', function () {
    return view('auth.login-custom');
})->name('login.custom')->middleware('guest');

/*
|--------------------------------------------------------------------------
| Diagnóstico
|--------------------------------------------------------------------------
*/
Route::get('/diagnostico', function () {
    return view('diagnostico');
})->name('diagnostico');

/*
|--------------------------------------------------------------------------
| Dashboard Breeze (si lo sigues usando)
|--------------------------------------------------------------------------
| Puedes dejarlo por compatibilidad, pero tu pantalla real post-login
| la defines como /prompts-dashboard.
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Área del usuario)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Primera pantalla después del login (tu dashboard real de prompts)
    Route::get('/prompts-dashboard', [PromptDashboardController::class, 'index'])
        ->name('prompts.dashboard');

    // CRUD prompts
    Route::resource('prompts', PromptController::class);

    // Versiones
    Route::get('/prompts/{prompt}/versiones', [VersionController::class, 'index'])
        ->name('prompts.versiones.index');
    Route::get('/prompts/{prompt}/versiones/{version}', [VersionController::class, 'show'])
        ->name('prompts.versiones.show');

    // Compartidos
    Route::get('/prompts/{prompt}/compartidos', [CompartidoController::class, 'index'])
        ->name('prompts.compartidos.index');
    Route::get('/prompts/{prompt}/compartidos/create', [CompartidoController::class, 'create'])
        ->name('prompts.compartidos.create');
    Route::post('/prompts/{prompt}/compartidos', [CompartidoController::class, 'store'])
        ->name('prompts.compartidos.store');
    Route::delete('/prompts/{prompt}/compartidos/{compartido}', [CompartidoController::class, 'destroy'])
        ->name('prompts.compartidos.destroy');

    // Usar prompt (auditoría + incremento)
    Route::post('/prompts/{prompt}/usar', [PromptUsoController::class, 'store'])
        ->name('prompts.usar');

    // Actividades
    Route::get('/actividades', [ActividadController::class, 'index'])
        ->name('actividades.index');

    // CRUD apoyo
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    Route::resource('etiquetas', EtiquetaController::class)->except(['show']);

    // AI Chat
    Route::post('/ai/chat', [AiChatController::class, 'send'])
        // ->middleware('throttle:ai-chat')
        ->name('ai.chat.send');

    // AI Chat Debug
    Route::post('/ai/chat-debug', function (\Illuminate\Http\Request $request) {
        try {
            return response()->json([
                'status' => 'debug',
                'mock_mode' => env('OPENAI_MOCK_MODE'),
                'debug' => config('app.debug'),
                'message' => 'Testing...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    })->name('ai.chat.debug');

    // AI Test
    Route::get('/ai/test', function () {
        return response()->json([
            'mock_mode_env' => env('OPENAI_MOCK_MODE'),
            'api_key_exists' => !!config('services.openai.key'),
            'model' => config('services.openai.model'),
            'debug' => config('app.debug'),
            'message' => 'Config OK'
        ]);
    })->name('ai.test');

    // AI Debug (vista)
    Route::get('/ai/debug', function () {
        return view('ai-debug');
    })->name('ai.debug');
});


/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/prompts', [AdminPromptController::class, 'index'])
            ->name('prompts.index');

        Route::get('/prompts/{prompt}/edit', [AdminPromptController::class, 'edit'])
            ->name('prompts.edit');

        Route::put('/prompts/{prompt}', [AdminPromptController::class, 'update'])
            ->name('prompts.update');

        Route::delete('/prompts/{prompt}', [AdminPromptController::class, 'destroy'])
            ->name('prompts.destroy');

        // Gestión de usuarios
        Route::resource('users', AdminUserController::class);
    });

require __DIR__ . '/auth.php';
require __DIR__ . '/profile.php';
