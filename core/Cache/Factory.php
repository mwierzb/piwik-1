<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\Cache;
use Piwik\Config;
use Stash\DriverList;

class Factory
{
    /**
     * @var Backend[]
     */
    private static $backends = array();

    public static function buildDefaultCache($id, $options = array())
    {
        $config  = Config::getInstance()->cache;
        $options = array_merge($config, $options);

        return self::buildCache($id, $config['backend'], $options);
    }

    public static function flushAll()
    {
        foreach (self::$backends as $backend) {
            $backend->doFlush();
        }
    }

    /**
     * @param  $backend
     * @param  array $options
     * @return Cache
     */
    public static function buildCache($id, $backend, $options = array())
    {
        $backend = self::buildCachedBackend($backend, $options);
        $cache   = new Cache($id);
        $cache->setDriver($backend);

        return $cache;
    }

    /**
     * @param $backend
     * @param array $options
     * @return Backend
     */
    private static function buildCachedBackend($backend, $options)
    {
        $key = $backend . md5(implode('', $options));

        if (empty(self::$backends[$key])) {
            self::$backends[$key] = self::buildBackend($backend, $options);
        }

        return self::$backends[$key];
    }

    /**
     * @param $backend
     * @param array $options
     * @return Backend
     */
    private static function buildBackend($backend, $options = array())
    {
        if ('composite' === $backend) {
            return self::getChainedCache($options);
        }

        $backend = DriverList::getDriverClass($backend);
        $backend = new $backend;
        $backend->setOptions($options);

        return $backend;
    }

    private static function getChainedCache($options)
    {
        $drivers = array();
        foreach ($options['backends'] as $backendToBuild) {
            $drivers[] = self::buildCachedBackend($backendToBuild, $options);
        }

        $driver = DriverList::getDriverClass('composite');
        $driver = new $driver($drivers);

        return $driver;
    }

}