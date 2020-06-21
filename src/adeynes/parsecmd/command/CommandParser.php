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
            if ($flag_name === null || isset($flags[$flag_name])) continue;

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

    /**
     * @param string $duration Must be of the form [ay][bM][cw][dd][eh][fm] with a, b, c, d, e, f integers
     * @return int UNIX timestamp corresponding to the duration (1y will return the timestamp one year from now)
     * @throws \InvalidArgumentException If the duration is invalid
     */
    public static function parseDuration(string $duration): int
    {
        $time_units = ['y' => 'year', 'M' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'm' => 'minute'];
        $regex = '/^([0-9]+y)?([0-9]+M)?([0-9]+w)?([0-9]+d)?([0-9]+h)?([0-9]+m)?$/';
        $matches = [];
        $is_matching = preg_match($regex, $duration, $matches);
        if (!$is_matching) {
            throw new \InvalidArgumentException("Invalid duration passed to CommandParser::parseDuration(). Must be of the form [ay][bM][cw][dd][eh][fm] with a, b, c, d, e, f integers");
        }

        $time = '';

        foreach ($matches as $index => $match) {
            if ($index === 0 || strlen($match) === 0) continue; // index 0 is the full match
            $n = substr($match, 0, -1);
            $unit = $time_units[substr($match, -1)];
            $time .= "$n $unit ";
        }

        $time = trim($time);

        return $time === '' ? time() : strtotime($time);
    }

}