<?php

namespace Aprilo\Chatbot\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\OrderFactory;
use Aprilo\Chatbot\Model\ChatbotSession;

class QueryHandler
{
    protected $customerSession;
    protected $orderFactory;
    protected $chatbotSession;
    protected $curlClient;

    public function __construct(
        CustomerSession $customerSession,
        OrderFactory $orderFactory,
        ChatbotSession $chatbotSession,
        Curl $curlClient
    ) {
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
        $this->chatbotSession = $chatbotSession;
        $this->curlClient = $curlClient;
    }

    /**
     * Handle chatbot query and return response
     */
    public function handleQuery($queryData)
    {
        $sessionToken = $queryData['token'] ?? null;
        $queryType = $queryData['query_type'] ?? 'general';

        // Validate user session
        $session = $this->chatbotSession->validateSession($sessionToken);
        $loggedIn = $session ? true : false;

        // If user-specific query and not logged in, request login
        if (!$loggedIn && in_array($queryType, ['order_status', 'refund_status'])) {
            return ['error' => 'Please log in to access this information.'];
        }

        // User-Specific Queries
        if ($queryType == 'order_status') {
            return $this->fetchOrderStatus($queryData['order_id']);
        }

        // Call AI Chatbot Service for General Queries
        return $this->fetchAIResponse($queryData['query'], $loggedIn);
    }

    /**
     * Fetch order status from Magento
     */
    protected function fetchOrderStatus($orderId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        return $order->getId()
            ? ['order_id' => $orderId, 'status' => $order->getStatus()]
            : ['error' => 'Order not found'];
    }

    /**
     * Fetch response from AI Chatbot
     */
    protected function fetchAIResponse($query, $loggedIn)
    {
        $apiUrl = "http://localhost:5005/chatbot/query";

        $postData = [
            'query' => $query,
            'logged_in' => $loggedIn
        ];

        $this->curlClient->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curlClient->post($apiUrl, json_encode($postData));
        $response = json_decode($this->curlClient->getBody(), true);

        return $response ?: ['error' => 'AI service unavailable'];
    }
}
