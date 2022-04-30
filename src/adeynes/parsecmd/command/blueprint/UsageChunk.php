<?php
declare(strict_types=1);

namespace adeynes\parsecmd\command\blueprint;

abstract class UsageChunk
{

    /** @var string */
    protected string $name;

    /** @var int */
    protected int $length;
    
    /** @var string */
    protected string $display;

    public function __construct(string $name, int $length, ?string $display)
    {
        $this->name = $name;
        $this->length = $length;
        $this->display = $display ?? ucfirst($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLength(): int
    {
        return $this->length;
    }
    
    public function getDisplay(): string
    {
        return $this->display;
    }

}