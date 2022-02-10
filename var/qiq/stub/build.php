#!/usr/bin/env php
<?php

use BEAR\QiqModule\StubGen;

require dirname(__DIR__, 3) . '/vendor/autoload.php';
require __DIR__ . '/Qiq.php';
(new StubGen())(Qiq::class, dirname(__DIR__) . '/Helper');
