<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::NoShow => 'No-show',
            default => ucfirst($this->value),
        };
    }

    /** Bootstrap colour used for badges in the admin. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'secondary',
            self::Rejected => 'danger',
            self::NoShow => 'dark',
        };
    }

    /** Whether an appointment in this status still occupies its time slot. */
    public function holdsSlot(): bool
    {
        return ! in_array($this, [self::Cancelled, self::Rejected], true);
    }

    /** Allowed status transitions (the appointment state machine). */
    public function canTransitionTo(self $to): bool
    {
        return in_array($to, match ($this) {
            self::Pending => [self::Confirmed, self::Rejected, self::Cancelled],
            self::Confirmed => [self::Completed, self::Cancelled, self::NoShow],
            self::Completed, self::Cancelled, self::Rejected, self::NoShow => [],
        }, true);
    }

    /** Statuses that can still be rescheduled. */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    /** @return list<string> */
    public static function slotHoldingValues(): array
    {
        return array_map(fn (self $s) => $s->value, array_filter(self::cases(), fn (self $s) => $s->holdsSlot()));
    }
}
