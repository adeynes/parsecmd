<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class ParsedCommand
{

    /** @var CommandBlueprint */
    protected $blueprint;

    /** @var string[] */
    protected $arguments;

    /** @var string[] */
    protected $flags;

    /**
     * @param CommandBlueprint blueprint
     * @param string[] $arguments
     * @param string[] $flags
     */
    public function __construct(CommandBlueprint $blueprint, array $arguments, array $flags)
    {
        $this->blueprint = $blueprint;
        $this->arguments = $arguments;
        $this->flags = $flags;
    }

    /**
     * Retrieve the specified arguments
     * @param int[]|int[][] $queries An integer will retrieve the element at that index,
     * an $array will implode the elements from $array[0] with length $array[1]
     * i.e. to get ['hello world', 'argument', 'beans are cool'] from the arguments
     * 'beans are cool hello world argument', you would request [[3, 2], -1, [0, 3]]
     * @return string[]
     */
    /*
    public function get(array $queries): array
    {
        $args = [];

        foreach ($queries as $query) {
            if (is_array($query)) {
                // $request[0] is offset, $request[1] is length. Negative length means start from back
                if ($query[1] < 0) {
                    $query[1] = count($this->getArgs()) - $query[0];
                }

                $args[] = trim(implode(' ', array_slice($this->getArgs(), ...$query)));
            } else {
                // array_slice instead of access to allow negative offsets
                $args[] = array_slice($this->getArgs(), $query, 1)[0];
            }
        }

        return $args;
    }
    */

    /**
     * @param string[] $queries
     * @return string[]
     */
    public function get(array $queries): array
    {
        $arguments = [];

        foreach ($queries as $query) {
            $arguments[$query] = $this->getArgument($query);
        }

        return $arguments;
    }

    public function getBlueprint(): CommandBlueprint
    {
        return $this->blueprint;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $name): ?string
    {
        return $this->getArguments()[$name] ?? null;
    }

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getFlag(string $name): ?string
    {
        return $this->getFlags()[$name] ?? null;
    }

}