<?php

namespace Lovat\Api\Model;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Client
 * @package Lovat\Api\Model
 */
class Cache extends TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'lovat_api_cache';

    /**
     * The tag name that limits the cache cleaning scope within a particular tag
     */
    const CACHE_TAG = 'LOVAT_SETTINGS_CACHE';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param FrontendPool $cacheFrontendPool
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FrontendPool $cacheFrontendPool,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }

    /**
     * Save cache lovat
     *
     * @param $cacheData
     */
    public function saveCache($cacheData)
    {
        $this->cache->save(
            $this->serializer->serialize($cacheData),
            self::TYPE_IDENTIFIER,
            [self::CACHE_TAG],
            86400
        );
    }

    /**
     * Return cache data
     *
     * @return array|bool|float|int|string|null
     */
    public function getCache()
    {
        $cacheData = $this->cache->load(self::TYPE_IDENTIFIER);
        if (!empty($cacheData)) {
            $cacheData = $this->serializer->unserialize($cacheData);
        }

        return $cacheData;
    }

    /**
     * Clear lovat cache
     */
    public function clearCache()
    {
        $this->cache->clean([self::CACHE_TAG]);
    }
}
