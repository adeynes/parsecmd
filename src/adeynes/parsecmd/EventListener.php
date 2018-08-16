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
        if (!$command = $this->getVirion()->getCommand($form->getCommandName())) return;

        $blueprint = $command->getBlueprint();
        $usage = $command->getBlueprint()->getUsage();
        foreach ($blueprint->getArguments() as $argument) {
            $name = $argument->getName();
            $datum = $data[$name];
            // Replace any word containing the chunk name with the value
            $usage = preg_replace("/\s\??$name\S*/", " $datum", $usage);
        }

        foreach ($blueprint->getFlags() as $flag) {
            $name = $flag->getName();
            $datum = $data[$name];
            if ($flag->getLength() === 0) {
                if ($datum === true) continue;
                $usage = preg_replace("/\s-$name\S*/", '', $usage);
            } else {
                $usage = preg_replace("/\s-$name\S*/", " -$name $datum", $usage);
            }
        }

        $this->getVirion()->getPlugin()->getServer()->dispatchCommand($player, ltrim($usage, '/'));
        var_dump($usage);
    }

}