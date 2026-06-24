<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authentication Filter - Enterprise Level
 * Protects routes that require user login
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->has('user_id')) {
            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());
            
            // Log unauthorized access attempt
            $this->logUnauthorizedAccess($request);
            
            // Redirect to login with message
            return redirect()->to('/auth/login')
                ->with('error', 'Please login to access this page');
        }
        
        // Check if user account is active
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get('user_id'));
        
        if (!$user || $user['status'] !== 'active') {
            $session->destroy();
            return redirect()->to('/auth/login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }
        
        // Update last activity
        $session->set('last_activity', time());
        
        // Session timeout check (30 minutes)
        if (time() - $session->get('last_activity') > 1800) {
            $session->destroy();
            return redirect()->to('/auth/login')
                ->with('error', 'Your session has expired. Please login again.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security headers
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        
        return $response;
    }
    
    /**
     * Log unauthorized access attempts
     */
    private function logUnauthorizedAccess($request)
    {
        $activityModel = new \App\Models\ActivityLogModel();
        $activityModel->insert([
            'user_id' => null,
            'action' => 'unauthorized_access_attempt',
            'description' => 'Attempted to access: ' . $request->getUri(),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString()
        ]);
    }
}