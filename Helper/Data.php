<?php

namespace Aprilo\Chatbot\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public $scopeInterface;
    const XML_PATH_ENABLED = 'chatbot/general/enabled';
    const XML_PATH_OLLAMA_ENDPOINT = 'chatbot/general/ollama_endpoint';

    public function __construct(
        ScopeInterface $scopeInterface
    ){
        $this->scopeInterface = $scopeInterface;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get the Ollama API endpoint from the configuration.
     *
     * @return string
     */
    public function getOllamaEndpoint(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OLLAMA_ENDPOINT,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }
}
