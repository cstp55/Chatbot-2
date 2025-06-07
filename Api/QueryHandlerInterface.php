<?php

namespace Aprilo\Chatbot\Api;

interface QueryHandlerInterface
{
    /**
     * Handle chatbot queries
     */
    public function handleQuery($queryData);
}
