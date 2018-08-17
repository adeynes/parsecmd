<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class BlueprintFactory
{

    public static function generate(array $blueprint, string $usage): CommandBlueprint
    {
        /** @var Argument[] $arguments */
        $arguments = [];
        /** @var Flag[] $flags */
        $flags = [];

        foreach ($blueprint['arguments'] as $argument_name => $argument) {
            $arguments[$argument_name] = new Argument(
                $argument_name,
                $argument['length'] ?? 1,
                $argument['display'] ?? null,
                $argument['optional'] ?? false
            );
        }

        foreach ($blueprint['flags'] as $flag_name => $flag) {
            $aliases = $flag['aliases'] ?? null;
            // make index higher than all others to merge
            $names = [count($aliases) => $flag['name']] + $aliases;
            $flag = new Flag(
                $flag_name,
                $flag['length'] ?? 1,
                $flag['display'] ?? null,
                $flag['options'] ?? null
            );

            foreach ($names as $name) {
                $flags[$name] = $flag;
            }
        }

        // TODO: ability to use default usage from plugin.yml
        return new CommandBlueprint($arguments, $flags, $usage);
    }

}