<?php
declare(strict_types=1);

namespace adeynes\parsecmd;

class CommandForm extends Form
{

    public function setCommandName(string $command_name): self
    {
        $this->data['command_name'] = $command_name;
        return $this;
    }

}