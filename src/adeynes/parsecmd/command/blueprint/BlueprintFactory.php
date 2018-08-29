<?php
declare(strict_types=1);

namespace adeynes\parsecmd\command\blueprint;

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

        $flag_aliases = [];

        foreach ($blueprint['flags'] as $flag_name => $flag) {
            $flag = new Flag(
                $flag_name,
                $flag['length'] ?? 1,
                $flag['display'] ?? null,
                $flag['options'] ?? []
            );
            $flags[$flag_name] = $flag;

            $aliases = $flag['aliases'] ?? [];
            $aliases[] = $flag_name;
            foreach ($aliases as $alias) {
                $flag_aliases[$alias] = $flag_name;
            }
        }

        $blueprint = new CommandBlueprint($arguments, $flags, $usage);
        $blueprint->addFlagAliases($flag_aliases);

        return $blueprint;
    }

}