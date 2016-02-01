<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Touch\Persistence;

use Generated\Shared\Transfer\LocaleTransfer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Orm\Zed\Touch\Persistence\Map\SpyTouchSearchTableMap;
use Orm\Zed\Touch\Persistence\Map\SpyTouchStorageTableMap;
use Orm\Zed\Touch\Persistence\Map\SpyTouchTableMap;
use Orm\Zed\Touch\Persistence\SpyTouchQuery;
use Spryker\Zed\Propel\Business\Formatter\PropelArraySetFormatter;

class TouchQueryContainer extends AbstractQueryContainer implements TouchQueryContainerInterface
{

    const TOUCH_ENTRY_QUERY_KEY = 'search touch entry';
    const TOUCH_ENTRIES_QUERY_KEY = 'search touch entries';
    const TOUCH_EXPORTER_ID = 'exporter_touch_id';

    /**
     * @param string $itemType
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryTouchListByItemType($itemType)
    {
        $query = SpyTouchQuery::create();
        $query->filterByItemType($itemType);

        return $query;
    }

    /**
     * @param string $itemType
     * @param string $itemId
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryTouchEntry($itemType, $itemId)
    {
        $query = SpyTouchQuery::create();
        $query
            ->setQueryKey(self::TOUCH_ENTRY_QUERY_KEY)
            ->filterByItemType($itemType)
            ->filterByItemId($itemId);

        return $query;
    }

    /**
     * @param string $itemType
     * @param string $itemId
     * @param string $itemEvent
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryUpdateTouchEntry($itemType, $itemId, $itemEvent)
    {
        $query = SpyTouchQuery::create();
        $query
            ->filterByItemType($itemType)
            ->filterByItemId($itemId)
            ->filterByItemEvent($itemEvent);

        return $query;
    }

    /**
     * @param string $itemType
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     * @param \DateTime $lastTouchedAt
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function createBasicExportableQuery($itemType, LocaleTransfer $locale, \DateTime $lastTouchedAt)
    {
        $query = SpyTouchQuery::create();
        $query
            ->filterByItemType($itemType)
            ->filterByItemEvent(SpyTouchTableMap::COL_ITEM_EVENT_ACTIVE)
            ->filterByTouched(['min' => $lastTouchedAt])
            ->withColumn(SpyTouchTableMap::COL_ID_TOUCH, self::TOUCH_EXPORTER_ID);

        return $query;
    }

    /**
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryExportTypes()
    {
        $query = SpyTouchQuery::create();
        $query
            ->addSelectColumn(SpyTouchTableMap::COL_ITEM_TYPE)
            ->setDistinct()
            ->setFormatter(new PropelArraySetFormatter());

        return $query;
    }

    /**
     * @param string $itemType
     * @param string $itemEvent
     * @param array $itemIds
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryTouchEntries($itemType, $itemEvent, array $itemIds)
    {
        $query = SpyTouchQuery::create()
            ->setQueryKey(self::TOUCH_ENTRIES_QUERY_KEY)
            ->filterByItemType($itemType)
            ->filterByItemEvent($itemEvent)
            ->filterByItemId($itemIds);

        return $query;
    }

    /**
     * @param string $itemType
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryTouchDeleteStorageAndSearch($itemType)
    {
        $query = SpyTouchQuery::create();
        $query->filterByItemEvent(SpyTouchTableMap::COL_ITEM_EVENT_DELETED)
            ->filterByItemType($itemType)
            ->leftJoinTouchSearch()
            ->leftJoinTouchStorage()
            ->addAnd(SpyTouchSearchTableMap::COL_FK_TOUCH, null, Criteria::ISNULL)
            ->addAnd(SpyTouchStorageTableMap::COL_FK_TOUCH, null, Criteria::ISNULL);

        return $query;
    }

    /**
     * @param string $itemType
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Touch\Persistence\SpyTouchQuery
     */
    public function queryTouchDeleteOnlyByItemType($itemType)
    {
        $query = SpyTouchQuery::create();
        $query->filterByItemEvent(SpyTouchTableMap::COL_ITEM_EVENT_DELETED)
            ->filterByItemType($itemType);

        return $query;
    }

}