<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class CommandBlueprint
{

    /** @var Argument[] */
    protected $arguments;

    /** @var Flag[] */
    protected $flags;

    /** @var null|string */
    protected $usage;

    /**
     * @param Argument[] $arguments
     * @param Flag[] $flags
     * @param null|string $usage
     */
    public function __construct(array $arguments, array $flags, ?string $usage)
    {
        foreach ($arguments as $argument) {
            $this->arguments[$argument->getName()] = $argument;
        }
        foreach ($flags as $flag) {
            $this->flags[$flag->getName()] = $flag;
        }
        $this->usage = $usage;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $name): ?Argument
    {
        return $this->getArguments()[$name] ?? null;
    }

    /**
     * @return Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getFlag(string $name): ?Flag
    {
        return $this->getFlags()[$name] ?? null;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function getMinimumArgumentCount(): int
    {
        $count = 0;
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isOptional()) {
                $count += $argument->getLength();
            }
        }

        return $count;
    }

}