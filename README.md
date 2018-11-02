# Star Trek Timeline

A Star Trek fiction timeline covering all media (TV, cinema, novels, comics), written in PHP.

**At the moment this is still a prototype.**

When finished this library aims to provide a timeline for all published Star Trek.

Episodes, novels etc. are organized in CSV files. This files are devided in a human readable fashion (e.g. by media or series) to be easily manageable and updateable.

This library automatically sorts all entries chronologically as excactly as possible using a number of criteria (given stardate, publication date, episode number etc.). If needed entries can be placed manually at a certain place on the timeline.

An API is provided that returns the timeline in an object-oriented fashion.
It's planned to add the possibility to filter timeline entries by year, series etc.

## Prerequisites

Star Trek Timeline requires PHP >= 7.2.

## Installation

Use [Composer](https://getcomposer.org/) to install this package:

```
composer require etienneq/star-trek-timeline
```

## Current status of prototype

At the moment the data is not yet complete. But it's enough to demonstrate the intended potential. Take a look [here](htmlpreview.github.io/?https://github.com/etienneq/star-trek-timeline/blob/master/timeline.html).

I'm planning to launch a website which utilizes this library to make the timeline accessible for all internet users in a dynamic fashion.

## Data structure

Data files are located in the [resources](resources) directory.
There are two types of files: Items files (\*.csv) which contain all the episodes, books etc. and meta data (\*.ini) files which give additional information about a particular series or season.

* [Defining items files](doc/items-files.md)
* [Defining meta data files](doc/meta-data-files.md)

## Data sources

Besides my own notes the following sources were used for creating the data files.

### TV series

* Star Trek: [memory-alpha.wikia.com](http://memory-alpha.wikia.com/wiki/Star_Trek:_The_Original_Series)
* Star Trek: The Animated Series: [memory-alpha.wikia.com](http://memory-alpha.wikia.com/wiki/Star_Trek:_The_Animated_Series)
* Star Trek: The Next Generation: [memory-alpha.wikia.com](http://en.memory-alpha.wikia.com/wiki/Star_Trek%3A_The_Next_Generation)
* Star Trek: Deep Space Nine: [memory-alpha.wikia.com](http://en.memory-alpha.wikia.com/wiki/Star_Trek%3A_Deep_Space_Nine)
* Star Trek: Voyager: [memory-alpha.wikia.com](http://memory-alpha.wikia.com/wiki/Star_Trek%3A_Voyager)
* Star Trek: Enterprise: [memory-alpha.wikia.com](http://en.memory-alpha.wikia.com/wiki/Star_Trek%3A_Enterprise)
* Star Trek: Discovery: [startreklitverse.yolasite.com](https://startreklitverse.yolasite.com/discovery-chronology.php)

### Movies

* TOS and TNG movies: [memory-alpha.wikia.com](http://memory-alpha.wikia.com/wiki/Star_Trek_films)

### Novels

* Pocket Books novel list: [startreklitverse.yolasite.com](https://startreklitverse.yolasite.com/complete-pocket-books-novel-list.php)
    * Up until June '18 release (DSC "Fear Itself")