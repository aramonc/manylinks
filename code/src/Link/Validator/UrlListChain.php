<?php

namespace Arc\ManyLinks\Link\Validator;

use Particle\Validator\Chain;

class UrlListChain extends Chain
{
    public function urlList(): Chain
    {
        return $this->addRule(new UrlListRule());
    }
}