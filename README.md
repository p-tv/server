# PTV Server

This is WIP and doesn't have a nice editing front end yet. You will also need to know how to get an environment up and running until this gets dockerized.


### Requirements
* PHP 7.2+
* PHP Extensions: sqlite3, json

### Installation

1. Checkout source
2. Download composer into root directory
3. Run composer install to install dependancies.


### Running
1. Fist get a plex token.
2. Set it to an enviroment variable PLEX_TOKEN and the IP address of your plex server to PLEX_HOST (or edit diconfig.php and put them in there)
3. In the src/scripts directory run the UpdateMediaSources.php file to create your database end load in sources from plex.
4. Use a sqlite front end to enable media sources and categorize them to tv/movies/filler.
5. Use sqllite to create a channel called all movies, enabled it and useMovies content source.
6. In src/scripts run UpdateTitles.php - This will take a long time depending on the amount of content to populate the local database with content info.
7. In src/scripts run UpdateSchedule.php - This will build a schedule of programs for the next 24 hours.
8. To start API server goto the src/web directory and run php -S <ip>:8000 to server

These steps are very clunky and will hopefully one day be replaced with a nice web interface and be setup in a docker container.
