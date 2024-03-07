<?php

namespace Hallewood\LogSnag;

class InvalidMessageException extends \Exception
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
