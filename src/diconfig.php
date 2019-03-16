<?php

use function DI\autowire;
use function DI\create;
use function DI\get;
use ptv\service\APIService;
use ptv\service\ChannelRestrictionService;
use ptv\service\ChannelService;
use ptv\service\GenreService;
use ptv\service\MediaSourceService;
use ptv\PlexServer;
use ptv\service\ProgramService;
use ptv\service\ScheduleBuilderService;
use ptv\service\TitleService;
use ptv\service\TvShowGenreService;
use ptv\service\TvShowService;


if (isset($_ENV['PLEX_TOKEN']) == false) {
    throw new \Exception('Missing plex token');
}

if (isset($_ENV['PLEX_HOST']) == false) {
    throw new \Exception('Missing plex host');
}


return [
    'plexserver.token' => $_ENV['PLEX_TOKEN'],
    'plexserver.host' => $_ENV['PLEX_HOST'],
    PlexServer::class => create()->constructor(get('plexserver.host'), get('plexserver.token')),
    MediaSourceService::class => autowire(),
    TitleService::class => autowire(),
    GenreService::class => autowire(),
    ChannelService::class => autowire(),
    ProgramService::class => autowire(),
    ScheduleBuilderService::class => autowire(),
    APIService::class => autowire(),
    ChannelRestrictionService::class => autowire(),
    TvShowService::class => autowire(),
    TvShowGenreService::class => autowire()
];