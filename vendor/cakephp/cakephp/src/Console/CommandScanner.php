<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Console;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;

/**
 * Used by CommanCollection and CommandTask to scan the filesystem
 * for command classes.
 *
 * @internal
 */
class CommandScanner
{
    /**
     * Scan CakePHP core, the applications and plugins for shell classes
     *
     * @return array
     */
    public function scanAll()
    {
        $shellList = [];

        $appNamespace = Configure::read('App.namespace');
        $shellList['app'] = $this->scanDir(
            App::path('Shell')[0],
            $appNamespace . '\Shell\\',
            '',
            ['app']
        );

        $shellList['CORE'] = $this->scanDir(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Shell' . DIRECTORY_SEPARATOR,
            'Cake\Shell\\',
            '',
            ['command_list']
        );

        $plugins = [];
        foreach (Plugin::loaded() as $plugin) {
            $plugins[$plugin] = $this->scanDir(
                Plugin::classPath($plugin) . 'Shell',
                str_replace('/', '\\', $plugin) . '\Shell\\',
                Inflector::underscore($plugin) . '.',
                []
            );
        }
        $shellList['plugins'] = $plugins;

        return $shellList;
    }

    /**
     * Scan a directory for .php files and return the class names that
     * should be within them.
     *
     * @param string $path The directory to read.
     * @param string $namespace The namespace the shells live in.
     * @param string $prefix The prefix to apply to commands for their full name.
     * @param array $hide A list of command names to hide as they are internal commands.
     * @return array The list of shell info arrays based on scanning the filesystem and inflection.
     */
    protected function scanDir($path, $namespace, $prefix, array $hide)
    {
        $dir = new Folder($path);
        $contents = $dir->read(true, true);
        if (empty($contents[1])) {
            return [];
        }

        $shells = [];
        foreach ($contents[1] as $file) {
            if (substr($file, -4) !== '.php') {
                continue;
            }

            $shell = substr($file, 0, -4);
            $name = Inflector::underscore(str_replace('Shell', '', $shell));
            if (in_array($name, $hide, true)) {
                continue;
            }

            $shells[] = [
                'file' => $path . $file,
                'fullName' => $prefix . $name,
                'name' => $name,
                'class' => $namespace . $shell
            ];
        }

        return $shells;
    }
}
