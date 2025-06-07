<?php
namespace Aprilo\Chatbot\Controller\Proxy;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Aprilo\Chatbot\Helper\Data as ChatbotHelper;
use Psr\Log\LoggerInterface;

class Index extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ChatbotHelper
     */
    protected $chatbotHelper;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Curl $curl,
        ChatbotHelper $chatbotHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $jsonFactory;
        $this->curl = $curl;
        $this->chatbotHelper = $chatbotHelper;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $ollamaUrl = $this->chatbotHelper->getOllamaEndpoint();

        // Check if the endpoint is configured
        if (empty($ollamaUrl)) {
            $this->logger->error('Chatbot Proxy: Ollama endpoint is not configured in Magento admin.');
            return $result->setData(['error' => true, 'message' => 'Sorry, I encountered an error. Please try again..'])->setHttpResponseCode(503);
        }
        
        try {
            // Get the raw POST body from the React app request
            $reactRequestBody = $this->getRequest()->getContent();

            // Set headers for the request to Ollama
            $this->curl->setHeaders([
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($reactRequestBody)
            ]);

            // Make the POST request to the Ollama server
            $this->curl->post($ollamaUrl, $reactRequestBody);

            // Get the response from Ollama
            $ollamaResponse = $this->curl->getBody();
            $statusCode = $this->curl->getStatus();
            
            if ($statusCode == 200) {
                 // Pass the raw JSON response from Ollama back to the React app
                 $result->setHttpResponseCode($statusCode);
                 $result->setHeader('Content-Type', 'application/json', true);
                 $result->setJsonData($ollamaResponse);
            } else {
                 $this->logger->error('Chatbot Proxy: Received non-200 status from Ollama.', ['status' => $statusCode, 'response' => $ollamaResponse]);
                 return $result->setData(['error' => true, 'message' => 'Error from upstream service.'])->setHttpResponseCode($statusCode);
            }

        } catch (\Exception $e) {
            $this->logger->critical('Chatbot Proxy Error: ' . $e->getMessage());
            return $result->setData(['error' => true, 'message' => 'An internal server error occurred.'])->setHttpResponseCode(500);
        }

        return $result;
    }
}