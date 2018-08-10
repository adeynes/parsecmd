<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

abstract class UsageChunk
{

    /** @var int */
    protected $name;

    /** @var int */
    protected $length;

    /** @var bool */
    protected $is_optional;

    public function __construct(string $name, int $length, bool $is_optional = false)
    {
        $this->name = $name;
        $this->length = $length;
        $this->is_optional = $is_optional;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function isOptional(): bool
    {
        return $this->is_optional;
    }

}