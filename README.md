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

Please note that windows `getPids()` is slow (2 seconds). There may be a way to speed it up using `WMI` in the future.

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

    // ... your code

    // at the end of your program
    $running->releasePidFile();

```

For more detailed usage, see the [Forever Example](examples/forever).

## Support Me

Hi, I'm Finlay Beaton ([@ofbeaton](https://github.com/ofbeaton)). This software is made possible by donations of fellow users like you, encouraging me to toil the midnight hours away and sweat into the code and documentation. 

Everyone's time should be valuable, please consider donating.

[Donate through Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=RDWQCGL5UD6DS&lc=CA&item_name=ofbeaton&item_number=commandrunning&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted)

## License

This software is distributed under the MIT License. Please see [License file](LICENSE) for more information.
