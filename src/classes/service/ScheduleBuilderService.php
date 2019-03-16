<?php


namespace ptv\service;


use ptv\Database;
use ptv\model\Channel;
use ptv\model\ChannelRestriction;
use ptv\model\Program;
use ptv\model\Title;
use ptv\Utils;

class ScheduleBuilderService {

    /**
     * @var Database
     */
    private $db;
    /**
     * @var ProgramService
     */
    private $programService;

    /**
     * @var FillerService
     */
    private $fillerService;
    /**
     * @var ChannelService
     */
    private $channelService;

    /**
     * @var TitleService
     */
    private $titleService;

    /**
     * @var TitleChannelPlayCountService
     */
    private $titleChannelPlayCountService;

    /**
     * @var ChannelRestrictionService
     */
    private $channelRestrictionService;
    /**
     * @var ShowChannelPlayCountService
     */
    private $showChannelPlayCountService;

    /**
     * GenreService constructor.
     * @param Database $db
     * @param ProgramService $programService
     * @param FillerService $fillerService
     * @param ChannelService $channelService
     * @param TitleService $titleService
     * @param ChannelRestrictionService $channelRestrictionService
     * @param ShowChannelPlayCountService $showChannelPlayCountService
     * @param TitleChannelPlayCountService $titleChannelPlayCountService
     */
    public function __construct(Database $db, ProgramService $programService, FillerService $fillerService,
                                ChannelRestrictionService $channelRestrictionService, ChannelService $channelService,
                                ShowChannelPlayCountService $showChannelPlayCountService, TitleService $titleService,
                                TitleChannelPlayCountService $titleChannelPlayCountService) {
        $this->db = $db;
        $this->programService = $programService;
        $this->fillerService = $fillerService;
        $this->channelService = $channelService;
        $this->titleService = $titleService;
        $this->titleChannelPlayCountService = $titleChannelPlayCountService;
        $this->channelRestrictionService = $channelRestrictionService;
        $this->showChannelPlayCountService = $showChannelPlayCountService;
    }


    public function buildAllChannelsTil(string $builTillTime) {
        $enabledChannels = $this->channelService->getEnabledChannels();
        foreach ($enabledChannels as $channel) {
            $this->buildScheduleTill($channel, $builTillTime);
        }
    }


    /**
     * Builds a schedule for a channel until a date/time has been reached
     * @param Channel $channel
     * @param string $buildTillTime
     */
    public function buildScheduleTill(Channel $channel, string $buildTillTime) {
        $lastProgram = $this->programService->getLastScheduledItem($channel);
        if ($lastProgram != null) {
            $startTime = $lastProgram->endTime;
        } else {
            $startTime = Utils::GetCurrentDateTimeString();
        }

        $this->buildScheduleForDates($channel, $startTime, $buildTillTime);
    }

    /**
     * Builds a schedule for a channel for between two periods. Assumes no programs is there already
     * @param Channel $channel
     * @param string $startTime
     * @param string $endTime
     */
    private function buildScheduleForDates(Channel $channel, string $startTime, string $endTime) {
        $currentUnixTime = strtotime($startTime);
        $endTimeUnix = strtotime($endTime);
        $previousTitleIds = [];

        while ($currentUnixTime < $endTimeUnix) {

            // Add a program...
            $title = $this->getTitleForChannel($channel, $currentUnixTime);
            $program = new Program();
            $program->startTime = Utils::UnixTimeToDateTime($currentUnixTime);
            $program->endTime = Utils::UnixTimeToDateTime($currentUnixTime + $title->durationSeconds);
            $program->titleId = $title->id;
            $program->channelId = $channel->id;
            $program->isFiller = false;
            $this->programService->addProgram($program);


            $name = $title->name;
            if ($title->showId !== null) {
                $name = $title->showName . ' ' . $name;
            }

            echo date("Y-m-d H:i:s", $currentUnixTime) . ' - ' . date("Y-m-d H:i:s", $currentUnixTime + $title->durationSeconds) .    ' - ' . $name . ' - Run length: ' . Utils::GetReadableDuration($title->durationSeconds) . PHP_EOL;

            $currentUnixTime += $title->durationSeconds;

            // Add filler content to next boundary if required.
            if ($channel->padWithFiller == true) {
                $currentUnixTime = $this->addFiller($channel, $currentUnixTime);
            }


        }
    }

    private function getTitleForChannel(Channel $channel, $startTimeUnix): Title {
        if ($channel->useMovies && $channel->useTv) {
            # have to make a choice
        }

        if ($channel->useMovies) {
            # Choose an unplayed title
            return $this->selectMovieTitle($channel, $startTimeUnix);
        }

        if ($channel->useTv) {
            return $this->selectTvTitle($channel, $startTimeUnix);
        }
        return null;
    }

    /**
     * Adds filler till the next half hour schedule
     * @param Channel $channel Channel to add to
     * @param int $currentUnixTime Current unix time
     * @return int
     */
    private function addFiller(Channel $channel, int $currentUnixTime): int {
        $unixEndTime = $this->determineFillerEndTime($currentUnixTime);
        $excludeTitles = [];

        while ($currentUnixTime < $unixEndTime) {
            $maxTime = $unixEndTime - $currentUnixTime;
            $title = $this->fillerService->getFillerTitle($maxTime, $excludeTitles);


            $program = new Program();
            $program->startTime = Utils::UnixTimeToDateTime($currentUnixTime);
            $program->titleId = $title->id;
            $program->channelId = $channel->id;
            $program->isFiller = true;


            if ($title->durationSeconds > $maxTime) {
                $runInfo = Utils::GetReadableDuration($maxTime) . ' (truncated) ';
                $program->endTime = Utils::UnixTimeToDateTime($currentUnixTime + $maxTime);
                $currentUnixTime += $maxTime;
                $program->fillerCutSeconds = $maxTime;
            } else {
                $runInfo = Utils::GetReadableDuration($title->durationSeconds);
                $program->endTime = Utils::UnixTimeToDateTime($currentUnixTime + $title->durationSeconds);
                $currentUnixTime += $title->durationSeconds;
            }

            $excludeTitles[] = $title->id;
            $this->programService->addProgram($program);

            $name = $title->name;
            if ($title->showId !== null) {
                $name = $title->showName . ' ' . $name;
            }

            echo date("Y-m-d H:i:s", $currentUnixTime) . ' - ' . $program->endTime .
                ' - Filler: ' . $name .
                ' - Run length: ' . $runInfo
                . PHP_EOL;
        }
        return $currentUnixTime;
    }


    /**
     * Selects a random movie title
     * @param Channel $channel
     * @param $startTimeUnix
     * @return Title
     * @throws \Exception
     */
    private function selectTvTitle(Channel $channel, $startTimeUnix): Title {
        $params = [ ':channelId' => $channel->id ];
        $restrictionsSQL = $this->buildRestrictions($channel, $params, $startTimeUnix);
        $sql = 'select s.id as showId, p.id as playId, p.playCount, p.episodeIndex from tvshow s, genre g,
                 tvshow_genre tsg
                left join show_channel_play_count p on p.showId = s.id and channelId = :channelId 
                where g.id = tsg.genreId and tsg.showId = s.id ' . $restrictionsSQL . ' 
                order by  random() desc limit 1';
        $result = $this->db->getRow($sql, $params);
        # TODO: Handle no results...


        $showId = $result['showId'];
        $playId = $result['playId'];
        $playCount = $result['playCount'];
        $episodeIndex  = (int) $result['episodeIndex'];
        if ($episodeIndex === null) {
            $episodeIndex = -1;
        }
        if ($playCount === null) {
            $playCount = 0;
        }


        # Figure out the next title to play in a show....
        $title = $this->titleService->getNextEpisodeForShow($showId, $episodeIndex);
        $newEpisodeIndex = (int) $title->episodeIndex;
        if ($newEpisodeIndex <= $episodeIndex) {
            // We have wrapped. Update play count
            $playCount++;
        }
        $this->showChannelPlayCountService->update($channel->id, $playId, $showId, $title->episodeIndex, $playCount);
        return $title;
    }


    /**
     * Selects a random movie title
     * @param Channel $channel
     * @param $startTimeUnix
     * @return Title
     * @throws \Exception
     */
    private function selectMovieTitle(Channel $channel, $startTimeUnix): Title {
        $params = [];
        $restrictionSQL = $this->buildRestrictions($channel, $params, $startTimeUnix);

        $sql = "select t.id, t.showId, p.id as playId, p.playCount, t.showName  from title t, media_source s, genre g, title_genre tg
                left join title_channel_play_count p on p.titleId = t.id
                where s.id = t.mediaSourceId and s.movieSource = 1 and g.id = tg.genreId and tg.titleId = t.id
                 $restrictionSQL order by p.playCount, random() desc limit 1";
        $result = $this->db->getRow($sql, $params);
        # TODO: Handle no results...


        $titleId = $result['id'];
        $showId = $result['showId'];
        $playId = $result['playId'];
        $this->titleChannelPlayCountService->update($channel->id, $titleId, $showId, $playId);
        return $this->titleService->getById($titleId);
    }

    /**
     * Determines the cut off point for filler if its 30 or 60 minute past
     * @param int $currentUnixTime Current time
     * @return int Unix time of ending
     */
    private function determineFillerEndTime(int $currentUnixTime): int {
        $currentMinutes = (int) date('i', $currentUnixTime);
        if ($currentMinutes == 0 || $currentMinutes == 30) {
            return $currentUnixTime;
        }
        if ($currentMinutes < 30) {
            $endTime = 30;
        } else {
            $endTime = 60;
        }
        $targetUnixTime = $currentUnixTime + (($endTime - $currentMinutes) * 60);
        # Remove the seconds part
        $dateStr = date('Y-m-d H:i', $targetUnixTime) . ':00';
        $timeConvertUnix = strtotime($dateStr);
        $timeConvertUnix--; # Remove 1 second
        return $timeConvertUnix;
    }


    /**
     * Builds the restriction SQL to add to the title find SQL
     *
     * @param Channel $channel
     * @param array $params
     * @param int $startTimeUnix
     * @return string
     * @throws \Exception
     */
    private function buildRestrictions(Channel $channel, array $params, int $startTimeUnix): string {
        $sql = '';
        $crs = $this->channelRestrictionService->getAllEnabledForChannel($channel);
        foreach ($crs as $cr) {
            $sql .= ' and (' . $this->buildRestrictCriteria($cr, $params, $startTimeUnix) . ') ';
        }

        return $sql;
    }

    /**
     * @param ChannelRestriction $cr
     * @param array $params
     * @param int $startTimeUnix
     * @return string
     * @throws \Exception
     */
    private function buildRestrictCriteria(ChannelRestriction $cr, array $params, int $startTimeUnix): string {
        $sql = '';
        $valArray = explode('|', $cr->value);
        $escapeBasicStart = "'";
        $escapeBasicEnd = "'";
        $escapeLikeStart = "upper('%";
        $escapeLikeEnd = "%')";

        switch ($cr->name) {
            case 'GENRE_LIKE':
                $conditionSQL = ' upper(g.name) like ';
                return $this->buildRestrictValueArray($conditionSQL, $valArray, $escapeLikeStart, $escapeLikeEnd, ' OR ');
                break;
            case 'GENRE_NOT_LIKE':
                $conditionSQL = ' upper(g.name) not like ';
                return $this->buildRestrictValueArray($conditionSQL, $valArray, $escapeLikeStart, $escapeLikeEnd, ' OR ');
                break;
            case 'SHOW_NAME':
                $conditionSQL = ' s.name = ';
                return $this->buildRestrictValueArray($conditionSQL, $valArray, $escapeBasicStart, $escapeBasicEnd, ' OR ');
            case 'NOT_SHOW_NAME':
                $conditionSQL = ' s.name <> ';
                return $this->buildRestrictValueArray($conditionSQL, $valArray, $escapeBasicStart, $escapeBasicEnd, ' OR ');
            case "YEAR_OR_BEFORE":
                return " year <= '" . $this->db->escapeString($cr->value ) . "' ";
            case "YEAR_OR_AFTER":
                return " year >= '" . $this->db->escapeString($cr->value ) . "' ";
            default:
                throw new \Exception("Unknown criteria: " . $cr->name);
        }
    }

    private function buildRestrictValueArray(string $conditionSQL, array $values, string $escapeStart, string $escapeEnd, string $conditional): string {
        $first = true;
        $sql = '';
        foreach ($values as $val) {
            if ($first == false) {
                $sql .= " $conditional ";
            } else {
                $first = false;
            }
            $sql .= $conditionSQL . $escapeStart . $this->db->escapeString($val) . $escapeEnd;
        }

        return $sql;
    }


}
