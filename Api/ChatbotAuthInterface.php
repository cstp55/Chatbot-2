<?php

namespace Aprilo\Chatbot\Api;

interface ChatbotAuthInterface
{
   /**
     * Authenticates a chatbot user and returns an authentication token object.
     *
     * @param string $username The chatbot username
     * @param string $password The chatbot password
     * @return \Aprilo\Chatbot\Api\Data\AuthTokenInterface|null
     */
    public function login(string $username, string $password);

    /**
     * Logout chatbot session
     * @return \Aprilo\Chatbot\Api\Data\AuthTokenInterface|null
     */
    public function logout();

    /**
     * Verify chatbot session
     * @return mixed
     */
    public function verifySession($token);
}
