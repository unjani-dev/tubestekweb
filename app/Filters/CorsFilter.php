<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CORS Filter
 * Handles Cross-Origin Resource Sharing for API
 */
class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle preflight requests
        if ($request->getMethod() === 'options') {
            $response = service('response');
            $response->setHeader('Access-Control-Allow-Origin', '*');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->setStatusCode(200);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        return $response;
    }
}

/**
 * API Authentication Filter
 * Validates API key for protected endpoints
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey = $request->getHeaderLine('X-API-Key');
        
        if (!$apiKey) {
            $apiKey = $request->getGet('api_key');
        }

        if (!$apiKey) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'API key is required'
                ])
                ->setStatusCode(401);
        }

        // Verify API key
        if (!$this->verifyApiKey($apiKey)) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ])
                ->setStatusCode(401);
        }

        // Set user_id in request for API
        $userId = $this->getUserIdFromApiKey($apiKey);
        $request->apiUserId = $userId;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    /**
     * Verify API key validity
     */
    private function verifyApiKey($apiKey)
    {
        // In production, check against database
        // For demo, accept any key that starts with 'cp_'
        return strpos($apiKey, 'cp_') === 0;
    }

    /**
     * Get user ID from API key
     */
    private function getUserIdFromApiKey($apiKey)
    {
        // In production, lookup in database
        // For demo, extract from key format: cp_userid_randomstring
        $parts = explode('_', $apiKey);
        return isset($parts[1]) ? (int)$parts[1] : 1;
    }
}