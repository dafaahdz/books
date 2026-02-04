<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestScramble extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Testing';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'test:scramble';

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
        $inputString = $params[0] ?? null;

        if (!$inputString) {
            CLI::error('Usage: php spark test:scramble <string>');
            return;
        }

        $chars = "`~1234567890-=+_)(*&^%$#@![]{};':\\|/?.>,<zxcvasdfqwerbnmgjkhlpoiuytQWERTYUIOPLKJHGFDSAZXCVBNM ";
        $charsLength = strlen($chars);
        $correctCount = 0;
        $threshold = 25;

        $resultParts = [];
        $targetLength = strlen($inputString);

        for ($i = 0; $i < $targetLength; $i++) {
            $correctCount = 0;
            while (true) {
                $randomChar = $chars[random_int(0, $charsLength - 1)];

                // gabung: yang sudah bener + yang lagi dirandom
                $current = implode('', $resultParts) . $randomChar;

                // print di baris yang sama
                CLI::write("\r" . $current, null, false);

                if ($randomChar === $inputString[$i] && $correctCount < $threshold) {
                    $correctCount++;
                }

                if ($randomChar === $inputString[$i] && $correctCount >= $threshold) {
                    $resultParts[] = $randomChar;
                    $correctCount = 0;
                    break;
                }

                // usleep(2); 
            }
        }


        CLI::write(implode('', $resultParts), 'green');
    }

}
