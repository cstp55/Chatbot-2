<?php

namespace Aprilo\Chatbot\Controller;

use Magento\Framework\App\Action\Context;
use Aprilo\Chatbot\Model\AuthManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;

class Login
{
    protected $authManager;
    protected $resultJsonFactory;
    protected $request;

    public function __construct(
        Context $context,
        AuthManager $authManager,
        JsonFactory $resultJsonFactory,
        RequestInterface $request
    ) {
        $this->authManager = $authManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = json_decode($this->request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $result->setData(['error' => 'Invalid credentials']);
        }

        $response = $this->authManager->login($data['email'], $data['password']);
        return $result->setData($response);
    }
}
