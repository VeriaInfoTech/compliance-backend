<?php

namespace Content\Validator;

use Laminas\Validator\AbstractValidator;
use function array_merge;
use function in_array;

class TypeValidator extends AbstractValidator
{
    /** @var string */
    const INVALID = 'typeInvalid';

    /** @var array */
    protected array $messageTemplates = [];

    /** @var array */
    protected array $typeList
        = [
            'page', 'blog', 'article', 'product', 'video', 'category', 'location', 'business','cart','order','tour',
        ];

    /** @var array */
    protected $options
        = [];

    /**
     * {@inheritDoc}
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->messageTemplates = [
            self::INVALID => 'Invalid content type!',
        ];

        parent::__construct($options);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        $this->setValue($value);

        if (!in_array($value, $this->typeList)) {
            $this->error(static::INVALID);
            return false;
        }

        return true;
    }
}