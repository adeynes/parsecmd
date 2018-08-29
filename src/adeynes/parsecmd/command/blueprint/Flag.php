<?php
declare(strict_types=1);

namespace adeynes\parsecmd\command\blueprint;

class Flag extends UsageChunk
{
    
    /** @var mixed[] */
    protected $options;
    
    public function __construct(string $name, int $length, ?string $display, array $options = [])
    {
        $this->options = $options;
        parent::__construct($name, $length, $display);
    }

    public function hasOptions(): bool
    {
        return count($this->getOptions()) > 0;
    }
    
    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

}