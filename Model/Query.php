<?php

namespace Aprilo\Chatbot\Model;

use Aprilo\Chatbot\Api\QueryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Query implements QueryInterface
{
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
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

    private function callLlamaModel($query)
    {
        // Example: Append order data to the query
        $additionalContext = "i wants order details. Latest order status: Pending, Order ID: 12345.";
        $prompt = $query . "\n\n" . $additionalContext;
    
        $llamaResponse = shell_exec("python3 /path/to/llama_script.py '" . escapeshellarg($prompt) . "'");
        return $llamaResponse;
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
