# Star Trek Timeline

A Star Trek fiction timeline covering all media (TV, cinema, streaming, novels, comics), written in PHP.

**At the moment this is just a prototype.**

## Prerequisites

Star Trek Timeline requires PHP >= 7.2.

## Installation

Use [Composer](https://getcomposer.org/) to install this package:

```
composer require etienneq/star-trek-timeline
```

## Goals

When finished this package aims to provide a timeline for all published Star Trek.

Episodes, novels etc. will be managed in [CSV files](resources) devided by media, series etc. to be easily updatable.
This library will automatically sort all entries chronologically.

An API will be provided to read the timeline in an object-oriented fashion.
Additionally it will be possible to filter timeline entries by year, series etc.

At the moment just few data is present:
* ENT season 1
* ENT novel "What Price Honor?"
* ENT Rise of the Federation novels
* DS9 season 1
* TNG season 6
* TNG novel "Requiem"

 But it's enough to demonstrate the intended potential. Take a look [here](timeline_example.md).

I'm planning to launch a website which utilizes this library to make the timeline accessible for all internet users.

## Data sources

* Discovery Season 1: [https://startreklitverse.yolasite.com/discovery-chronology.php](https://startreklitverse.yolasite.com/discovery-chronology.php)