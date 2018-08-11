<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\plugin\Plugin;

final class parsecmd
{

    public static function register(Plugin $plugin, string $class, string $usage): void
    {
        $map = $plugin->getServer()->getCommandMap();
        if (!is_subclass_of($class, '\\adeynes\\parsecmd\\Command')) {
            throw new \InvalidArgumentException(
                "Class $class passed to parsecmd::register() is not a subclass of \\adeynes\\parsecmd\\Command!"
            );
        }
        $map->register($plugin->getName(), new $class($plugin, CommandParser::generateBlueprint($usage)));
    }

}