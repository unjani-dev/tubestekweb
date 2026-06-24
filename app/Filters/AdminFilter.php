<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Authorization Filter - Enterprise Level
 * Protects admin-only routes
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // First check if user is logged in
        if (!$session->has('user_id')) {
            $session->set('redirect_url', current_url());
            return redirect()->to('/auth/login')
                ->with('error', 'Please login to access this page');
        }
        
        // Check if user has admin role
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get('user_id'));
        
        if (!$user || $user['role'] !== 'admin') {
            // Log unauthorized admin access attempt
            $this->logUnauthorizedAdminAccess($request, $user);
            
            // Redirect to user dashboard
            return redirect()->to('/dashboard')
                ->with('error', 'You do not have permission to access the admin panel');
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            $session->destroy();
            return redirect()->to('/auth/login')
                ->with('error', 'Your account has been suspended');
        }
        
        // Update last activity
        $session->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add strict security headers for admin panel
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->setHeader('Referrer-Policy', 'no-referrer');
        
        return $response;
    }
    
    /**
     * Log unauthorized admin access attempts (Security Breach Attempt)
     */
    private function logUnauthorizedAdminAccess($request, $user)
    {
        $activityModel = new \App\Models\ActivityLogModel();
        $activityModel->insert([
            'user_id' => $user ? $user['id'] : null,
            'action' => 'unauthorized_admin_access',
            'description' => 'SECURITY ALERT: Attempted to access admin panel - ' . $request->getUri(),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString()
        ]);
        
        // Optional: Send email alert to admin
        // $this->sendSecurityAlert($user, $request);
    }
}