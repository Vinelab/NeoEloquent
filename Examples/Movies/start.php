<?php

use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config/database.php';

// calls
countMovies();
showAllMovies();
countActors();
showAllActors();
showActorsWithNameEndingWith("s");
showFirstMovieWithActorsLazyLoaded();

// implementation
function showFirstMovieWithActorsLazyLoaded()
{
    $movie = Movie::first();
    $actors = $movie->actors;

   // show movies
    $output = new ConsoleOutput();
    $output->writeln("");
    $output->writeln("<question>Showing the actors for the movie:</question> <info>".$movie->title."</info>");
    $output->writeln("--------------------------------------------");
    foreach ($actors as $actor) {
        $output->writeln("- ".$actor->name);
    }
    $output->writeln("");
}

/**
 * Show only the actors whose names end with the given letter.
 *
 * @param  string $letter
 */
function showActorsWithNameEndingWith($letter)
{
    $actors = Actor::where('name', '=~', ".*$letter$")->get();

    // show actors
    $output = new ConsoleOutput();
    $output->writeln("<question>Actors with their names ending with the letter \"$letter\"</question>");
    $output->writeln("--------------------------------------------------");
    foreach ($actors as $actor) {
        $output->writeln("- ".$actor->name);
    }
    $output->writeln("");
}

function countMovies()
{
    $count = Movie::count();

    $output = new ConsoleOutput();
    $output->writeln("<info>There are $count movies.</info>");
    $output->writeln("");
}

function countActors()
{
    $count = Actor::count();

    $output = new ConsoleOutput();
    $output->writeln("<info>There are $count actors.</info>");
    $output->writeln("");
}

function showAllMovies()
{
    // fetch all movies
    $movies = Movie::get();

    // show all movies
    $output = new ConsoleOutput();
    $output->writeln("<question>Movies:</question>");
    $output->writeln("-------");
    foreach ($movies as $movie) {
        $output->writeln("- ".$movie->title);
    }
    $output->writeln("");
}

function showAllActors()
{
    // fetch all actors
    $actors = Actor::all();

    // show all actors
    $output = new ConsoleOutput();
    $output->writeln("<question>Actors:</question>");
    $output->writeln("-------");
    foreach ($actors as $actor) {
        $output->writeln("- ".$actor->name);
    }
    $output->writeln('');
}
