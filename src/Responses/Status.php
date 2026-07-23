<?php

namespace SmartDato\MondialRelayShipping\Responses;

use SmartDato\MondialRelayShipping\Enums\StatusLevel;
use Spatie\LaravelData\Data;

class Status extends Data
{
    public function __construct(
        public int $code,
        public StatusLevel $level,
        public string $message,
    ) {}

    public function isCritical(): bool
    {
        return $this->level === StatusLevel::CriticalError;
    }

    public function isError(): bool
    {
        return $this->level === StatusLevel::Error;
    }

    public function isWarning(): bool
    {
        return $this->level === StatusLevel::Warning;
    }
}
