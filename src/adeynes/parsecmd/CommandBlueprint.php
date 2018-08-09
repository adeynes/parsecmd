<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class CommandBlueprint
{

    /**
     * Argument[]
     */
    protected $args;

    /**
     * Flag[]
     */
    protected $flags;

    /**
     * @param Argument[] $args
     * @param Flag[] $flags
     */
    public function __construct(array $args, array $flags)
    {
        $this->args = $args;
        $this->flags = $flags;
    }

    /**
     * @return Argument[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

}