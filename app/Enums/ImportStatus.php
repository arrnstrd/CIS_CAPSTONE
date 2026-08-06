<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Processing = 'processing';
    case Completed = 'completed';
    case CompletedWithIssues = 'completed_with_issues';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public static function terminalStates(): array
    {
        return [
            self::Completed,
            self::CompletedWithIssues,
            self::Cancelled,
            self::Failed,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, self::terminalStates(), true);
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    private function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Validated, self::Cancelled, self::Failed],
            self::Validated => [self::Processing, self::Cancelled, self::Failed],
            self::Processing => [self::Completed, self::CompletedWithIssues, self::Failed],
            default => [],
        };
    }
}
