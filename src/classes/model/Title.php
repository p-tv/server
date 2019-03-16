<?php

namespace ptv\model;


class Title implements ModelHydration {
    var $id;
    var $name;
    var $durationSeconds;
    var $mediaSourceId;
    var $plexKey;
    var $thumbUrl;
    var $summary;
    var $tagLine;
    var $rating;
    var $contentRating;
    var $year;
    var $enabled = true;
    var $showName = '';
    var $episodeIndex = 0;
    var $showId = null;

    var $genres;

    function fromArray(array $arr) {
        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->durationSeconds = $arr['durationSeconds'];
        $this->mediaSourceId = $arr['mediaSourceId'];
        $this->plexKey = $arr['plexKey'];
        $this->thumbUrl = $arr['thumbUrl'];
        $this->summary = $arr['summary'];
        $this->tagLine = $arr['tagLine'];
        $this->rating = $arr['rating'];
        $this->contentRating = $arr['contentRating'];
        $this->year = $arr['year'];
        $this->enabled = $arr['enabled'];
        $this->episodeIndex = $arr['episodeIndex'];
        $this->showName = $arr['showName'];
        $this->showId = $arr['showId'];
    }

    function getInsertSQL(): string {
        return 'insert into title (name, durationSeconds, mediaSourceId, plexKey, thumbUrl, summary, tagLine, rating, 
                   contentRating, year, enabled, episodeIndex, showName, showId) 
                values (:name, :durationSeconds, :mediaSourceId, :plexKey, :thumbUrl, :summary, :tagLine, :rating, 
                        :contentRating, :year, :enabled, :episodeIndex, :showName, :showId)';
    }

    function getInsertParameters(): array {
        return [
            ':name' => $this->name,
            ':durationSeconds' => $this->durationSeconds,
            ':mediaSourceId' => $this->mediaSourceId,
            ':plexKey' => $this->plexKey,
            ':thumbUrl' => $this->thumbUrl,
            ':summary' => $this->summary,
            ':tagLine' => $this->tagLine,
            ':rating' => $this->rating,
            ':contentRating' => $this->contentRating,
            ':year' => $this->year,
            ':enabled' => $this->enabled,
            ':episodeIndex' => $this->episodeIndex,
            ':showName' => $this->showName,
            ':showId' => $this->showId
        ];

    }

    function getUpdateSQL(): string {
        return 'update title set name = :name, durationSeconds = :durationSeconds, mediaSourceId = :mediaSourceId, 
                 plexKey = :plexKey, thumbUrl = :thumbUrl, summary = :summary, tagLine = :tagLine, rating = :rating, 
                 contentRating = :contentRating, year = :year, enabled = :enabled, episodeIndex = :episodeIndex,
                 showId = :showId where id = :id';
    }

    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }

    /**
     * Returns if this title is part of a tv show.
     * @return bool
     */
    public function isShow(): bool {
        return $this->showId != null;
    }
}