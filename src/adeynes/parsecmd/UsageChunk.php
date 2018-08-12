<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

abstract class UsageChunk
{

    /** @var int */
    protected $name;

    /** @var int */
    protected $length;

    public function __construct(string $name, int $length)
    {
        $this->name = $name;
        $this->length = $length;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLength(): int
    {
        return $this->length;
    }

}