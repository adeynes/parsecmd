<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

use pocketmine\plugin\Plugin;

interface UsesParsecmdPlugin extends Plugin
{

    public function getParsecmd(): parsecmd;

}