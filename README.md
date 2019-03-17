# command-running
Detects if a command or process is currently running and optionally kill it.

[![Latest Stable Version](https://poser.pugx.org/ofbeaton/command-running/v/stable.png)](https://packagist.org/packages/ofbeaton/command-running)
[![Build Status](https://travis-ci.org/ofbeaton/command-running.svg?branch=master)](https://travis-ci.org/ofbeaton/command-running)
[![Dependency Status](https://www.versioneye.com/php/ofbeaton:command-running/badge.svg?style=flat)](https://www.versioneye.com/php/ofbeaton:command-running)
[![Total Downloads](https://img.shields.io/packagist/dt/ofbeaton/command-running.svg)](https://packagist.org/packages/ofbeaton/command-running)
[![License](https://poser.pugx.org/ofbeaton/command-running/license)](LICENSE)

Currently supports:
 - Linux
 - Windows

Please note that `getPids()` on windows is slow (2 seconds). There may be a way to speed it up using `WMI` in the future.

## Alternatives

If you don't need advanced features, you can use the [Symfony Lock component and trait](https://symfony.com/doc/master/console/lockable_trait.html) in any application, even non-symfony ones. It is actively maintained and is likely more up to date. It supports filesystem (FlockStore), shared memory (SemaphoreStore) and even databases and Redis servers. It does not support force claims, kills, or reports.

## Installing via Composer

The recommended way to install Command Running is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer.phar require ofbeaton/command-running
```

After installing, you can now use it in your code:

```php
    $pidfile = 'mypidfile.txt';
    $running = new \Ofbeaton\Command\Running($pidfile);

    $ok = $running->claimPidFile();
    if ($ok === false) {
        echo 'We are currently already running'.PHP_EOL;
        exit;
    }

    // your code

    // at the end of your program
    $running->releasePidFile();

```

For more detailed usage, see the [Forever Example](examples/forever).

## License

This software is distributed under the MIT License. Please see [License file](LICENSE) for more information.
