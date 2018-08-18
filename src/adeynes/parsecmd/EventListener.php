<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

final class EventListener implements Listener
{

    /** @var parsecmd */
    private $virion;

    public function __construct(parsecmd $virion)
    {
        $this->virion = $virion;
    }

    public function getVirion(): parsecmd
    {
        return $this->virion;
    }

    public function onPacketReceived(DataPacketReceiveEvent $ev): void {
        $packet = $ev->getPacket();

        if (!$packet instanceof ModalFormResponsePacket) return;

        // TODO: this function is way too long
        $player = $ev->getPlayer();
        $id = $packet->formId;
        $data = json_decode($packet->formData, true);

        // User closed out of the form
        // TODO: send usage?
        if (is_null($data)) return;

        if (!$form = $this->getVirion()->getForm($id)) return;

        $data = $form->process($data);
        // TODO: fail noisily, this should never happen
        $command_name = $form->getCommandName();
        if (!$command = $this->getVirion()->getCommand($command_name)) return;

        $blueprint = $command->getBlueprint();
        $usage = $blueprint->populateUsage($data);
        var_dump($usage);

        $this->getVirion()->getPlugin()->getServer()->dispatchCommand(
            $player,
            "$command_name $usage"
        );
    }

}