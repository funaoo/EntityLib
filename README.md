# EntityLib

> A powerful and easy-to-use library for creating interactive NPCs and entities in PocketMine-MP 5.39.x

EntityLib makes creating NPCs, floating text, and interactive entities super simple. If you've used InvMenu, you'll feel right at home - the API is designed to be just as clean and intuitive.

## ✨ Features

- 🎭 **Multiple Entity Types**: Humans, Animals (pig, cow, sheep, chicken), Mobs (zombie, skeleton, creeper), Villagers, and Floating Text
- 🎨 **Particle Effects**: 15+ particle types with customizable patterns (circle, spiral, rain, fountain)
- 🖼️ **Custom Skins**: Load skins from PNG files or URLs
- 💾 **Persistent Storage**: Entities survive server restarts
- 🎯 **Easy Interactions**: Simple callback system for clicks
- 🔄 **Look at Players**: NPCs can automatically track nearby players
- ⚡ **Optimized**: Handles 500+ entities without lag
- 🛠️ **Builder Pattern**: Clean, fluent API

## 📦 Installation

### As a Library

Download and place the `src/Funaoo/EntityLib` folder directly into your plugin's `src` directory.

## 🚀 Quick Start

### Basic Setup

```php
use Funaoo\EntityLib\EntityLib;

class MyPlugin extends PluginBase {
    
    public function onEnable(): void {
        // Initialize EntityLib (required!)
        EntityLib::register($this);
    }
}
```

### Create a Simple NPC

```php
use Funaoo\EntityLib\EntityLib;

// Create a human NPC
EntityLib::create($player->getPosition(), $player->getWorld())
    ->human()
    ->setSkin($player->getSkin())
    ->setName("§l§6Shop NPC")
    ->setScale(1.2)
    ->lookAtPlayers()
    ->onInteract(function($player) {
        $player->sendMessage("§aWelcome to the shop!");
        // Open your shop menu here
    })
    ->spawn();
```

### Floating Text (Among Us Style)

```php
// Create floating text with multiple lines
EntityLib::createFloatingText(
    new Vector3(100, 65, 100),
    $world,
    "§l§dPlay Among Us\n§r§7Crewmates // Impostor\n§a8 people playing..."
);
```

### NPC with Particle Effects

```php
EntityLib::create($position, $world)
    ->human()
    ->setName("§l§eQuest Master")
    ->addParticles(ParticleEffect::ENCHANT, 20) // Enchant particles every second
    ->onInteract(function($player) {
        $player->sendMessage("§6New quest available!");
    })
    ->spawn();
```

### Animal NPCs

```php
// Create a pig NPC
EntityLib::create($position, $world)
    ->animal("pig")
    ->setName("§dOink Oink")
    ->setScale(2.0) // Make it bigger
    ->onInteract(function($player) {
        $player->sendMessage("§7*Oink!*");
    })
    ->spawn();
```

### Villager Shop

```php
EntityLib::create($position, $world)
    ->villager()
    ->setName("§aItem Shop\n§7Click to browse")
    ->lookAtPlayers(true)
    ->addParticles(ParticleEffect::HAPPY_VILLAGER, 30)
    ->onInteract(function($player) {
        // Open trading menu
        $player->sendMessage("§aOpening shop...");
    })
    ->spawn();
```

## 📚 Advanced Usage

### Custom Skins

```php
// Load skin from PNG file
$skin = EntityLib::getSkinManager()->loadSkin("myskin", "skins/custom.png");

EntityLib::create($position, $world)
    ->human()
    ->setSkin($skin)
    ->setName("§bCustom Skin NPC")
    ->spawn();
```

### Persistent Entities

```php
// Save entity to survive restarts
EntityLib::create($position, $world)
    ->human()
    ->setName("§6Permanent NPC")
    ->persistent(true) // This entity will be saved
    ->spawn();

// Auto-load saved entities on startup
EntityLib::register($this, true); // true = auto-load
```

### Multiple Particle Effects

```php
$entity = EntityLib::create($position, $world)
    ->human()
    ->setName("§l§5Magic NPC")
    ->addParticles(ParticleEffect::ENCHANT, 10)
    ->addParticles(ParticleEffect::PORTAL, 20)
    ->addParticles(ParticleEffect::HEART, 40)
    ->spawn();
```

### Custom Metadata

```php
// Store custom data with entities
EntityLib::create($position, $world)
    ->human()
    ->setName("§aShop #1")
    ->setMetadata("shop_id", 1)
    ->setMetadata("shop_type", "weapons")
    ->onInteract(function($player, $entity) {
        $shopId = $entity->getMetadata("shop_id");
        $player->sendMessage("Opening shop #{$shopId}");
    })
    ->spawn();
```

### Entity Management

```php
// Get entity by ID
$entity = EntityLib::get($entityId);

// Remove entity
EntityLib::remove($entityId);

// Remove all entities
EntityLib::removeAll();

// Save specific entity
EntityLib::save($entityId);

// Save all entities
EntityLib::saveAll();

// Get all entities
$entities = EntityLib::getAll();
```

## 🎨 Particle Types

Available particle effects:
- `ParticleEffect::HEART` - Heart particles
- `ParticleEffect::FLAME` - Fire particles
- `ParticleEffect::ENCHANT` - Enchanting table effect
- `ParticleEffect::HAPPY_VILLAGER` - Green sparkles
- `ParticleEffect::ANGRY_VILLAGER` - Red exclamation
- `ParticleEffect::CRITICAL` - Critical hit effect
- `ParticleEffect::PORTAL` - Portal particles
- `ParticleEffect::SMOKE` - Smoke
- `ParticleEffect::EXPLODE` - Explosion particles
- `ParticleEffect::DUST_RED/GREEN/BLUE/YELLOW/PURPLE` - Colored dust

### Particle Patterns

- `ParticleEffect::PATTERN_SINGLE` - One particle at entity position
- `ParticleEffect::PATTERN_CIRCLE` - Circle around entity
- `ParticleEffect::PATTERN_SPIRAL` - Spiral going up
- `ParticleEffect::PATTERN_RAIN` - Particles falling from above
- `ParticleEffect::PATTERN_FOUNTAIN` - Particles shooting up

## 🛠️ Utilities

### Get Nearby Entities

```php
use Funaoo\EntityLib\utils\EntityUtils;

$nearbyEntities = EntityUtils::getEntitiesInRadius($position, $world, 10.0);
```

### Make Entity Face Player

```php
EntityUtils::faceEntity($npcEntity, $player);
```

### Teleport with Effect

```php
EntityUtils::teleportWithEffect($entity, $newPosition, true);
```

### Check Safe Spawn

```php
if (EntityUtils::isSafeSpawnPosition($position, $world)) {
    // Safe to spawn here
}
```

## 📊 Statistics

```php
// Count entities by type
$counts = EntityUtils::countByType();
// Returns: ['human' => 5, 'villager' => 3, 'pig' => 2]

// Get entities in world
$entities = EntityUtils::getEntitiesInWorld($world);
```

## 🔧 Configuration

Entities are saved in `plugin_data/EntityLib/entities.json`

You can manually edit this file if needed. The format is:
```json
{
  "1": {
    "type": "human",
    "name": "§aShop NPC",
    "position": {
      "x": 100,
      "y": 64,
      "z": 100,
      "world": "world"
    },
    "rotation": {
      "yaw": 90,
      "pitch": 0
    },
    "scale": 1.0,
    "metadata": {}
  }
}
```

## 🐛 Troubleshooting

### Entities not spawning?

Make sure you called `EntityLib::register($this)` in your `onEnable()`.

### Entities despawn on chunk unload?

This is normal behavior. They'll respawn when the chunk loads again. If you want truly persistent entities, use `->persistent(true)`.

### Can't click entities?

Check that your interaction callback is registered and that you're not on cooldown (default 0.5 seconds between clicks).

### Skins not loading?

- Ensure PNG files are 64x64 or 64x32
- Check file path is correct (relative to plugin data folder)
- Verify PHP has GD extension enabled

## 📝 API Reference

### EntityLib

Main class for creating and managing entities.

**Methods:**
- `register(Plugin $plugin, bool $autoLoad = false): void` - Initialize library
- `create(Vector3 $position, World $world): EntityBuilder` - Create new entity
- `createFloatingText(Vector3 $position, World $world, string $text): BaseEntity` - Quick floating text
- `get(int $entityId): ?BaseEntity` - Get entity by ID
- `getAll(): array` - Get all entities
- `remove(int $entityId, bool $permanent = false): bool` - Remove entity
- `removeAll(bool $permanent = false): void` - Remove all entities
- `save(int $entityId): bool` - Save entity
- `saveAll(): void` - Save all entities

### EntityBuilder

Fluent builder for configuring entities.

**Methods:**
- `human(): self` - Set type to human
- `floatingText(): self` - Set type to floating text
- `animal(string $type): self` - Set type to animal (pig, cow, sheep, chicken)
- `mob(string $type): self` - Set type to mob (zombie, skeleton, creeper)
- `villager(): self` - Set type to villager
- `setName(string $name): self` - Set nametag
- `setSkin(Skin $skin): self` - Set custom skin
- `setScale(float $scale): self` - Set size
- `setRotation(float $yaw, float $pitch = 0): self` - Set rotation
- `lookAtPlayers(bool $enable = true): self` - Auto-rotate to face players
- `setCanCollide(bool $canCollide): self` - Set collision
- `onInteract(Closure $callback): self` - Set click callback
- `addParticles(string $type, int $interval = 20): self` - Add particle effect
- `setMetadata(string $key, mixed $value): self` - Store custom data
- `persistent(bool $save = true): self` - Save to storage
- `spawn(): BaseEntity` - Spawn the entity

## 🤝 Contributing

Found a bug? Want a feature? Open an issue on GitHub!

## 📄 License

EntityLib is licensed under the MIT License.

## 💖 Credits

Created by **Funaoo**

Inspired by:
- InvMenu by Muqsit (for the clean API design)
- Among Us (for the floating text lobby style)
- Every server owner who's tired of writing entity code from scratch

## 🔗 Links

- GitHub: https://github.com/Funaoo/EntityLib

---

Made with ❤️ for the PocketMine-MP community
