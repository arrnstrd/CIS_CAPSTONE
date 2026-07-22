<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown whenever a classroom scan cannot proceed (invalid QR, ownership
 * mismatch, outside the attendance window, duplicate scan, etc).
 *
 * Carries the intended HTTP status code so the controller can translate it
 * directly into a JSON response without re-deriving the status from the
 * message text.
 */
class ClassroomScanRejectedException extends Exception
{
    public int $statusCode;

    public function __construct(string $message, int $statusCode = 400)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }
}
