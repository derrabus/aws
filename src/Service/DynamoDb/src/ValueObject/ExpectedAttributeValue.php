<?php

namespace AsyncAws\DynamoDb\ValueObject;

use AsyncAws\Core\Exception\InvalidArgument;
use AsyncAws\DynamoDb\Enum\ComparisonOperator;

/**
 * Represents a condition to be compared with an attribute value. This condition can be used with `DeleteItem`,
 * `PutItem`, or `UpdateItem` operations; if the comparison evaluates to true, the operation succeeds; if not, the
 * operation fails. You can use `ExpectedAttributeValue` in one of two different ways:.
 *
 * - Use `AttributeValueList` to specify one or more values to compare against an attribute. Use `ComparisonOperator` to
 *   specify how you want to perform the comparison. If the comparison evaluates to true, then the conditional operation
 *   succeeds.
 * - Use `Value` to specify a value that DynamoDB will compare against an attribute. If the values match, then
 *   `ExpectedAttributeValue` evaluates to true and the conditional operation succeeds. Optionally, you can also set
 *   `Exists` to false, indicating that you *do not* expect to find the attribute value in the table. In this case, the
 *   conditional operation succeeds only if the comparison evaluates to false.
 *
 * `Value` and `Exists` are incompatible with `AttributeValueList` and `ComparisonOperator`. Note that if you use both
 * sets of parameters at once, DynamoDB will return a `ValidationException` exception.
 */
final class ExpectedAttributeValue
{
    /**
     * Represents the data for the expected attribute.
     */
    private $value;

    /**
     * Causes DynamoDB to evaluate the value before attempting a conditional operation:.
     */
    private $exists;

    /**
     * A comparator for evaluating attributes in the `AttributeValueList`. For example, equals, greater than, less than,
     * etc.
     */
    private $comparisonOperator;

    /**
     * One or more values to evaluate against the supplied attribute. The number of values in the list depends on the
     * `ComparisonOperator` being used.
     */
    private $attributeValueList;

    /**
     * @param array{
     *   Value?: null|AttributeValue|array,
     *   Exists?: null|bool,
     *   ComparisonOperator?: null|ComparisonOperator::*,
     *   AttributeValueList?: null|AttributeValue[],
     * } $input
     */
    public function __construct(array $input)
    {
        $this->value = isset($input['Value']) ? AttributeValue::create($input['Value']) : null;
        $this->exists = $input['Exists'] ?? null;
        $this->comparisonOperator = $input['ComparisonOperator'] ?? null;
        $this->attributeValueList = isset($input['AttributeValueList']) ? array_map([AttributeValue::class, 'create'], $input['AttributeValueList']) : null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    /**
     * @return AttributeValue[]
     */
    public function getAttributeValueList(): array
    {
        return $this->attributeValueList ?? [];
    }

    /**
     * @return ComparisonOperator::*|null
     */
    public function getComparisonOperator(): ?string
    {
        return $this->comparisonOperator;
    }

    public function getExists(): ?bool
    {
        return $this->exists;
    }

    public function getValue(): ?AttributeValue
    {
        return $this->value;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null !== $v = $this->value) {
            $payload['Value'] = $v->requestBody();
        }
        if (null !== $v = $this->exists) {
            $payload['Exists'] = (bool) $v;
        }
        if (null !== $v = $this->comparisonOperator) {
            if (!ComparisonOperator::exists($v)) {
                throw new InvalidArgument(sprintf('Invalid parameter "ComparisonOperator" for "%s". The value "%s" is not a valid "ComparisonOperator".', __CLASS__, $v));
            }
            $payload['ComparisonOperator'] = $v;
        }
        if (null !== $v = $this->attributeValueList) {
            $index = -1;
            $payload['AttributeValueList'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['AttributeValueList'][$index] = $listValue->requestBody();
            }
        }

        return $payload;
    }
}
