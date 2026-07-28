<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Config;

use Phinx\Config\Config;

final class PhinxConfig extends Config
{
    public function setEnvironmentPassword(string $environment, string $password): void
    {
        if (!isset($this->values['environments'][$environment])
            || !\is_array($this->values['environments'][$environment])
        ) {
            throw new \InvalidArgumentException(\sprintf('The Phinx environment "%s" does not exist.', $environment));
        }

        $this->values['environments'][$environment]['pass'] = $password;
    }
}
