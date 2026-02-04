<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SwapVar extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'test:swap';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $varA = $params[0] ?? null;
        $varB = $params[1] ?? null;

        if (!$varA || !$varB) {
            CLI::error('Usage: php spark test:swap <string> <string>');
            return;
        }

        CLI::print('START: ============== ');
        CLI::print('VAR A: ' . $varA);
        CLI::print('VAR B: ' . $varB);

        $c = $varA;
        $varA = $varB;
        $varB = $c;

        CLI::print('END: ============== ');
        CLI::print('VAR A: ' . $varA);
        CLI::print('VAR B: ' . $varB);
    }
}
