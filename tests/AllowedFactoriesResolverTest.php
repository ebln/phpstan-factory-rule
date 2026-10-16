<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\AllowedFactoriesResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\A;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\B;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\C;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\D;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\M;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\P;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\Real;
use Test\Ebln\PHPStan\EnforceFactory\dataAttrib\resolver\U;

#[CoversClass(AllowedFactoriesResolver::class)]
final class AllowedFactoriesResolverTest extends PHPStanTestCase
{
    public function testNearestAttributeWins(): void
    {
        $resolver = $this->resolver();

        self::assertSame([B::class], $resolver->allowedFactories($this->class(D::class)));
        self::assertSame([B::class], $resolver->allowedFactories($this->class(C::class)));
        self::assertSame([A::class], $resolver->allowedFactories($this->class(P::class)));
    }

    public function testUnmarkedChainIsNull(): void
    {
        $resolver = $this->resolver();

        self::assertNull($resolver->allowedFactories($this->class(U::class)));
        self::assertNull($resolver->declaredFactories($this->class(D::class)));
    }

    public function testDeclaredKeepsWhatWasWritten(): void
    {
        self::assertSame(
            ['\TEST\ebln\phpstan\enforcefactory\dataattrib\resolver\real', 'Nope\Missing', Real::class],
            $this->resolver()->declaredFactories($this->class(M::class)),
        );
    }

    public function testAllowedCanonicalisesAndDropsUnknown(): void
    {
        self::assertSame([Real::class], $this->resolver()->allowedFactories($this->class(M::class)));
    }

    private function resolver(): AllowedFactoriesResolver
    {
        return new AllowedFactoriesResolver(self::createReflectionProvider());
    }

    /** @param class-string $name */
    private function class(string $name): ClassReflection
    {
        return self::createReflectionProvider()->getClass($name);
    }
}
