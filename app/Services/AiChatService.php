<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AiChatService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.key');
        $this->model = (string) config('services.openai.model', 'gpt-4o-mini');

        if (empty($this->apiKey)) {
            throw new Exception('Falta configurar OPENAI_API_KEY en el archivo .env');
        }
    }

    /**
     * Envía un mensaje a OpenAI y obtiene una respuesta.
     *
     * @param string $userMessage Mensaje del usuario
     * @param array|null $context Contexto adicional (current_prompt, goal, tone)
     * @return string Respuesta de OpenAI
     * @throws Exception
     */
    public function sendMessage(string $userMessage, ?array $context = null): string
    {
        // MODO SIMULADO - Para testing sin gastar dinero
        // Activar con: OPENAI_MOCK_MODE=true en .env
        $mockMode = env('OPENAI_MOCK_MODE') === 'true';
        if ($mockMode) {
            return $this->getMockResponse($userMessage, $context);
        }

        // Construir el mensaje del usuario con contexto
        $messageWithContext = $this->buildMessageWithContext($userMessage, $context);

        // Sistema prompt fijo
        $systemPrompt = $this->getSystemPrompt();

        // DEBUG: Registrar intento en logs para verificar que la key se lee
        Log::info('AiChatService: Conectando a OpenAI... Key inicia con: ' . substr($this->apiKey, 0, 7));

        try {
            // Llamar a OpenAI Completions API (no es Responses, usamos Messages/Chat)
            // withoutVerifying() evita errores de certificado SSL en local
            $response = Http::withoutVerifying()
                ->timeout(120) // Aumentar tiempo de espera a 120 segundos para respuestas largas
                ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                        'model' => $this->model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $systemPrompt,
                            ],
                            [
                                'role' => 'user',
                                'content' => $messageWithContext,
                            ],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 1500,
                    ]);

            // Validar respuesta
            if ($response->failed()) {
                $statusCode = $response->status();
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Error desconocido de OpenAI';

                Log::error('OpenAI API Error', [
                    'status' => $statusCode,
                    'message' => $errorMessage,
                    'model' => $this->model
                ]);

                if ($statusCode === 401 || $statusCode === 403) {
                    throw new Exception('Error de autenticación con OpenAI: ' . $errorMessage);
                }

                if ($statusCode === 429) {
                    throw new Exception('Límite de rate limit alcanzado en OpenAI. Intenta más tarde.');
                }

                if ($statusCode >= 500) {
                    throw new Exception('El servidor de OpenAI está experimentando problemas. Intenta más tarde.');
                }

                throw new Exception('Error en OpenAI: ' . $errorMessage);
            }

            $data = $response->json();

            // Extraer el texto de la respuesta
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new Exception('Respuesta inesperada de OpenAI: no se encontró contenido.');
            }

            // CONFIRMACIÓN: Registrar en logs que la respuesta vino de la API real
            Log::info('AiChatService: Respuesta generada por OpenAI recibida correctamente.');

            return $data['choices'][0]['message']['content'];

        } catch (Exception $e) {
            // Re-lanzar excepciones con mensajes útiles
            throw $e;
        }
    }

    /**
     * Retorna una respuesta simulada para testing sin gastar créditos
     *
     * @param string $userMessage
     * @param array|null $context
     * @return string
     */
    private function getMockResponse(string $userMessage, ?array $context = null): string
    {
        $responses = [
            'mejora' => 'Tu prompt está bien estructurado. Aquí algunas mejoras:

1. **Sé más específico**: En lugar de "genera contenido", especifica exactamente qué tipo de contenido.
2. **Agrega ejemplos**: Proporciona 1-2 ejemplos de lo que esperas.
3. **Define el tono**: Usa palabras como "formal", "casual", "profesional", etc.

**Ejemplo mejorado:**
"Genera un email profesional de seguimiento para un cliente que no ha respondido en 2 semanas. Tono cordial pero firme. Máximo 150 palabras."',

            'prompt' => 'Para escribir un buen prompt debes considerar:

1. **Claridad**: Sé específico sobre qué quieres
2. **Contexto**: Proporciona información de fondo relevante
3. **Ejemplos**: Muestra qué tipo de respuesta esperas
4. **Restricciones**: Define límites de longitud, tono, etc.
5. **Objetivo**: Indica para qué lo necesitas

**Tip**: Un buen prompt es como dar instrucciones a un colega. Sé tan específico como sea posible.',

            'analiza' => 'Análisis del prompt:

**Fortalezas:**
- Tiene una estructura clara
- Define objetivos específicos

**Áreas de mejora:**
- Podría ser más conciso
- Faltaría especificar el tono deseado

**Recomendación:**
Intenta seguir el patrón CEPAT:
- **C**ontexto: ¿Cuál es la situación?
- **E**specificidad: ¿Qué exactamente necesitas?
- **P**regunta: ¿Cuál es tu pregunta principal?
- **A**cciones: ¿Qué esperas que haga el AI?
- **T**ono: ¿Qué tono prefieres?',

            'test' => 'Este es un mensaje de test desde el modo simulado. ¡El chatbot está funcionando correctamente! 

Para usar OpenAI real, necesitas:
1. Agregar créditos a tu cuenta en https://platform.openai.com/account/billing/overview
2. Configurar un método de pago válido

Por ahora, puedes seguir testeando sin gastar dinero con este modo simulado.',
        ];

        // Detectar qué tipo de respuesta enviar
        $lowerMessage = strtolower($userMessage);

        if (strpos($lowerMessage, 'mejorar') !== false || strpos($lowerMessage, 'mejora') !== false) {
            return $responses['mejora'];
        } elseif (strpos($lowerMessage, 'analiza') !== false || strpos($lowerMessage, 'análisis') !== false) {
            return $responses['analiza'];
        } elseif (strpos($lowerMessage, 'prompt') !== false || strpos($lowerMessage, 'buen') !== false) {
            return $responses['prompt'];
        }

        return $responses['test'];
    }

    /**
     * Construye el mensaje del usuario incluyendo contexto.
     *
     * @param string $userMessage
     * @param array|null $context
     * @return string
     */
    private function buildMessageWithContext(string $userMessage, ?array $context = null): string
    {
        $parts = [];

        if ($context) {
            if (!empty($context['current_prompt'])) {
                $parts[] = "PROMPT ACTUAL:\n" . $context['current_prompt'];
            }

            if (!empty($context['goal'])) {
                $parts[] = "OBJETIVO:\n" . $context['goal'];
            }

            if (!empty($context['tone'])) {
                $parts[] = "TONO:\n" . $context['tone'];
            }
        }

        $parts[] = "MENSAJE DEL USUARIO:\n" . $userMessage;

        return implode("\n\n", $parts);
    }

    /**
     * Retorna el system prompt fijo para el asistente.
     *
     * @return string
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres un asistente de inteligencia artificial útil y versátil integrado en la aplicación PromptVault.

Tu objetivo es ayudar al usuario con lo que necesite, ya sea redactar prompts, generar código SQL, responder preguntas o analizar textos.

INSTRUCCIONES:
1. Responde siempre en español.
2. Sé directo y útil.
3. Si te piden código (como SQL para bases de datos), genéralo directamente.
4. Si te piden mejorar un prompt, actúa como experto en prompts.
5. Estructura tu respuesta con párrafos claros y listas para facilitar la lectura.

TONO: Profesional, amable, experto pero accesible.
FORMATO: Usa markdown (negritas, listas, bloques de código) para mejor legibilidad.
PROMPT;
    }
}
