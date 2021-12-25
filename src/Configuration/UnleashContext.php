<?php

namespace Unleash\Client\Configuration;

use Unleash\Client\Enum\ContextField;
use Unleash\Client\Enum\Stickiness;
use Unleash\Client\Exception\InvalidValueException;

final class UnleashContext implements Context
{
    /**
     * @param array<string,string> $customContext
     */
    public function __construct(
        private ?string $currentUserId = null,
        private ?string $ipAddress = null,
        private ?string $sessionId = null,
        private array $customContext = [],
        ?string $hostname = null,
        private ?string $environment = null,
    ) {
        $this->setHostname($hostname);
    }

    public function getCurrentUserId(): ?string
    {
        return $this->currentUserId;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId ?? (session_id() ?: null);
    }

    public function getCustomProperty(string $name): string
    {
        if (!array_key_exists($name, $this->customContext)) {
            throw new InvalidValueException("The custom context value '{$name}' does not exist");
        }

        return $this->customContext[$name];
    }

    public function setCustomProperty(string $name, string $value): self
    {
        $this->customContext[$name] = $value;

        return $this;
    }

    public function hasCustomProperty(string $name): bool
    {
        return array_key_exists($name, $this->customContext);
    }

    public function removeCustomProperty(string $name, bool $silent = true): self
    {
        if (!$this->hasCustomProperty($name) && !$silent) {
            throw new InvalidValueException("The custom context value '{$name}' does not exist");
        }

        unset($this->customContext[$name]);

        return $this;
    }

    public function setCurrentUserId(?string $currentUserId): self
    {
        $this->currentUserId = $currentUserId;

        return $this;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function setEnvironment(?string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->findContextValue(ContextField::HOSTNAME) ?? (gethostname() ?: null);
    }

    public function setHostname(?string $hostname): self
    {
        if ($hostname === null) {
            $this->removeCustomProperty(ContextField::HOSTNAME);
        } else {
            $this->setCustomProperty(ContextField::HOSTNAME, $hostname);
        }

        return $this;
    }

    /**
     * @param array<string> $values
     */
    public function hasMatchingFieldValue(string $fieldName, array $values): bool
    {
        $fieldValue = $this->findContextValue($fieldName);
        if ($fieldValue === null) {
            return false;
        }

        return in_array($fieldValue, $values, true);
    }

    public function findContextValue(string $fieldName): ?string
    {
        return match ($fieldName) {
            ContextField::USER_ID, Stickiness::USER_ID => $this->getCurrentUserId(),
            ContextField::SESSION_ID, Stickiness::SESSION_ID => $this->getSessionId(),
            ContextField::IP_ADDRESS => $this->getIpAddress(),
            ContextField::ENVIRONMENT => $this->getEnvironment(),
            default => $this->customContext[$fieldName] ?? null,
        };
    }
}
