<?php

namespace MrLinter\Metrics\Storage\PromPushGw\Client;

/**
 * Interface for PushGateway Client.
 */
interface Client
{
    /**
     * Replace job metrics data.
     *
     * @param non-empty-string $job
     *
     * @throws ReplaceException
     */
    public function replace(string $job, string $data): void;
}
