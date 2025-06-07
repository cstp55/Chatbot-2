<?php

namespace Aprilo\Chatbot\Model;

use Aprilo\Chatbot\Api\QueryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Aprilo\Chatbot\Helper\Data as ChatbotHelper;

class Query implements QueryInterface
{
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $logger;
    protected $curl;
    protected $chatbotHelperl;
    protected $jsonFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        JsonFactory $jsonFactory,
        Curl $curl,
        ChatbotHelper $chatbotHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->curl = $curl;
        $this->chatbotHelperl = $chatbotHelper;
        $this->jsonFactory = $jsonFactory;
    }

    public function processQuery($query)
    {
        try {
            // Check for predefined commands
            if (stripos($query, 'order status') !== false) {
                return $this->getOrderStatusById($query);
            } elseif (stripos($query, 'order history') !== false) {
                return $this->getOrderHistory();
            }

            // Send other queries to LLaMA
            $llamaResponse = $this->callLlamaModel($query);

            // If LLaMA responds, send it to the user
            if (!empty($llamaResponse)) {
                return $llamaResponse;
            }

            // Fallback response
            return "Sorry, I couldn't process your request. Can I help with something else?";
        } catch (\Exception $e) {
            $this->logger->error("Error processing query: " . $e->getMessage());
            return "An error occurred while processing your request. Please try again later.";
        }
    }

    private function callLlamaModel($queryData)
    {
        $ollamaUrl = $this->chatbotHelper->getOllamaEndpoint();

        // Check if the endpoint is configured
        if (empty($ollamaUrl)) {
            $this->logger->error('Chatbot API: Ollama endpoint is not configured in Magento admin.');
            throw new WebapiException(
                new Phrase('Sorry, the chatbot service is not configured.'),
                0,
                WebapiException::HTTP_SERVICE_UNAVAILABLE
            );
        }
        
        try {
            // Note: Use $queryData here, as it's the incoming parameter
            $this->curl->setHeaders([
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($queryData) // Use $queryData length
            ]);

            $this->curl->post($ollamaUrl, $queryData); // Use $queryData here

            $ollamaResponse = $this->curl->getBody();
            $statusCode = $this->curl->getStatus();

            if ($statusCode == 200) {
                return $ollamaResponse;
            } else {
                $this->logger->error('Chatbot API: Received non-200 status from Ollama.', ['status' => $statusCode, 'response' => $ollamaResponse]);
                throw new WebapiException(
                    new Phrase('Error from upstream chatbot service.'),
                    0,
                    $statusCode
                );
            }
        } catch (WebapiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical('Chatbot API Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new WebapiException(
                new Phrase('An internal server error occurred while processing the query.'),
                0,
                WebapiException::HTTP_INTERNAL_ERROR
            );
        }
    }

    private function getOrderStatusById($query)
    {
        // Extract order ID from the query (you can improve this with regex or NLP)
        $orderId = preg_replace('/[^0-9]/', '', $query);

        if (!$orderId) {
            return "Please provide a valid order ID.";
        }

        try {
            $order = $this->orderRepository->get($orderId);
            return "Order #{$orderId} is currently " . $order->getStatus() . ".";
        } catch (\Exception $e) {
            return "Unable to find order with ID {$orderId}. Please check the ID and try again.";
        }
    }

    private function getOrderHistory()
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $orders = $this->orderRepository->getList($searchCriteria)->getItems();

            if (empty($orders)) {
                return "You have no order history.";
            }

            $response = "Here is your order history:\n";
            foreach ($orders as $order) {
                $response .= "- Order #{$order->getIncrementId()} ({$order->getStatus()})\n";
            }
            return $response;
        } catch (\Exception $e) {
            return "Unable to retrieve your order history at the moment.";
        }
    }
}
