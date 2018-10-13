# Star Trek Timeline

A Star Trek fiction timeline covering all media (TV, cinema, novels, comics), written in PHP.

**At the moment this is still a prototype.**

When finished this library aims to provide a timeline for all published Star Trek.

Episodes, novels etc. are organized in [CSV files](resources). This files are devided in a human readable fashion (e.g. by media or series) to be easily manageable and updateable.

This library automatically sorts all entries chronologically as excactly as possible using a number of criteria (given stardate, publication date, episode number etc.).

An API will be provided to read the timeline in an object-oriented fashion.
Additionally it will be possible to filter timeline entries by year, series etc.

## Prerequisites

Star Trek Timeline requires PHP >= 7.2.

## Installation

Use [Composer](https://getcomposer.org/) to install this package:

```
composer require etienneq/star-trek-timeline
```

## Current status of prototype

At the moment just few data is present:
* DSC season 1
* ENT season 1
* TNG season 6
* DS9 season 1
* some novels

But it's enough to demonstrate the intended potential. Take a look [here](timeline_example.md).

I'm planning to launch a website which utilizes this library to make the timeline accessible for all internet users in a dynamic fashion.

## Data structure

Data files are located in the [resources](resources) directory.
There are two types of files: Data files (\*.csv) which contain all the episodes and books and meta data (\*.json) files which give additional information about a particular series or season.

* [Defining data files](doc/data-files.md)
* [Defining meta data files](doc/meta-data-files.md)

## Data sources

The following sources were used for creating the data files.

* Enterprise: [http://de.memory-alpha.wikia.com/wiki/Star_Trek%3A_Enterprise](http://de.memory-alpha.wikia.com/wiki/Star_Trek%3A_Enterprise)
* Discovery Season 1: [https://startreklitverse.yolasite.com/discovery-chronology.php](https://startreklitverse.yolasite.com/discovery-chronology.php)
* Deep Space Nine: [http://de.memory-alpha.wikia.com/wiki/Star_Trek%3A_Deep_Space_Nine](http://de.memory-alpha.wikia.com/wiki/Star_Trek%3A_Deep_Space_Nine)