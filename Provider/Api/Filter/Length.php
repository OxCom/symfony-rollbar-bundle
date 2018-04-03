<?php

namespace SymfonyRollbarBundle\Provider\Api\Filter;

class Length
{
    /**
     * @var int
     */
    protected $max;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $max = empty($options['max']) ? 255 : $options['max'];
        $this->max = (int)$max;
    }

    /**
     * Check and truncate string if required
     *
     * @param string $str
     *
     * @return string
     */
    public function __invoke($str = '')
    {
        if (empty($str)) {
            return $str;
        }

        $length = mb_strlen($str);

        if ($length > $this->max) {
            // every part of string is important so no '...' in the end
            $str = trim($str);
            $str = mb_substr($str, 0, $this->max);
        }

        return $str;
    }
}
