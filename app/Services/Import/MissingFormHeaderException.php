<?php

namespace App\Services\Import;

/**
 * Thrown by SpreadsheetParser when the SF1 form-level header cells
 * (Grade Level at T6 / Section at W6) are missing or invalid.
 *
 * BulkImportService catches this to record a single blocking issue
 * instead of letting every per-row validation fail on the same root cause.
 */
class MissingFormHeaderException extends \RuntimeException {}
