<?php

namespace Content\Validator;

use Laminas\Validator\AbstractValidator;
use function array_merge;
use function preg_match;

class SlugValidator extends AbstractValidator
{
    /** @var string */
    const INVALID = 'slugInvalid';

    /** @var string */
    const TAKEN = 'slugTaken';

    /** @var array */
    protected array $messageTemplates = [];

    /** @var array */
    protected array $formatPattern
        = [
            'strict'       => '/[^a-zA-Z0-9\_\-]/',
            'strict-space' => '/[^a-zA-Z0-9\_\-\s]/',
            'medium'       => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/',
            'medium-space' => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"\s]/',
            'loose'        => '/[\000-\040]/',
            'loose-space'  => '/[\000-\040][\s]/',
        ];

    /** @var array */
    protected $options
        = [
            'format'            => 'strict',
            'check_duplication' => false,
        ];

    /**
     * {@inheritDoc}
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->messageTemplates = [
            self::INVALID => 'Invalid slug format !',
            self::TAKEN   => 'Slug is already taken !',
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

        $format = empty($this->options['format']) ? 'strict' : $this->options['format'];
        if (preg_match($this->formatPattern[$format], $value)) {
            $this->error(static::INVALID);
            return false;
        }

        if ($this->options['check_duplication']) {
            // ToDo: finish it
            $isDuplicated = 1;
            if ($isDuplicated) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}