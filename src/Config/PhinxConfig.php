<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Config;

use Phinx\Config\Config;

final class PhinxConfig extends Config
{
    /** @param array<string, mixed> $configArray */
    public function __construct(
        array $configArray,
        ?string $configFilePath = null,
        private readonly ?string $symfonyEnvironment = null,
    ) {
        parent::__construct($configArray, $configFilePath);
    }

    /** @return array<string, array<string, mixed>>|null */
    public function getEnvironments(): ?array
    {
        $environments = parent::getEnvironments();

        // Keep the connection under its legacy key, separate from Phinx's reserved keys.
        if (null !== $this->symfonyEnvironment && isset($environments['default'])) {
            $environments[$this->symfonyEnvironment] = $environments['default'];
        }

        return $environments;
    }

    public function setEnvironmentPassword(string $environment, string $password): void
    {
        if ($environment === $this->symfonyEnvironment) {
            $environment = 'default';
        }

        if (!isset($this->values['environments'][$environment])
            || !\is_array($this->values['environments'][$environment])
        ) {
            throw new \InvalidArgumentException(\sprintf('The Phinx environment "%s" does not exist.', $environment));
        }

        $this->values['environments'][$environment]['pass'] = $password;
    }
}
