<?php

namespace Aprilo\Chatbot\Api;

interface QueryHandlerInterface
{
    /**
     * Handle chatbot queries
     * @return mixed
     */
    public function handleQuery($queryData);
}
