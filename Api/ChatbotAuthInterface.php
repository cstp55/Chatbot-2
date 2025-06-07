<?php

namespace Aprilo\Chatbot\Api;

interface ChatbotAuthInterface
{
    /**
     * Login chatbot session
     */
    public function login($email, $password);

    /**
     * Logout chatbot session
     */
    public function logout();

    /**
     * Verify chatbot session
     */
    public function verifySession($token);
}
