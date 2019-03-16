<?php

namespace ptv\service;


use ptv\Database;
use ptv\model\Genre;
use ptv\model\Title;

class TitleGenreService {

    /**
     * @var Database
     */
    private $db;
    /**
     * @var GenreService
     */
    private $genreService;

    /**
     * GenreService constructor.
     * @param Database $db
     * @param GenreService $genreService
     */
    public function __construct(Database $db, GenreService $genreService) {
        $this->db = $db;
        $this->genreService = $genreService;
    }


    /**
     * @param Title $title
     * @return Genre[]
     */
    public function getGenresForTitle(Title $title) {
        $ids = $this->getIdsForTitle($title);
        return $this->genreService->getForIds($ids);
    }

    public function updateGenresForTitle(Title $title, ?array $genreList) {
        if ($genreList == null) {
            $genreList = [];
        }
        $existingGenreIds = $this->getGenresForTitle($title);
        foreach ($genreList as $genreName) {
            $id = $this->genreService->getIdForName($genreName, true);
            $existingArrayIndex = array_search($id, $existingGenreIds);
            if ($existingArrayIndex !== false) {
                # Remove it still there
                array_splice($existingGenreIds, $existingArrayIndex, 1);
            } else {
                # Add it as new
                $this->db->execute('insert into title_genre (titleId, genreId) VALUES (:titleId, :genreId)', [':titleId' => $title->id, ':genreId' => $id]);
            }
        }
        # Clear out any genres that are still present.
        if (count($existingGenreIds) > 0) {
            $inString = $this->db->buildInString($existingGenreIds);
            $this->db->execute("delete from title_genre where titleId = :id and genreId in $inString", [':id' => $title->id]);
        }
    }

    private function getIdsForTitle(Title $title) {
        return $this->db->getColumn('select genreId from title_genre where titleId = :titleId', [$title->id]);
    }
}