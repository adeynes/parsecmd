<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class EventListener implements Listener
{

    public function onPacketReceived(DataPacketReceiveEvent $ev) : void {
        $packet = $ev->getPacket();

        if ($packet instanceof ModalFormResponsePacket) {
            $player = $ev->getPlayer();
            $form_id = $packet->formId;
            $data = json_decode($packet->formData, true);

            // if !isset forms[id] continue
        }
    }

}