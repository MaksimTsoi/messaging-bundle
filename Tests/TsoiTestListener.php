<?php

namespace Tsoi\EventBusBundle\Tests;

use PHPUnit\Framework\BaseTestListener;

if (class_exists('Symfony\Bridge\PhpUnit\SymfonyTestsListener')) {
    class_alias('Symfony\Bridge\PhpUnit\SymfonyTestsListener', 'Tsoi\EventBusBundle\Tests\TsoiTestListener');
} else {
    class TsoiTestListener extends BaseTestListener
    {
    }
}