<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

final class parsecmd
{

    /** @var null|parsecmd */
    private static $instance = null;

    /** @var UsesParsecmdPlugin */
    private $plugin;

    /** @var Command[] */
    private $commands;

    /** @var Form[] */
    private $forms;

    /** @var int */
    private $next_form_id;

    private function __construct(UsesParsecmdPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->next_form_id = rand(0xAAAAAA, 0xFFFFFF);
        $plugin->getServer()->getPluginManager()->registerEvents(new EventListener($this), $plugin);
    }

    public static function new(UsesParsecmdPlugin $plugin, array $commands = []): ?self
    {
        if (self::getInstance()) {
            $plugin->getServer()->getLogger()->critical("{$plugin->getName()} has already instantiated parsecmd!");
            return null;
        }

        $parsecmd = new self($plugin);
        $parsecmd->registerAll($commands);
        return new self($plugin);
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function getPlugin(): UsesParsecmdPlugin
    {
        return $this->plugin;
    }

    public function getCommand(string $command_name): ?Command
    {
        return $this->commands[$command_name] ?? null;
    }

    public function getForm(int $id): ?Form
    {
        return $this->forms[$id] ?? null;
    }

    private function registerAll(array $commands): void
    {
        foreach ($commands['commands'] as $command) {
            $blueprint = $command['blueprint'];
            $blueprint = is_array($blueprint) ? $blueprint : $commands['blueprints'][$blueprint];
            $this->register(
                $command['class'],
                BlueprintFactory::generate($blueprint, $command['usage']),
                $command['aliases'] ?? []
            );
        }
    }

    public function register(string $class, CommandBlueprint $blueprint, array $aliases): void
    {
        $plugin = $this->getPlugin();
        $map = $plugin->getServer()->getCommandMap();
        if (!is_subclass_of($class, '\\adeynes\\parsecmd\\Command')) {
            throw new \InvalidArgumentException(
                "Class $class passed to parsecmd::register() is not a subclass of \\adeynes\\parsecmd\\Command!"
            );
        }
        /** @var Command $command */
        $command = new $class($plugin, $blueprint);
        $command->setAliases($aliases);
        $map->register($plugin->getName(), $command);
        $this->commands[$command->getName()] = $command;
    }

    public function newForm(): Form
    {
        $form = new Form($id = $this->bumpNextFormId());
        $this->forms[$id] = $form;
        return $form;
    }

    private function bumpNextFormId(): int
    {
        return $this->next_form_id++;
    }

}