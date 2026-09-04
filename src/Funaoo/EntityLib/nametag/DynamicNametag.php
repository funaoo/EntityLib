<?php

declare(strict_types=1);

namespace Funaoo\EntityLib\nametag;

use Closure;
use pocketmine\Server;
use Funaoo\EntityLib\entity\BaseEntity;

final class DynamicNametag {

    private array    $customVars = [];
    private ?Closure $callback   = null;

    public function __construct(private string $template) {}

    public function getText(BaseEntity $entity): string {
        $server = Server::getInstance();
        $text   = strtr($this->template, [
            '{player_count}' => count($server->getOnlinePlayers()),
            '{max_players}'  => $server->getMaxPlayers(),
            '{time}'         => date('H:i:s'),
            '{date}'         => date('Y-m-d'),
            '{entity_id}'    => $entity->getId(),
            '{world}'        => $entity->getWorld()->getFolderName(),
        ]);

        foreach ($this->customVars as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }

        return $this->callback !== null ? ($this->callback)($text, $entity) : $text;
    }

    public function setVariable(string $key, mixed $value): void  { $this->customVars[$key] = $value; }
    public function setVariables(array $vars): void               { foreach ($vars as $k => $v) $this->customVars[$k] = $v; }
    public function getVariable(string $key): mixed               { return $this->customVars[$key] ?? null; }
    public function setCallback(Closure $cb): void                { $this->callback = $cb; }
    public function getTemplate(): string                         { return $this->template; }
    public function setTemplate(string $template): void           { $this->template = $template; }
}
