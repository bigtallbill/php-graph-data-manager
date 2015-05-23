<?php

namespace Bigtallbill\MongoGraphModel;


use Doctrine\ODM\MongoDB\DocumentManager;

class GraphManager
{
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const YEAR = 31536000;

    const METHOD_SET = 'set';
    const METHOD_INC = 'inc';

    /** @var DocumentManager */
    private $documentManager;

    private $window = self::DAY;
    private $granularity = self::MINUTE;

    private $incremental = false;

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
     * @return ParsedStat[]
     */
    public function parseAggregate($group, $aggregateResults)
    {
        $parsed = array();
        foreach ($aggregateResults as $result) {

            $key = $result['_id'];
            if (is_array($result['_id'])) {
                $key = json_encode($result['_id']);
            }

            $parsed[] = new ParsedStat(($this->incremental) ? self::METHOD_INC : self::METHOD_SET, $this->window, $this->granularity, $group, $key, $result['count']);
        }
        return $parsed;
    }

    /**
     * @param $group
     * @param $key
     * @param $value
     * @return ParsedStat[]
     */
    public function parseSimpleKeyValue($group, $key, $value)
    {
        $parsed = array(
            new ParsedStat(($this->incremental) ? self::METHOD_INC : self::METHOD_SET, $this->window, $this->granularity, $group, $key, $value)
        );

        return $parsed;
    }

    /**
     * @param ParsedStat[] $stats
     */
    public function insertMultiple($stats)
    {
        foreach ($stats as $stat) {
            $this->insertStat($stat);
        }
    }

    /**
     * @param ParsedStat $parsedStat A single parsed stat
     * @return bool
     * @throws \Exception
     */
    public function insertStat(ParsedStat $parsedStat)
    {
        $this->assertGranularityIsMoreThanWindow();
        $this->assertWindowIsDivisibleByGranularity();

        $currentSegment = $this->getTimeSegment($parsedStat->getGranularity());
        $timeWindow = $this->getTimeSegment($parsedStat->getWindow());

        $humanGranularity = $this->getHumanTimeIncrement($parsedStat->getGranularity());
        $humanWindow = $this->getHumanTimeIncrement($parsedStat->getWindow());

        $queryBuilder = $this->documentManager->createQueryBuilder('Bigtallbill\MongoGraphModel\Graph');
        $queryBuilder->update()
            ->upsert(true)
            ->field('method')->equals($parsedStat->getMethod())
            ->field('granularity')->equals($parsedStat->getGranularity())
            ->field('granularity_human')->set($humanGranularity)
            ->field('window')->equals($parsedStat->getWindow())
            ->field('window_human')->equals($humanWindow)
            ->field('group')->equals($parsedStat->getGroup())
            ->field('key')->equals($parsedStat->getKey())
            ->field('date')->equals(new \MongoDate($timeWindow));

        if ($parsedStat->getMethod() === self::METHOD_INC) {
            $queryBuilder->field("segments.$currentSegment")->inc($parsedStat->getValue());
        } else {
            $queryBuilder->field("segments.$currentSegment")->set($parsedStat->getValue());
        }

        $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $window
     * @return $this
     */
    public function setWindow($window)
    {
        $this->window = $window;
        return $this;
    }

    /**
     * @param string $granularity
     * @return $this
     */
    public function setGranularity($granularity)
    {
        $this->granularity = $granularity;
        return $this;
    }

    /**
     * Gets the human representation of the given number of seconds
     *
     * e.g 60 = Minute, 3600 = Hour ect (Up to Year)
     *
     * @param int $inputSeconds
     * @return null|string
     */
    protected function getHumanTimeIncrement($inputSeconds)
    {
        $humanGranularity = null;

        $years = $inputSeconds / self::YEAR;
        $days = $inputSeconds / self::DAY;
        $hours = floor($inputSeconds / self::HOUR);
        $minutes = floor(($inputSeconds / 60) % 60);
        $seconds = $inputSeconds % 60;

        if ($years >= 1) {
            $humanGranularity = 'Year';
            return $humanGranularity;
        } elseif ($days >= 1) {
            $humanGranularity = 'Day';
            return $humanGranularity;
        } elseif ($hours >= 1) {
            $humanGranularity = 'Hour';
            return $humanGranularity;
        } elseif ($minutes >= 1) {
            $humanGranularity = 'Minute';
            return $humanGranularity;
        } elseif ($seconds >= 1) {
            $humanGranularity = 'Second';
            return $humanGranularity;
        }
        return $humanGranularity;
    }

    protected function assertWindowIsDivisibleByGranularity()
    {
        $remainder = $this->window % $this->granularity;
        if ($remainder !== 0) {
            throw new \Exception('the given window is not evenly divisible by the given granularity');
        }
    }

    protected function assertGranularityIsMoreThanWindow()
    {
        if ($this->granularity >= $this->window) {
            throw new \Exception('Window MUST be more than the granularity');
        }
    }

    /**
     * Given a window of time in seconds, gets the current timestamp to the current segment
     *
     * @param int $windowSeconds
     * @return float
     */
    protected function getTimeSegment($windowSeconds)
    {
        return floor(time() / $windowSeconds) * $windowSeconds;
    }

    /**
     * @param boolean $incremental
     */
    public function setIncremental($incremental)
    {
        $this->incremental = $incremental;
    }
}
