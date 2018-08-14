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
    protected $labels = [];

    public function __construct(string $id)
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

    public function setTitle(string $title): void
    {
        $this->data['title'] = $title;
    }

    public function addContent(array $content): self
    {
        $this->data['content'][] = $content;
        return $this;
    }

    public function addLabel(?string $label): void
    {
        $this->labels[] = $label ?? count($this->labels);
    }

    public function addToggle(string $text, bool $default = false, string $label = null): self
    {
        $this->addLabel($label);
        return $this->addContent(['type' => 'toggle', 'text' => $text, 'default' => $default]);
    }

    public function addInput(string $text, string $placeholder = '', string $default = null, string $label = null): self
    {
        $this->addLabel($label);
        return $this->addContent(['type' => 'input', 'text' => $text, 'placeholder' => $placeholder, 'default' => $default]);
    }

    public function send(Player $player): void
    {
        $packet = new ModalFormRequestPacket;
        $packet->formId = $this->getId();
        $packet->formData = json_encode($this->getData());
        $player->dataPacket($packet);
    }

}