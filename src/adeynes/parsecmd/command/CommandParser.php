<?php
declare(strict_types=1);

namespace adeynes\parsecmd\command;

class CommandParser
{

    public static function parse(Command $command, array $arguments): ParsedCommand
    {
        $flags = [];
        $copy = $arguments;
        $blueprint = $command->getBlueprint();

        foreach ($arguments as $i => $argument) {
            if (strpos($argument, '-') !== 0) continue;

            // Avoid getting finding the flag twice; only first time counts
            $flag_name = $blueprint->getFlagAlias(substr($argument, 1));
            if (isset($flags[$flag_name])) continue;

            if (is_null($flag = $blueprint->getFlag($flag_name))) continue;

            $length = $flag->getLength();
            if ($length === -1) $length = count($copy);
            $flags[$flag->getName()] = implode(' ', array_slice($copy, $i + 1, $length));

            // Remove tag & tag parameters
            // array_diff_key() doesn't reorder the keys
            $arguments = array_diff_key($arguments, array_flip(range($i, $i + $length)));
        }

        $parsed_arguments = [];

        foreach ($blueprint->getArguments() as $blueprint_argument) {
            $length = $blueprint_argument->getLength();
            if ($length < 0) {
                $length = count($arguments) + $length + 1;
            }

            $argument = implode(
                ' ',
                array_splice($arguments, 0, $length)
            );

            if ($argument === '') $argument = null;

            $parsed_arguments[$blueprint_argument->getName()] = $argument;
        }

        return new ParsedCommand($blueprint, $parsed_arguments, $flags);
    }

    public static function parseDuration(string $duration): int
    {
        $parts = str_split($duration);
        $time_units = ['y' => 'year', 'M' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'm' => 'minute'];
        $time = '';
        $i = -1;

        foreach ($parts as $part) {
            ++$i;
            if (!isset($time_units[$part])) continue;
            $unit = $time_units[$part];

            $n = implode('', array_slice($parts, 0, $i));
            $time .= "$n $unit ";
            array_splice($parts, 0, $i + 1);

            $i = -1;
        }

        $time = trim($time);

        return $time === '' ? time() : strtotime($time);
    }

}