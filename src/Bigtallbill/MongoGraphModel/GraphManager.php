<?php

namespace Bigtallbill\MongoGraphModel;


use Doctrine\ODM\MongoDB\DocumentManager;

class GraphManager
{
    const GRAN_DAYS = 'days';
    const GRAN_DAYS_INCLUSIVE = 'daysInclusive';
    const GRAN_MINUTES = 'minutes';
    const GRAN_MINUTES_INCLUSIVE = 'minutesInclusive';

    const WINDOW_MINUTE = 60;
    const WINDOW_HOUR = 3600;
    const WINDOW_DAY = 86400;
    const WINDOW_YEAR = 1314000;

    /** @var DocumentManager */
    private $documentManager;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @param $group
     * @param $aggregateResults
     * @return array
     */
    public function parseAggregate($group, $aggregateResults)
    {
        $parsed = array();
        foreach ($aggregateResults as $result) {

            $key = $result['_id'];
            if (is_array($result['_id'])) {
                $key = json_encode($result['_id']);
            }

            $parsed[] = array(
                'group' => $group,
                'key' => $key,
                'value' => $result['count'],
            );
        }
        return $parsed;
    }

    /**
     * @param $group
     * @param $key
     * @param $value
     * @return array
     */
    public function parseSimpleKeyValue($group, $key, $value)
    {
        $parsed = array(
            array(
                'group' => $group,
                'key' => $key,
                'value' => $value,
            )
        );

        return $parsed;
    }

    /**
     * @param array $parsedStat A single parsed stat array
     * @param int $window The time windows within which to treat multiple inserts as the same
     * @param string $granularity The granularity which to store statistics in the document
     * @return bool
     * @throws \Exception
     */
    public function insertWindowStat($parsedStat, $window = 86400, $granularity = self::GRAN_DAYS_INCLUSIVE)
    {
        $statKey = 'stats';

        $granularityLengths = array(
            self::GRAN_DAYS_INCLUSIVE => 86400,
            self::GRAN_DAYS => 86400,
            self::GRAN_MINUTES => 60,
            self::GRAN_MINUTES_INCLUSIVE => 60
        );

        if ($granularityLengths[$granularity] >= $window) {
            throw new \Exception('Window MUST be more than the granularity');
        }

        $yearEpoch = strtotime('jan 1 ' . date('Y'));
        $year = (string)date('Y');
        $month = (string)date('n');
        $day = (string)date('j');
        $dayOfYear = (string)date('z');
        $hour = (string)date('G');
        $minute = (string)date('i');

        switch ($granularity) {
            case self::GRAN_DAYS_INCLUSIVE:
                $statKey .= ".$year.$month.$day";
                break;
            case self::GRAN_DAYS:
                $statKey .= ".$dayOfYear";
                break;
            case self::GRAN_MINUTES:
                $statKey .= ".$minute";
                break;
            case self::GRAN_MINUTES_INCLUSIVE:
                $statKey .= ".$year.$month.$day.$hour.$minute";
                break;
        }

        $timeWindow = $this->getTimeWindow($window);

        $this->documentManager->createQueryBuilder('Bigtallbill\MongoGraphModel\Graph')
            ->update()
            ->upsert(true)
            ->field('granularity')->equals($granularity)
            ->field('window')->equals($window)
            ->field('group')->equals($parsedStat['group'])
            ->field('key')->equals($parsedStat['key'])
            ->field('date')->equals(new \MongoDate($timeWindow))
            ->field($statKey)->set($parsedStat['value'])
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    private function getRepository()
    {
        return $this->documentManager->getRepository('Bigtallbill\MongoGraphModel\Graph');
    }

    /**
     * @param $window
     * @return float
     */
    protected function getTimeWindow($window)
    {
        return floor(time() / $window) * $window;
    }
}
