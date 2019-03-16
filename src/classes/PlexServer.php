<?php

namespace ptv;


use ptv\model\MediaSource;
use ptv\model\Title;

class PlexServer {

    private $token;

    private $api;
    private $host;

    /**
     * PlexServer constructor.
     * @param string $host
     * @param string $token
     */
    public function __construct(string $host, string $token) {
        $this->token = $token;
        $this->host = $host;
        $this->api = new PlexApi($host);
        $this->api->setToken($token);
    }


    public function getTvTitles(MediaSource $source): array {
        $titles = [];
        $plexTitles = $this->api->getLibrarySectionContents($source->plexKey);
        foreach ($plexTitles['Directory'] as $directory) {
            $ratingKey = $directory['ratingKey'];
            $showName = $directory['title'];
            $thumbUrl = $directory['thumb'];


            # Get genres
            $genres = $this->getGenresFromArray($directory);
            $children = $this->api->getAllLeaves($ratingKey);

            if ($children['size'] == 1) {
                $videos = [ $children['Video'] ];
            } else {
                $videos = $children['Video'];
            }

            $episodeIndex = 1;

            foreach ($videos as $child) {
                $title = new Title();
                $title->name = $this->buildTvShowName($showName, $child);
                $title->durationSeconds = floor($child['duration'] / 1000.0);
                $title->thumbUrl = $thumbUrl;
                $title->plexKey = $child['ratingKey'];
                $title->mediaSourceId = $source->id;
                $title->year = $this->nullArrayToEmpty($child, 'year');
                $title->contentRating = $this->nullArrayToEmpty($child, 'contentRating');
                $title->rating = $this->nullArrayToEmpty($child, 'rating');
                $title->summary = $this->nullArrayToEmpty($child, 'summary');
                $title->tagLine = '';
                $title->showName = $showName;
                $title->genres = $genres;
                $title->episodeIndex = $episodeIndex;
                $episodeIndex++;

                $titles[] = $title;


            }
        }
        return $titles;

    }

    /**
     * Returns a list of titles for a source
     * @param MediaSource $source
     * @return Title[]
     */
    public function getTitles(MediaSource $source): array {
        $plexTitles = $this->api->getLibrarySectionContents($source->plexKey);
        $titles = [];
        foreach ($plexTitles['Video'] as $video) {
            $title = new Title();
            $title->name = $video['title'];
            $title->durationSeconds = floor($video['duration'] / 1000.0);
            $title->thumbUrl = $video['thumb'];
            $title->plexKey = $video['ratingKey'];
            $title->mediaSourceId = $source->id;
            $title->year = $this->nullArrayToEmpty($video, 'year');
            $title->contentRating = $this->nullArrayToEmpty($video, 'contentRating');
            $title->rating = $this->nullArrayToEmpty($video, 'rating');
            $title->summary = $this->nullArrayToEmpty($video, 'summary');
            $title->tagLine = $this->nullArrayToEmpty($video, 'tagline');
            $titles[] = $title;
            $title->genres = $this->getGenresFromArray($video);
        }
        return $titles;
    }

    /**
     * Generates a transcode/play steam. WIP for media buffer / steam selection / resolution etc
     * @param Title $title Title to play
     * @return string a URL to play the dash stream
     * @throws \Exception
     */
    public function getPlayURL(TItle $title): string {
        $sessionID = Utils::MakeGUID();

        /*
         * Build a DASH based URL
         *
        $url = $this->getBaseUrl();
        $url .= '/video/:/transcode/universal/start.mpd?hasMDE=1&path=' . urlencode('/library/metadata/' . $title->plexKey);
        $url .= '&mediaIndex=0&partIndex=0';
        $url .= '&protocol=dash&fastSeek=1';
        $url .= '&location=lan';
        $url .= '&addDebugOverlay=0&autoAdjustQuality=0&directStreamAudio=0&mediaBufferSize=102400';
        $url .= '&session=' . $sessionID;
        $url .= '&subtitles=burn&Accept-Language=en';
        $url .= '&X-Plex-Session-Identifier=' . $sessionID;
        $url .= '&X-Plex-Client-Profile-Extra=append-transcode-target-codec%28type%3DvideoProfile%26context%3Dstreaming%26audioCodec%3Daac%26protocol%3Ddash%29';
        $url .= '&X-Plex-Product=Plex%20Web&X-Plex-Version=3.77.4&X-Plex-Client-Identifier=9gn4eujufpayrv64sl7c7gow&X-Plex-Platform=Chrome&X-Plex-Platform-Version=71.0&X-Plex-Sync-Version=2&X-Plex-Device=Windows&X-Plex-Device-Name=Chrome&X-Plex-Device-Screen-Resolution=1920x1009%2C1920x1200';
        $url .= '&X-Plex-Token=' . urlencode($this->token);
        $url .= '&X-Plex-Language=en';
        */


        $url =  $this->getBaseUrl();
        $url .= '/video/:/transcode/universal/start.m3u8?hasMDE=1&path=' . urlencode('/library/metadata/' . $title->plexKey);
        $url .= '&mediaIndex=0';
        $url .= '&partIndex=0';
        $url .= '&fastSeek=1';
        $url .= '&location=lan';
        $url .= '&addDebugOverlay=0';
        $url .= '&autoAdjustQuality=0';
        $url .= '&directStreamAudio=0';
        $url .= '&mediaBufferSize=102400';
        $url .= '&session=' . $sessionID;
        #$url .= '&subtitles=burn';
        $url .= '&Accept-Language=en';
        $url .= '&X-Plex-Session-Identifier=' . $sessionID;
        $url .= '&X-Plex-Product=Plex%20Web';
        $url .= '&X-Plex-Version=3.77.4';
        $url .= '&X-Plex-Client-Identifier=9gn4eujufpayrv64sl7c7gow';
        $url .= '&X-Plex-Platform=Chrome';
        $url .= '&X-Plex-Platform-Version=71.0';
        $url .= '&X-Plex-Sync-Version=2';
        $url .= '&X-Plex-Device=Windows';
        $url .= '&X-Plex-Device-Name=Chrome';
        $url .= '&X-Plex-Device-Screen-Resolution=1920x1009%2C1920x1200';
        $url .= '&X-Plex-Token=' . $this->token;
        $url .= '&X-Plex-Language=en';


        return $url;
    }

    /**
     * Gets the list of all media sources in plex
     * @return MediaSource[]
     */
    public function getSections(): array {
        $ret = [];
        $sections = $this->api->getLibrarySections();
        foreach ($sections['Directory'] as $section) {
            $mediaSource = new MediaSource();
            $mediaSource->plexKey = $section['key'];
            $mediaSource->name = $section['title'];
            $ret[] = $mediaSource;
        }
        return $ret;
    }

    /**
     * Gets the base URL for the server
     * @return string
     */
    public function getBaseUrl() {
        return 'http://' . $this->host . ':32400';
    }


    /**
     * Gets a section key from a name
     *
     * @param string $name section name
     * @return int section key name from plex
     * @throws \Exception
     */
    private function getPlexSectionKey(string $name): int {
        $sections = $this->api->getLibrarySections();
        foreach ($sections['Directory'] as $section) {
            if ($section['title'] === $name) {
                return $section['key'];
            }
        }
        throw new \Exception("Unable to find key for section: $name");
    }

    /**
     * Checks an array key exists and contains a string other wise returns an empty string
     * @param array $arr Array to test
     * @param string $key Key to check
     * @return string
     */
    private function nullArrayToEmpty(?array $arr, string $key) {
        if ($arr == null || is_array($arr ) === false) {
            return '';
        }
        if (array_key_exists($key, $arr) === false) {
            return '';
        }
        $val = $arr[$key];
        if ($val == null) {
            return '';
        }
        return $val;
    }

    /**
     * Generates a list of strings of genres from the plex objects
     * @param array $plexGenreArray
     * @return array|null
     */
    private function getGenresFromArray(array $plexGenreArray): ?array {
        $ret = null;
        if (array_key_exists('Genre', $plexGenreArray)) {
            $ret = [];
            foreach ($plexGenreArray['Genre'] as $genre) {
                if (is_array($genre) && array_key_exists('tag', $genre)) {
                    $ret[] = $genre['tag'];
                } else {
                    $ret[] = (string) $genre;
                }
            }
        }
        return $ret;

    }

    /**
     * Builds a TV show name from its title and parent title.
     *
     * @param string $showName Show name to build from
     * @param array $values Array of fields to use.
     * @return string
     */
    private function buildTvShowName(string $showName, array $values): string {
        $ret = '';
        if (isset($values['parentTitle'])) {
            if ($values['parentTitle'] != $showName) {
                $ret .= $values['parentTitle'] . ' - ';
            }
        }
        if (isset($values['index'])) {
            $ret .= 'Ep ' . $values['index'] . ' - ';
        }
        return $ret . $values['title'];
    }

}