<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use App\Models\AiInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class AiChatController extends Controller
{
    public function send(Request $request)
    {
        // Validar entrada
        try {
            $validated = $request->validate([
                'message' => 'required|string|min:1|max:2000',
                'context' => 'nullable|array',
                'context.current_prompt' => 'nullable|string|max:8000',
                'context.goal' => 'nullable|string|max:300',
                'context.tone' => 'nullable|string|max:50',
                'prompt_id' => 'nullable|exists:prompts,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validación fallida',
                'messages' => $e->errors(),
            ], 422);
        }

        try {
            // Instanciar servicio
            $aiService = new AiChatService();

            // Preparar contexto - asegurar que sea un array o null
            $context = $validated['context'] ?? null;
            if ($context && !is_array($context)) {
                $context = null;
            }

            // Enviar a OpenAI
            $reply = $aiService->sendMessage(
                $validated['message'],
                $context
            );

            // Guardar interacción en BD (auditoría) - COMENTADO: tabla no existe aún
            // if (Auth::check()) {
            //     AiInteraction::create([
            //         'user_id' => Auth::id(),
            //         'prompt_id' => $validated['prompt_id'] ?? null,
            //         'request_json' => json_encode([
            //             'message' => $validated['message'],
            //             'context' => $context,
            //         ]),
            //         'response_json' => json_encode([
            //             'reply' => $reply,
            //         ]),
            //     ]);
            // }

            return response()->json([
                'reply' => $reply,
            ], 200);

        } catch (Exception $e) {
            $statusCode = 500;
            $message = 'Error al procesar tu solicitud';

            if (str_contains($e->getMessage(), 'Falta configurar OPENAI_API_KEY')) {
                $statusCode = 500;
                $message = 'Error de configuración del servidor. Contacta al administrador.';
            } elseif (str_contains($e->getMessage(), 'autenticación')) {
                $statusCode = 502;
                $message = 'Error de autenticación con OpenAI. Por favor, intenta más tarde.';
            } elseif (str_contains($e->getMessage(), 'rate limit')) {
                $statusCode = 429;
                $message = 'Has alcanzado el límite de solicitudes. Intenta nuevamente en unos minutos.';
            } elseif (str_contains($e->getMessage(), 'servidor')) {
                $statusCode = 502;
                $message = 'El servicio de IA no está disponible. Intenta más tarde.';
            } elseif (str_contains(strtolower($e->getMessage()), 'time') || str_contains($e->getMessage(), 'cURL error 28')) {
                $statusCode = 504;
                $message = 'La IA tardó demasiado en responder. Intenta con una solicitud más breve.';
            }

            return response()->json([
                'error' => $message,
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }
}
