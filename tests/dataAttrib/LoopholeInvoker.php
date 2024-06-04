<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory\dataAttrib;

class LoopholeInvoker
{
    private LoopholeFactory $loopholeFactory;

    public function __construct()
    {
        $this->loopholeFactory = new LoopholeFactory();
    }

    public function expectedFailingLoophole(): object
    {
        $loophole = $this->loopholeFactory->variableUninferable(true);

        return $loophole;
    }

    public function expectedMissingClass()
    {
        return $this->loopholeFactory->variableUninferable(false);
    }
}
