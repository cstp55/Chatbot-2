<?php

namespace Aprilo\Chatbot\Controller;

use Aprilo\Chatbot\Model\QueryHandler;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;

class Query
{
    protected $queryHandler;
    protected $resultJsonFactory;
    protected $request;

    public function __construct(
        QueryHandler $queryHandler,
        JsonFactory $resultJsonFactory,
        RequestInterface $request
    ) {
        $this->queryHandler = $queryHandler;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $queryData = json_decode($this->request->getContent(), true);
        $response = $this->queryHandler->handleQuery($queryData);
        return $result->setData($response);
    }
}
