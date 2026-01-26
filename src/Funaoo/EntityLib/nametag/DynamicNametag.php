<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Funaoo\EntityLib\entity\BaseEntity;
use pocketmine\Server;
use Closure;

/**
 * DynamicNametag - Nametags with variables that update automatically
 *
 * Supports variables like {player_count}, {time}, {custom}, etc.
 */
class DynamicNametag {

    private string $template;
    private array $customVariables = [];
    private ?Closure $customCallback = null;

    /**
     * Create a dynamic nametag
     *
     * @param string $template Template with variables like {player_count}
     */
    public function __construct(string $template) {
        $this->template = $template;
    }

    /**
     * Get the current text with variables replaced
     */
    public function getText(BaseEntity $entity): string {
        $text = $this->template;

        // Replace built-in variables
        $text = str_replace('{player_count}', (string)count(Server::getInstance()->getOnlinePlayers()), $text);
        $text = str_replace('{max_players}', (string)Server::getInstance()->getMaxPlayers(), $text);
        $text = str_replace('{time}', date('H:i:s'), $text);
        $text = str_replace('{date}', date('Y-m-d'), $text);
        $text = str_replace('{entity_id}', (string)$entity->getId(), $text);
        $text = str_replace('{world}', $entity->getWorld()->getFolderName(), $text);

        // Replace custom variables
        foreach ($this->customVariables as $key => $value) {
            $text = str_replace('{' . $key . '}', (string)$value, $text);
        }

        // Execute custom callback if set
        if ($this->customCallback !== null) {
            $callback = $this->customCallback;
            $text = $callback($text, $entity);
        }

        return $text;
    }

    /**
     * Set a custom variable
     */
    public function setVariable(string $key, mixed $value): void {
        $this->customVariables[$key] = $value;
    }

    /**
     * Set multiple custom variables
     */
    public function setVariables(array $variables): void {
        $this->customVariables = array_merge($this->customVariables, $variables);
    }

    /**
     * Get a custom variable
     */
    public function getVariable(string $key): mixed {
        return $this->customVariables[$key] ?? null;
    }

    /**
     * Set a custom callback for advanced text processing
     */
    public function setCallback(Closure $callback): void {
        $this->customCallback = $callback;
    }

    /**
     * Get the template
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * Set a new template
     */
    public function setTemplate(string $template): void {
        $this->template = $template;
    }
}