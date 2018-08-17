<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

abstract class UsageChunk
{

    /** @var int */
    protected $name;

    /** @var int */
    protected $length;
    
    /** @var string */
    protected $display;

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