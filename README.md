# router
Modernise the routing in your legacy projects.

**PHP 8-style routing for PHP 4+ style routing** - Write routes today, upgrade to modern tomorrow.

This library provides a modern routing API that works on PHP 4 and above. When you're ready to upgrade to PHP 8.1+, use the included migration tool to automatically convert your routes to routing files (like laravel or symfony has)

## Why Use This Package?

- **Future-proof**: API modern routing standards and practices
- **Zero-friction migration**: Automated upgrade tool converts to routing files.
- **IDE-friendly**: CLI tool auto-generates PHPDoc annotations for full autocompletion
- **Flexible**: Router Based Files, File based routing supported.
- **Well-tested**: 100% code coverage across PHP 4 - 8.5

## Projects with file based routing

- TODO: take the router from [teensyphp](https://github.com/daniel-samson/teensyphp)
- TODO: add a fileRouter function to file based routing fileRouter gets $_SERVER["DOCUMENT_ROOT"], to work out the differences if __FILE__ to get the url path.
- TODO: add class wrappers for autoloader workflow
- TODO: Write a guide blog
  - explain how old php application use .htaccess to achieve file based routing /path/to/file or ./path -> /path/index.php
  - explain the how mixture of http verbs in the same route file can make things hard to maintain
  - how to use our routing package to clean up the issue.
  - how to use our routing package to migrate to towards router files (like laravel or symphony).
  - how to move towards automatically using the routing packages console commands.
