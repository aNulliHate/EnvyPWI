<?php
/**
 * Created by PhpStorm.
 * User: Clint Dave Luna
 * Date: 11/09/2019
 * Time: 8:33 PM
 */

namespace anullihate\EnvyPWI\listeners;

use anullihate\EnvyPWI\Main;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class EventListener implements Listener {
    /** @var Main */
    private $plugin;
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    /**
     * @return PerWorldInventory
     */
    public function getPlugin(): Main {
        return $this->plugin;
    }
    /**
     * @param EntityLevelChangeEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onLevelChange(EntityLevelChangeEvent $event) : void {
        $player = $event->getEntity();
        if(!($player instanceof Player) or $player->isCreative()) {
            return;
        }
        $origin = $event->getOrigin();
        $target = $event->getTarget();
        $this->getPlugin()->storeInventory($player, $origin);
        if($player->hasPermission("per-world-inventory.bypass")) {
            return;
        }
        if($this->getPlugin()->getParentWorld($origin->getFolderName()) === $this->getPlugin()->getParentWorld($target->getFolderName())) {
            return;
        }
        $this->getPlugin()->setInventory($player, $target);
    }
    /**
     * @param PlayerQuitEvent $event
     *
     * @priority MONITOR
     */
    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        $this->getPlugin()->save($player, true);
    }
    /**
     * @param PlayerLoginEvent $event
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onPlayerLogin(PlayerLoginEvent $event) : void {
        $player = $event->getPlayer();
        if($player->isCreative() or $player->hasPermission("per-world-inventory.bypass")) {
            return;
        }
        $this->getPlugin()->load($player);
    }
    /**
     * @param InventoryTransactionEvent $event
     *
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
        if($this->getPlugin()->isLoading($event->getTransaction()->getSource())) {
            $event->setCancelled();
        }
    }
}