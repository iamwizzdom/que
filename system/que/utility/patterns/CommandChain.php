<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/30/2019
 * Time: 9:42 PM
 */

namespace que\utility\pattern;


class CommandChain
{
    /**
     * @var Command[]
     */
    private array $commands = [];

    /**
     * @param $cmd
     */
    public function addCommand(Command $cmd)
    {
        $this->commands[] = $cmd;
    }

    /**
     * @param $name
     * @param $args
     */
    public function runCommand($name, $args)
    {
        foreach ($this->commands as $cmd) {
            if ($cmd->onCommand($name, $args))
                return;
        }
    }
}