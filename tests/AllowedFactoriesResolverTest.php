<?php

declare(strict_types=1);

namespace Test\Ebln\PHPStan\EnforceFactory;

use Ebln\PHPStan\EnforceFactory\AllowedFactoriesResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\A;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\B;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\C;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\D;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\M;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\P;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\Real;
use Test\Ebln\PHPStan\EnforceFactory\Fixtures\Resolver\U;

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

    public function testAttributeHolderIsTheNearestMarkedClass(): void
    {
        $resolver = $this->resolver();

        self::assertSame(C::class, $resolver->attributeHolder($this->class(D::class))?->getName());
        self::assertSame(C::class, $resolver->attributeHolder($this->class(C::class))?->getName());
        self::assertSame(P::class, $resolver->attributeHolder($this->class(P::class))?->getName());
        self::assertNull($resolver->attributeHolder($this->class(U::class)));
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
            ['\TEST\ebln\phpstan\enforcefactory\fixtures\resolver\real', 'Nope\Missing', Real::class],
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
