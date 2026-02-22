<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;
use pocketmine\Server;
use Funaoo\EntityLib\entity\BaseEntity;

final class DynamicNametag {

    private array $customVars = [];
    private ?Closure $callback = null;

    public function __construct(private string $template) {}

    public function getText(BaseEntity $entity): string {
        $text = $this->template;

        $text = str_replace('{player_count}', (string)count(Server::getInstance()->getOnlinePlayers()), $text);
        $text = str_replace('{max_players}',  (string)Server::getInstance()->getMaxPlayers(), $text);
        $text = str_replace('{time}',         date('H:i:s'), $text);
        $text = str_replace('{date}',         date('Y-m-d'), $text);
        $text = str_replace('{entity_id}',    (string)$entity->getId(), $text);
        $text = str_replace('{world}',        $entity->getWorld()->getFolderName(), $text);

        foreach ($this->customVars as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }

        if ($this->callback !== null) {
            $text = ($this->callback)($text, $entity);
        }

        return $text;
    }

    public function setVariable(string $key, mixed $value): void {
        $this->customVars[$key] = $value;
    }

    public function setVariables(array $vars): void {
        foreach ($vars as $k => $v) {
            $this->customVars[$k] = $v;
        }
    }

    public function getVariable(string $key): mixed {
        return $this->customVars[$key] ?? null;
    }

    public function setCallback(Closure $cb): void {
        $this->callback = $cb;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function setTemplate(string $template): void {
        $this->template = $template;
    }
}
