<?php

namespace Aprilo\Chatbot\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\App\RequestInterface;

class AuthManager
{
    protected $customerSession;
    protected $tokenFactory;
    protected $request;
    protected $encryptor;

    public function __construct(
        Session $customerSession,
        TokenFactory $tokenFactory,
        RequestInterface $request,
        EncryptorInterface $encryptor
    ) {
        $this->customerSession = $customerSession;
        $this->tokenFactory = $tokenFactory;
        $this->request = $request;
        $this->encryptor = $encryptor;
    }

    /**
     * Authenticate user and generate token
     * @return mixed
     */
    public function login($email, $password)
    {
        if ($this->customerSession->isLoggedIn()) {
            return ['status' => 'already_logged_in', 'token' => $this->getSessionToken()];
        }

        try {
            $customer = $this->customerSession->login($email, $password);
            if ($customer) {
                $token = $this->generateToken($customer->getId());
                return ['status' => 'success', 'token' => $token];
            }
        } catch (\Exception $e) {
            throw new Exception(__('Invalid login credentials'), 401);
        }
    }

    /**
     * Generate session token for chatbot
     */
    protected function generateToken($customerId)
    {
        return $this->tokenFactory->create()->createCustomerToken($customerId)->getToken();
    }

    /**
     * Get session token if available
     */
    public function getSessionToken()
    {
        return $this->customerSession->getData('chatbot_token');
    }

    /**
     * Validate session token
     */
    public function validateSession($token)
    {
        $sessionToken = $this->getSessionToken();
        return ($sessionToken && $sessionToken === $token);
    }

    /**
     * Logout chatbot session
     */
    public function logout()
    {
        $this->customerSession->destroy();
        return ['status' => 'logged_out'];
    }
}
