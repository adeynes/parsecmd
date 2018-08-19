<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class Form
{

    /** @var int */
    protected $id;

    /** @var array[][] */
    protected $data;

    /** @var string */
    protected $command_name;

    /**
     * @var array Nice human-readable names to reference fields instead of good ol' numbers
     */
    protected $aliases = [];
    
    protected $dropdown_values = [];

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->data = [
            'type' => 'custom_form',
            'title' => '',
            'content' => []
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array[][]
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getCommandName(): ?string
    {
        return $this->command_name;
    }

    /**
     * @return string[][]
     */
    public function getDropdownValues(): array
    {
        return $this->dropdown_values;
    }

    public function setTitle(string $title): self
    {
        $this->data['title'] = $title;
        return $this;
    }

    public function setCommandName(string $command_name): self
    {
        $this->command_name = $command_name;
        return $this;
    }

    public function process(array $data): array
    {
        $new = [];
        foreach ($data as $i => $datum) {
            $new[$this->aliases[$i]] = $datum;
        }
        return $new;
    }

    protected function addContent(array $content, ?string $alias): self
    {
        $this->data['content'][] = $content;
        $this->addAlias($alias);
        return $this;
    }

    public function addAlias(?string $alias): void
    {
        $this->aliases[] = $alias ?? count($this->aliases);
    }

    public function addLabel(string $text, string $alias = null): self
    {
        return $this->addContent(
            ['type' => 'label', 'text' => $text],
            $alias
        );
    }

    public function addInput(string $text, string $placeholder = '', string $default = '', string $alias = null): self
    {
        return $this->addContent(
            ['type' => 'input', 'text' => $text, 'placeholder' => $placeholder, 'default' => $default],
            $alias
        );
    }

    public function addToggle(string $text, bool $default = false, string $alias = null): self
    {
        return $this->addContent(
            ['type' => 'toggle', 'text' => $text, 'default' => $default],
            $alias
        );
    }

    public function addSlider(string $text, int $min, int $max, int $step = null, int $default = null, string $alias = null): self
    {
        $content = ['type' => 'slider', 'text' => $text, 'min' => $min, 'max' => $max];
        if (!is_null($step)) {
            $content['step'] = $step;
        }
        if (!is_null($default)) {
            $content['default'] = $default;
        }
        return $this->addContent($content, $alias);
    }
    
    public function addStepSlider(string $text, array $steps, int $default = null, string $alias = null): self
    {
        $content = ['type' => 'step_slider', 'text' => $text, 'steps' => $steps];
        if (!is_null($default)) {
            $content['default'] = $default;
        }

        return $this->addContent($content, $alias);
    }

    public function addDropdown(string $text, array $options, int $default = null, string $alias = null): self
    {
        $content = ['type' => 'dropdown', 'text' => $text, 'options' => $options];
        if (!is_null($default)) {
            $content['default'] = $default;
        }

        foreach ($options as $i => $option) {
            $this->dropdown_values[$alias][$i] = $option;
        }
        
        return $this->addContent($content, $alias);
    }

    public function send(Player $player): void
    {
        $packet = new ModalFormRequestPacket();
        $packet->formId = $this->getId();
        $packet->formData = json_encode($this->getData());
        $player->dataPacket($packet);
    }

}