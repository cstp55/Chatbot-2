<?php

namespace Aprilo\Chatbot\Model;

use Magento\Framework\Model\AbstractModel;
use Aprilo\Chatbot\Model\ResourceModel\ChatbotSession as ChatbotSessionResource;

class ChatbotSession extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ChatbotSessionResource::class);
    }

    /**
     * Start a new chatbot session
     */
    public function startSession($userId, $token)
    {
        $this->setData('user_id', $userId);
        $this->setData('token', $token);
        $this->setData('logged_in', 1);
        $this->save();
    }

    /**
     * Validate chatbot session by token
     */
    public function validateSession($token)
    {
        return $this->getCollection()->addFieldToFilter('token', $token)->getFirstItem();
    }

    /**
     * End chatbot session
     */
    public function endSession()
    {
        $this->delete();
    }
}
