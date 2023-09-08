# Virtual Domains

Multi Domain Support for Joomla.

A fork that supports PHP8.

Supports Joomla 4.

## Installation

Download the [latest release](https://github.com/caincoulton/virtualdomains/releases/latest) and install the package in Joomla.
*Future updates should be available via the Joomla Extension update mechanism. TODO*

## Building

To build the package, use Phing:

Just typing `phing` will build the package.

Don't forget to update the version number in the `version` file.

The resulting Joomla package can then be found in the `dist` folder.

### Building on Windows

Download phing-latest from https://www.phing.info/ and copy to virtualdomains project directory.

To build the package, run:

```
php phing-latest.phar
```

## Features & Options

### Remove menu root

Removes the root menu item from the SEF URL (Experimental)

#### Why is this needed?

When using multiple domains on a single Joomla installation, it makes logical sense to structure each sub-site around it's own menu.  The catch with this, is that having multiple root menu items with the same alias is not allowed.  

```
e.g domain1.com/about.html and domain2.com/about.html will have the same alias. 
``` 

The workaround for this is have a separator for the root menu item of each website menu, and then have the site root pages as children.  This structure will add a prefix directory to each of the site pages, but at least then it is possible to control the pages names.

```
e.g. domain1.com/dom1/about.html and domain2.com/dom2/about.html is allowed, and will have no conflicting aliases.
```

This option will strip out the top level menu item alias from the SEF url to allow the above example to show the following in the browser address bar:

```
domain1.com/about.html and domain2.com/about.html
```

### How does it work?

Build rules are attached to the SiteRouter so that after a SEF URL is built, the top level menu item alias is stripped from what's generated.  When parsing a URL, the top level menu item alias is injected back in.

The top level menu item is intended to be a Separator type menu item.

### Errors to watch out for

404 - Page not found

JROOT\libraries\src\Router\Router.php:153

The url that is being parsed is still looking for the menu root item in the URL.