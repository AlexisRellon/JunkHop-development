<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Send success response
     *
     * @param  mixed  $result
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    public function sendResponse($result, string $message = '', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send error response
     *
     * @param  string  $error
     * @param  array|string|null  $errorMessages
     * @param  int  $code
     * @return JsonResponse
     */
    public function sendError(string $error, $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
