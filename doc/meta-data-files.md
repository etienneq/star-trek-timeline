# Meta data files

Meta data files can be provided for each package.
The following naming convention must be observed. A meta data file must be called meta.ini if it belongs to a sub directory or have the same name as the corresponding CSV file (e.g. my-series.csv, my-series.ini).

A meta data file can define any of the following attributes.

```
title=Star Trek: Deep Space Nine
symbol=DS9
media=TV
```

In the end a single meta data object is created for each CSV file. To do so the attributes of it's own meta data file (if it exists) and all existing parent meta data files are merged. A child's attribute always overwrites a parent's attribute.

An example. Let's assume the following directory structure...


```
tv
--- meta.ini
--- ds9
------ meta.ini
------ my-package.csv
------ my-package.ini

```

... and the following meta data files.

```
tv/meta.init:
	media=TV

tv/ds9/meta.ini:
	title=Star Trek: Deep Space Nine
	symbol=DS9

tv/ds9/my-package.ini:
	symbol=something different
```

The resulting attributes will be:

```
	title=Star Trek: Deep Space Nine
	symbol=something different
	media=TV
```

[back to index](../README.md)
