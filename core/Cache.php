<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Cache\Backend;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

class Cache
{
    /**
     * @var Pool
     */
    private $pool;
    private $id;
    private $item;

    public function __construct($id)
    {
        $this->pool = new Pool();
        $this->pool->setNamespace('piwikcache');
        $this->id   = $this->getIdIfValid($id);
        $this->item = $this->pool->getItem();
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->pool->setDriver($driver);
    }

    /**
     * Sets the namespace to prefix all cache ids with.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->pool->setNamespace('piwikcache' . $namespace);
        $this->item->setKey($this->id, 'piwikcache' . $namespace);
    }

    protected function getIdIfValid($id)
    {
        if (!Filesystem::isValidFilename($id)) {
            throw new \Exception("Invalid cache ID request $id");
        }

        return $id;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function get()
    {
        return $this->item->get();
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     *
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function has()
    {
        return !$this->item->isMiss();
    }

    /**
     * Puts data into the cache.
     *
     * @param mixed  $data     The cache entry/data.
     * @param int    $lifeTime The cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function set($data, $lifeTime = 0)
    {
        return $this->item->set($data, $lifeTime);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete()
    {
        return $this->item->clear();
    }

    /**
     * Flushes all cache entries.
     *
     * @return boolean TRUE if the cache entries were successfully flushed, FALSE otherwise.
     */
    public function flushAll()
    {
        return $this->pool->flush();
    }
}
