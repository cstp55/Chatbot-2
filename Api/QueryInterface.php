<?php

namespace Aprilo\Chatbot\Api;

interface QueryInterface
{
    /**
     * Process chatbot queries
     * @param string $query
     * @return string
     */
    public function processQuery($query);
}
