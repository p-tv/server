<?php

namespace ptv\model;


class TitleGenre implements ModelHydration {
    var $titleId;
    var $genreId;

    function fromArray(array $arr) {
        $this->titleId = $arr['titleId'];
        $this->genreId = $arr['genreId'];
    }

    function getInsertSQL(): string {
        return 'insert into title_genre (titleId, genreId) values (:titleId, :genreId)';
    }

    function getInsertParameters(): array {
        return [':titleId' => $this->titleId, ':genreId' => $this->genreId];
    }

    function getUpdateSQL(): string {
        throw new \Exception('Unable to perform updates on this');
    }

    function getUpdateParameters(): array {
        throw new \Exception('Unable to perform updates on this');
    }


}