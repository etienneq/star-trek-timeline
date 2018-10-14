# Data files

Data files can be organized in a hierarchical directory structure. Each sub directory and each CSV file is called a package thus allowing to create a hierarchy of packages (parent and child packages). There are no naming conventions for data files except the file ending csv.

The first line must be a header line containing the name of all fields.

```
"number","title","startDate","endDate","startStardate","endStardate","publicationDate","after","details"
```

* number - episode or book number
* title - title of episode or book
* startDate - gregorian (earth) date when story begins
* endDate - gregorian (earth) date when story ends, format: YYYY, YYYY-MM or YYYY-MM-DD
* startStardate - stardate, TOS or TNG style
* endStardate - stardate, TOS or TNG style
* publicationDate - format: YYYY-MM-DD
* after - used for manuel sort order of current entry (see below)
* details - some additional information regarding current entry

title and startDate are mandatory attributes. All other attributes are optional.

The second to nth line contains the timeline entries.

Use a coma as field separator. If necessary fields can be enclosed in double quotes. It's best practice to enclose at least the title field.
If a field contains a coma ist must be enclosed in double quotes.

The order in which entries are listed within a single data file is of no importance because all entries will be sorted automatically.
Only when single chapters/sections should become individual timeline entries the order within the data file is important (see below).

## Format of startDate and endDate

Both real-world date fields may contain a full date, year/month combination or just the year.
Additionally B.C. years are defined as negative dates, approximate years are preceded by a tilde.

Examples:
* 2369 // the year 2369
* 2372-10 // october 2372
* 2375-05-02 // october 2, 2375
* -2000 // 2000 BC
* ~-10000 // c. 10.000 B.C.

## Automated sort order

There are a number of criteria used to sort timeline entries in an automated fashion:

* by TNG-era stardate (TOS stardates are ignored)
* by start date (as exactly as possible), examples: 2365-10-01 is greater than 2365-09; 2365-10 can't be compared to 2365 (because second date could be less or greater than first one)
* by publication date if both compared entries are from a TNG-era TV series or are from the same data file
* by number if both compared entries are from the same data file

If automated sorting doesn't yield the desired results it's possible to define an entry's predecessor manually.

## Manual sort order to overwrite automated sort order

Predecessor relationship is defined in an entry's 'after' fields.
The predecessor is referenced by it's key.
An entry's key is defined by it's package name and its number (or auto-generated acronym if no number is set).

The package name corresponds to relative filesystem path + filename without ending.
Example: tv/ds9/my-package.csv -> tv/ds9/my-package

The acronym is generated from an entry's title and is always upper-case.
Example: Rise of the Federation -> ROTF

Examplary data file tv/ds9/my-package.csv:


```
"number","title","startDate","endDate","startStardate","endStardate","publicationDate","after","details"
1,"Numbered entry",2365,,,,,,
,"Unnumbered entry",2364-10,,,,,,
,"Child 1",,,,,,"tv/ds9/my-package-1",
,"Child 2",,,,,,"tv/ds9/my-package-UE",
```

This would become the following timeline:

* Unnumbered entry
* Child 2
* 1 Numbered entry
* Child 1

## Anthologies

Anthologies consist of multiple independent stories.
The anthology must be defined as a data file while each story is defined as a single entry in that data file.

## Making chapters/sections individual timeline entries

Sometimes a story doesn't take place in a single period of time, e.g. when time travel or flashbacks are used.
To reflect this and make single sections individual timeline entries use the following format:

```
"number","title","startDate","endDate","startStardate","endStardate","publicationDate","after","details"
,"An Easy Fast",2373-09-29,,,,YYYY-MM-DD,,"sections 1, 3, 5, 7, 9, 11, 13, 15"
--,"section 2",2324,,,,,,Gold short story
--,"sections 4, 6",2327,,,,,,Gold short story
--,"sections 8, 10",2333,,,,,,Gold short story
--,"sections 12, 14",2344,,,,,,Gold short story
```

To define that a section belongs to a story the number must be set to '--'. Corresponding sections must follow immediately after it's parent entry.

Sections inherit the publication date from it's parent entry.  

[back to index](../README.md)
