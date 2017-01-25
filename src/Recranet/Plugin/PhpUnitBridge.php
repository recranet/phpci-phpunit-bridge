<?php
/**
 * PHPUnit Bridge Plugin for PHPCI.
 *
 * @copyright Copyright 2017, Recranet
 * @license https://github.com/recranet/phpci-phpunit-bridge/blob/master/LICENSE.md
 * @link https://github.com/recranet/phpci-phpunit-bridge
 */

namespace Recranet\Plugin;

use PHPCI\Plugin;
use PHPCI\Builder;
use PHPCI\Model\Build;
use PHPCI\ZeroConfigPlugin;

/**
* PHPUnit Bridge Plugin - Allows PHPUnit testing with the Symfony PHPUnit Bridge.
*
* @author Raymon de Looff <raydelooff@gmail.com>
*/
class PhpUnitBridge implements Plugin, ZeroConfigPlugin
{
    /**
     * @var \PHPCI\Builder
     */
    protected $phpci;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $build;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var string
     */
    protected $options;

    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;

        $this->parseOptions($options);
    }

    /**
     * Parse the options.
     *
     * @param $options
     */
    public function parseOptions($options)
    {
        if (array_key_exists('working_directory', $options)) {
            $this->workingDirectory = $options['working_directory'];
        }

        if (array_key_exists('options', $options)) {
            if (!is_array($options['options'])) {
                $options['options'] = array($options['options']);
            }

            $this->options = implode(' ', $options['options']);
        }

        if (array_key_exists('path', $options)) {
            $this->path = $options['path'];
        }
    }

    /**
     * Check if the plugin can be executed without any configurations
     *
     * @param $stage
     * @param Builder $builder
     * @param Build $build
     *
     * @return bool
     */
    public static function canExecute($stage, Builder $builder, Build $build)
    {
        return $stage == 'test';
    }

    /**
    * Runs PHPUnit tests in a specified directory, optionally using specified config file(s).
    */
    public function execute()
    {
        if ($this->workingDirectory) {
            $currentDirectory = getcwd();
            chdir($this->phpci->buildPath . DIRECTORY_SEPARATOR . $this->workingDirectory);
        }

        $phpunit = $this->phpci->findBinary('simple-phpunit');

        $cmd = $phpunit . ' %s %s';
        $success = $this->phpci->executeCommand($cmd, $this->options, $this->path);

        if ($this->workingDirectory) {
            chdir($currentDirectory);
        }

        return $success;
    }
}
