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

    /** @var null|callable */
    protected $callable = null;

    /**
     * @var array Nice human-readable names to reference fields instead of good ol' numbers
     */
    protected $aliases = [];

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

    public function processData(array &$data): array
    {
        foreach ($data as $i => $datum) {
            $data[$this->aliases[$i]] = $datum;
        }
        return $data;
    }

    public function setTitle(string $title): self
    {
        $this->data['title'] = $title;
        return $this;
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

        return $this->addContent($content, $alias);
    }

    public function send(Player $player): void
    {
        $packet = new ModalFormRequestPacket;
        $packet->formId = $this->getId();
        $packet->formData = json_encode($this->getData());
        $player->dataPacket($packet);
    }

}