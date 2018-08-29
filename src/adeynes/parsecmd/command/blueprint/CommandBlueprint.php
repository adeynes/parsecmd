<?php
declare(strict_types=1);

namespace adeynes\parsecmd\command\blueprint;

use adeynes\parsecmd\form\Form;

class CommandBlueprint
{

    /** @var Argument[] */
    protected $arguments = [];

    /** @var Flag[] */
    protected $flags = [];

    /** @var string[] */
    protected $flag_aliases = [];

    /** @var null|string */
    protected $usage;

    /**
     * @param Argument[] $arguments
     * @param Flag[] $flags
     * @param null|string $usage
     */
    public function __construct(array $arguments, array $flags, ?string $usage)
    {
        foreach ($arguments as $name => $argument) {
            $this->arguments[$name] = $argument;
        }
        foreach ($flags as $name => $flag) {
            $this->flags[$name] = $flag;
        }
        $this->usage = $usage;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $name): ?Argument
    {
        return $this->getArguments()[$name] ?? null;
    }

    /**
     * @return Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getFlag(string $name): ?Flag
    {
        return $this->getFlags()[$name] ?? null;
    }

    /**
     * @return string[]
     */
    public function getFlagAliases(): array
    {
        return $this->flag_aliases;
    }

    public function getFlagAlias(string $alias): ?string
    {
        return $this->getFlagAliases()[$alias] ?? null;
    }

    /**
     * @param string[] $aliases
     */
    public function addFlagAliases(array $aliases): void
    {
        $this->flag_aliases = array_merge($this->flag_aliases, $aliases);
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function getMinimumArgumentCount(): int
    {
        $count = 0;
        foreach ($this->getArguments() as $argument) {
            if (!$argument->isOptional()) {
                $length = $argument->getLength();
                if ($length < 0) $length = 1;
                $count += $length;
            }
        }

        return $count;
    }

    public function populateForm(Form $form): Form
    {
        foreach ($this->getArguments() as $argument) {
            $name = $argument->getName();
            $form->addInput($argument->getDisplay(), '', '', $name);
        }

        foreach ($this->getFlags() as $flag) {
            $name = $flag->getName();

            if ($flag->getLength() === 0) {
                $form->addToggle($flag->getDisplay(), false, $name);
            } else {
                if ($flag->hasOptions()) {
                    $form->addDropdown($flag->getDisplay(), $flag->getOptions(), null, $name);
                } else {
                    $form->addInput($flag->getDisplay() . " (optional)", '', '', $name);
                }
            }
        }

        return $form;
    }

    public function populateUsage(Form $form, array $data): string
    {
        $usage = '';
        foreach ($this->getArguments() as $argument) {
            $name = $argument->getName();
            $usage .= "{$data[$name]} ";
        }

        foreach ($this->getFlags() as $flag) {
            $name = $flag->getName();

            $datum = $data[$name];
            if (is_bool($datum)) {
                $usage .= $datum ? "-$name " : '';
            } else {
                if ($flag->hasOptions()) {
                    $usage .= "-$name {$form->getDropdownValues()[$name][$datum]}";
                } else {
                    $usage .= "-$name {$data[$name]} ";
                }
            }
        }

        return trim($usage);
    }

}