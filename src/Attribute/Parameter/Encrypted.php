<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\Attribute\Parameter;

use Attribute;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolveContext;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolverInterface;
use Exception;
use RuntimeException;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeResolverInterface;
use Yiisoft\Hydrator\AttributeHandling\Exception\UnexpectedAttributeException;
use Yiisoft\Hydrator\AttributeHandling\ParameterAttributeResolveContext;
use Yiisoft\Hydrator\Result;
use Yiisoft\Security\Crypt;
use Yiisoft\Strings\StringHelper;

/**
 * Encrypts and decrypts a value
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Encrypted implements
    ParameterAttributeInterface,
    ParameterAttributeResolverInterface,
    ColumnAttributeResolverInterface
{
    private Crypt $crypt;

    /**
     * @param bool $passwordBased set true to use password-based key derivation
     * @param string $secret key in the $_ENV array containing the encryption password or key
     * @param array|string $infoPath object property containing context/application specific information
     *  Nested properties are specified using dot '.' notation,
     * e.g. user.email resolves to $context->object->user->email for encryption
     * and $context->data['user']['email'] for decryption
     *  The array format allows the object property and db column to have different names or paths;
     *  the first entry specifies the object property, the second specifies the db column name;
     *  both must resolve to the same value
     * @param string $cipher the cipher to use for encryption and decryption
     * @param ?int $iterations number of iterations for password based encryption
     * @param ?string $kdfAlgorithm Hash algorithm for key derivation
     * @param ?string $authorizationKeyInfo HKDF info value for derivation of message authentication key
     */
    public function __construct(
        private readonly bool $passwordBased,
        private readonly string $secret,
        private readonly array|string $infoPath = '',
        string $cipher = 'AES-128-CBC',
        ?int $iterations = null,
        ?string $kdfAlgorithm = null,
        ?string $authorizationKeyInfo = null
    )
    {
        $crypt = new Crypt($cipher);

        if (is_int($iterations)) {
            $crypt = $crypt->withDerivationIterations($iterations);
        }
        if (is_string($kdfAlgorithm)) {
            $crypt = $crypt->withKdfAlgorithm($kdfAlgorithm);
        }
        if (is_string($authorizationKeyInfo)) {
            $crypt = $crypt->withKdfAlgorithm($authorizationKeyInfo);
        }

        $this->crypt = $crypt;
    }

    /**
     * @throws Exception
     */
    public function getColumnValue(
        ParameterAttributeInterface $attribute,
        ColumnAttributeResolveContext $context
    ): Result
    {
        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if (is_string($resolvedValue) && !empty($resolvedValue)) {
                if ($this->passwordBased) {
                    $encrypted = $this->crypt->encryptByPassword($resolvedValue, $_ENV[$this->secret]);
                } else {
                    $encrypted = $this->crypt->encryptByKey(
                        $resolvedValue,
                        $_ENV[$this->secret],
                        $this->resolveInfo($this->infoPath, $context)
                    );
                }
                return Result::success($encrypted);
            }
        }

        return Result::fail();
    }

    /**
     * @throws Exception
     */
    public function getParameterValue(
        ParameterAttributeInterface $attribute,
        ParameterAttributeResolveContext $context
    ): Result
    {
        if (!$attribute instanceof Encrypted) {
            throw new UnexpectedAttributeException(Encrypted::class, $attribute);
        }

        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if (is_string($resolvedValue) && !empty($resolvedValue)) {
                if ($this->passwordBased) {
                    $decrypted = $this->crypt->decryptByPassword($resolvedValue, $_ENV[$this->secret]);
                } else {
                    $decrypted = $this->crypt->decryptByKey(
                        $resolvedValue,
                        $_ENV[$this->secret],
                        $this->resolveInfo($this->infoPath, $context)
                    );
                }
                return Result::success($decrypted);
            }
        }

        return Result::fail();
    }

    public function getResolver(): self
    {
        return $this;
    }

    private function resolveInfo(
        array|string $infoPath,
        ColumnAttributeResolveContext|ParameterAttributeResolveContext $context
    ): string
    {
        if (empty($infoPath)) {
            return '';
        }

        if (is_array($infoPath)) {
            if ($context instanceof ColumnAttributeResolveContext) {
                $infoPath = $infoPath[0];
            } else {
                $infoPath = $infoPath[1];
            }
        }

        if ($context instanceof ColumnAttributeResolveContext) {
            $infoPath = StringHelper::parsePath($infoPath);
            return $this->resolveObjectInfo($infoPath, $context->getObject());
        }

        return $context->getData()->getValue($infoPath)->getValue();
    }

    private function resolveObjectInfo(array $infoPath, object $object): string
    {
        $property = array_shift($infoPath);
        $properties = get_object_vars($object);

        if (array_key_exists($property, $properties)) {
            $value = $object->$property;
        } else {
            $method = "get$property";
            if (method_exists($object, $method)) {
                $value = $object->$method();
            } else {
                throw new RuntimeException('Invalid infoPath');
            }
        }

        return count($infoPath) ? $this->resolveObjectInfo($infoPath, $value) : $value;
    }
}
