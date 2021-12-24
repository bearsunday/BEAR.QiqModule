# BEAR.QiqModule

[QiQ template](https://qiqphp.com/) for BEAR.Sunday

## Installation

### Composer install

    composer require bear/qiq-module

### Module install

```php

use BEAR\QiqModule\QiqModule;

protected function configure(): void
{
    $this->install(new QiqModule($templateDir);
}
```
