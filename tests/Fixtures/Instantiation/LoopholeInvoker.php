<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\Fixtures\Instantiation;

class LoopholeInvoker
{
    private LoopholeFactory $loopholeFactory;

    public function __construct()
    {
        $this->loopholeFactory = new LoopholeFactory();
    }

    public function invokeBoth(): object
    {
        $this->loopholeFactory->unknownClassInUnion(false);

        return $this->loopholeFactory->stringInUnion(true, 'anything');
    }
}
