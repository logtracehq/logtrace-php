<?php

declare(strict_types=1);

namespace Logtrace;

use RuntimeException;

final class LogtraceException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
    ) {
        parent::__construct(sprintf('logtrace: %d - %s', $statusCode, $message));
    }
}
