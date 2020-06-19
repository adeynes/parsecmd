<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use adeynes\parsecmd\command\blueprint\BlueprintFactory;
use adeynes\parsecmd\command\blueprint\CommandBlueprint;
use adeynes\parsecmd\command\Command;
use pocketmine\plugin\Plugin;

final class parsecmd
{

    /** @var null|parsecmd */
    private static $instance = null;

    /** @var Plugin */
    private $plugin;

    /** @var Command[] */
    private $commands;

    private function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    // TODO: per-command override
    public static function new(Plugin $plugin, array $commands = [], bool $override = false): ?self
    {
        if (self::getInstance()) {
            $plugin->getServer()->getLogger()->critical("{$plugin->getName()} has already instantiated parsecmd!");
            return null;
        }

        $parsecmd = new self($plugin);
        $parsecmd->registerAll($commands, $override);
        self::$instance = $parsecmd;
        return $parsecmd;
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function getCommand(string $command_name): ?Command
    {
        return $this->commands[$command_name] ?? null;
    }

    private function registerAll(array $commands, bool $override = false): void
    {
        foreach ($commands['commands'] as $command) {
            $blueprint = $command['blueprint'];
            $blueprint = is_array($blueprint) ? $blueprint : $commands['blueprints'][$blueprint];
            $this->register(
                $command['class'],
                BlueprintFactory::generate($blueprint, $command['usage']),
                $command['aliases'] ?? [],
                $override
            );
        }
    }

    public function register(string $class, CommandBlueprint $blueprint, array $aliases, bool $override = false): void
    {
        $plugin = $this->getPlugin();
        $map = $plugin->getServer()->getCommandMap();

        if (!is_subclass_of($class, Command::class)) {
            throw new \InvalidArgumentException(
                "Class $class passed to parsecmd::register() is not a subclass of \\adeynes\\parsecmd\\Command!"
            );
        }

        /** @var Command $command */
        $command = new $class($plugin, $blueprint);

        if ($override && $old = $map->getCommand($command->getName())) {
            $old->setLabel($command . '_disabled');
            $map->unregister($old);
        }

        $command->setAliases($aliases);
        $map->register($plugin->getName(), $command);
        $this->commands[$command->getName()] = $command;
    }

}