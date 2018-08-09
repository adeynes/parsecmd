<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class CommandParser
{

    public static function generateBlueprint(string $usage): CommandBlueprint
    {
        /** @var Argument[] $args */
        $args = [];
        /** @var Flag[] $flags */
        $flags = [];

        foreach (explode(' ', $usage) as $usage_chunk) {
            $lengths = [];
            preg_match_all('/\((.*?)\)/', $usage_chunk, $lengths);

            // defaults is no matches (ie no length specified)
            $length = -1; // -1 is infinite length
            $name = $usage_chunk;

            // [[]] is no matches
            if ($lengths !== [[]]) {
                // given '(beans) are (cool)', [0] will be [(beans), (cool)], [1] will be [beans, cool]

                $length_tag = end($lengths[0]);

                // length tag wasn't () (which means infinite length is infinite (-1))
                if (end($lengths[1]) !== '') {
                    $length = end($lengths[1]);
                }

                $parenthesis_index = strrpos($usage_chunk, $length_tag);
                // remove the parenthesis to get just the name
                $name = substr($usage_chunk, 0, $parenthesis_index);
            }

            if (strpos($usage_chunk, '-') === 0) { // flag
                $flags[] = new Flag($name, $length);
            } else { // argument
                $args[] = new Argument($name, $length);
            }
        }
    }

    public static function parse(PCMDCommand $command, array $args): ParsedCommand
    {
        $tags = [];
        $copy = $args;

        foreach ($args as $i => $arg) {
            if (strpos($arg, '-') !== 0) continue;

            // Avoid getting finding the tag twice; only first time counts
            if (isset($tags[$tag = substr($arg, 1)])) continue;

            // Use is_null because $length can be 0 so !0 would be true
            if (is_null($length = $command->getTag($tag))) continue;

            $tags[$tag] = implode(' ', array_slice($copy, $i + 1, $length));

            // Remove tag & tag parameters
            // array_diff_key() doesn't reorder the keys
            $args = array_diff_key($args, self::makeKeys(range($i, $i + $length)));
        }

        return new ParsedCommand($command->getName(), $args, $tags);
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