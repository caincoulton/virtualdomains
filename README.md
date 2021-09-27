# Virtual Domains
Multi Domain Support for Joomla.

A fork that supports PHP 7.1 and later.

Supports Joomla 3 and Joomla 4.

## Installation

Download the [latest release](https://github.com/smehrbrodt/virtualdomains/releases/latest) and install the package in Joomla.
Future updates should be available via the Joomla Extension update mechanism.

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