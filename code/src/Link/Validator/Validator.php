<?php

namespace Arc\ManyLinks\Link\Validator;

use Particle\Validator\Validator as ParticleValidator;

/**
 * @method UrlListChain required($key, $name = null, $allowEmpty = false)
 * @method UrlListChain optional($key, $name = null, $allowEmpty = true)
 */
class Validator extends ParticleValidator
{
    public function buildChain($key, $name, $required, $allowEmpty)
    {
        return new UrlListChain($key, $name, $required, $allowEmpty);
    }
}