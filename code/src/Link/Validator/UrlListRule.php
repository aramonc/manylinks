<?php

namespace Arc\ManyLinks\Link\Validator;

use Particle\Validator\MessageStack;
use Particle\Validator\Rule;

class UrlListRule extends Rule
{
    /**
     * @var Rule\Url
     */
    private $urlRule;

    public function __construct()
    {
        $this->urlRule = new Rule\Url(['http', 'https']);
    }

    /**
     * This method should validate, possibly log errors, and return the result as a boolean.
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        $urls = explode("\n", str_replace("\r", '', $value));

        foreach ($urls as $url) {
            if (!$this->urlRule->validate($url)) {
                return false;
            }
        }

        return true;
    }

    public function setMessageStack(MessageStack $messageStack)
    {
        parent::setMessageStack($messageStack);
        $this->urlRule->setMessageStack($messageStack);

        return $this;
    }
}