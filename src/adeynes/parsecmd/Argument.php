<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class Argument extends UsageChunk
{

    /** @var bool */
    protected $is_optional;

    public function __construct(string $name, int $length, bool $is_optional)
    {
        $this->is_optional = $is_optional;
        parent::__construct($name, $length);
    }

    public function isOptional(): bool
    {
        return $this->is_optional;
    }

}