<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\plugin\Plugin;

final class parsecmd
{

    /** @var null|parsecmd */
    private static $instance = null;

    /** @var Plugin */
    private $plugin;

    /** @var Form[] */
    private $forms;

    /** @var int */
    private $next_form_id;

    private function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->next_form_id = rand(0xAAAAAA, 0xFFFFFF);
        $plugin->getServer()->getPluginManager()->registerEvents(new EventListener(), $plugin);
    }

    public static function new(Plugin $plugin): ?self
    {
        if (self::getInstance()) {
            $plugin->getServer()->getLogger()->critical("{$plugin->getName()} has already instantiated parsecmd!");
            return null;
        }
        return new self($plugin);
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function register(string $class, string $usage): void
    {
        $plugin = $this->getPlugin();
        $map = $plugin->getServer()->getCommandMap();
        if (!is_subclass_of($class, '\\adeynes\\parsecmd\\Command')) {
            throw new \InvalidArgumentException(
                "Class $class passed to parsecmd::register() is not a subclass of \\adeynes\\parsecmd\\Command!"
            );
        }
        $map->register($plugin->getName(), new $class($plugin, CommandParser::generateBlueprint($usage)));
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