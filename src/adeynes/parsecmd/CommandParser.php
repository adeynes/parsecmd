<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class CommandParser
{

    public static function parse(Command $command, array $arguments): ParsedCommand
    {
        $flags = [];
        $copy = $arguments;
        $blueprint = $command->getBlueprint();

        foreach ($arguments as $i => $argument) {
            if (strpos($argument, '-') !== 0) continue;

            // Avoid getting finding the tag twice; only first time counts
            if (isset($flags[$flag = substr($argument, 1)])) continue;

            // Use is_null because $length can be 0 so !0 would be true
            if (is_null($flag = $blueprint->getFlag($flag))) continue;

            $length = $flag->getLength();
            if ($length === -1) $length = count($copy);
            $flags[$flag->getName()] = implode(' ', array_slice($copy, $i + 1, $length));

            // Remove tag & tag parameters
            // array_diff_key() doesn't reorder the keys
            $arguments = array_diff_key($arguments, self::makeKeys(range($i, $i + $length)));
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

        foreach ($time_units as $symbol => $unit) {
            if (($length = array_search($symbol, $parts)) === false) continue;

            $n = implode('', array_slice($parts, 0, $length));
            $time .= "$n $unit ";
            array_splice($parts, 0, $length + 1);
        }

        $time = trim($time);

        return $time === '' ? time() : strtotime($time);
    }

    /**
     * Turns the values of an array into the keys of the return array. Populates values with an empty string
     * @param array $array
     * @return array
     */
    private static function makeKeys(array $array): array
    {
        $values_as_keys = [];

        foreach ($array as $value) {
            $values_as_keys[$value] = '';
        }

        return $values_as_keys;
    }

}