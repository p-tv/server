<?php

namespace ptv\service;


class FillerService {
    /**
     * @var MediaSourceService
     */
    private $mediaSourceService;
    /**
     * @var TitleService
     */
    private $titleService;

    /**
     * GenreService constructor.
     * @param MediaSourceService $mediaSourceService
     * @param TitleService $titleService
     */
    public function __construct(MediaSourceService $mediaSourceService, TitleService $titleService) {
        $this->mediaSourceService = $mediaSourceService;
        $this->titleService = $titleService;
    }


    /**
     * Gets a filler title to play
     * @param int $maxSeconds
     * @param array $excludeTitleIds List of title id to exclude if not null (to prevent 2 titles in a row)
     * @return \ptv\model\Title
     */
    public function getFillerTitle(int $maxSeconds, array $excludeTitleIds) {
        $fillerSources = $this->mediaSourceService->getAllFillerSources();
        $item = $this->titleService->getRandomTitleWithMaxLength($fillerSources, $maxSeconds, $excludeTitleIds);
        if ($item != null) {
            return $item;
        }
        return $this->titleService->getRandomTitle($fillerSources, $excludeTitleIds);
    }

}