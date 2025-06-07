<?php

namespace Aprilo\Chatbot\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Order extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import Order Data'));
        return $resultPage;
    }

    // protected function _isAllowed()
    // {
    //     return $this->_authorization->isAllowed('Aprilo_Chatbot::chatbot');
    // }
}
