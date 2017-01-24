<?php
/**
 * PHPUnit Bridge Plugin for PHPCI.
 *
 * @copyright Copyright 2017, Recranet
 * @license https://github.com/recranet/phpci-phpunit-bridge/blob/master/LICENSE.md
 * @link https://github.com/recranet/phpci-phpunit-bridge
 */

namespace Recranet\Plugin;

use PHPCI\Plugin\Util\TapParser;
use PHPCI\Plugin\PhpUnit as PhpUnitPlugin;

/**
* PHPUnit Bridge Plugin - Allows PHPUnit testing with the Symfony PHPUnit Bridge.
*
* @author Raymon de Looff <raydelooff@gmail.com>
*/
class PhpUnitBridge extends PhpUnitPlugin
{
    /**
    * Runs PHPUnit tests in a specified directory, optionally using specified config file(s).
    */
    public function execute()
    {
        if (empty($this->xmlConfigFile) && empty($this->directory)) {
            $this->phpci->logFailure('Neither configuration file nor test directory found.');

            return false;
        }

        $success = true;

        // Run any config files first. This can be either a single value or an array.
        if ($this->xmlConfigFile !== null) {
            $success &= $this->runConfigFile($this->xmlConfigFile);
        }

        // Run any dirs next. Again this can be either a single value or an array.
        if ($this->directory !== null) {
            $success &= $this->runDir($this->directory);
        }

        $tapString = $this->phpci->getLastOutput();
        $tapString = mb_convert_encoding($tapString, "UTF-8", "ISO-8859-1");

        try {
            $tapParser = new TapParser($tapString);
            $output = $tapParser->parse();

            $failures = $tapParser->getTotalFailures();

            $this->build->storeMeta('phpunit-errors', $failures);
            $this->build->storeMeta('phpunit-data', $output);
        } catch (\Exception $e) {

        }

        return $success;
    }

    /**
     * Run the tests defined in a PHPUnit config file.
     *
     * @param $configPath
     * @return bool|mixed
     */
    protected function runConfigFile($configPath)
    {
        if (is_array($configPath)) {
            return $this->recurseArg($configPath, array($this, 'runConfigFile'));
        } else {
            if ($this->runFrom) {
                $curdir = getcwd();
                chdir($this->phpci->buildPath . DIRECTORY_SEPARATOR . $this->runFrom);
            }

            $phpunit = $this->phpci->findBinary('simple-phpunit');

            $cmd = $phpunit . ' --tap %s -c "%s" ' . $this->coverage . $this->path;
            $success = $this->phpci->executeCommand($cmd, $this->args, $this->phpci->buildPath . $configPath);

            if ($this->runFrom) {
                chdir($curdir);
            }

            return $success;
        }
    }

    /**
     * Run the PHPUnit tests in a specific directory or array of directories.
     *
     * @param $directory
     * @return bool|mixed
     */
    protected function runDir($directory)
    {
        if (is_array($directory)) {
            return $this->recurseArg($directory, array($this, 'runDir'));
        } else {
            $curdir = getcwd();
            chdir($this->phpci->buildPath);

            $phpunit = $this->phpci->findBinary('simple-phpunit');

            $cmd = $phpunit . ' --tap %s "%s"';
            $success = $this->phpci->executeCommand($cmd, $this->args, $this->phpci->buildPath . $directory);
            chdir($curdir);
            return $success;
        }
    }
}
