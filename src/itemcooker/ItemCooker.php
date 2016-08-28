<?php

namespace itemcooker;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class ItemCooker extends PluginBase {
    
    private function registerPermissions() {
        $permissions = array(
            new Permission("itemcooker.command.cook", "Allows the player to cook or smelt an item.", Permission::DEFAULT_TRUE)
        );
        foreach ($permissions as $permission) {
            $this->getServer()->getPluginManager()->addPermission($permission);
        }
    }
    
    public function onLoad() {
        $this->getLogger()->info("ItemCooker is now loading...");
    }
    
    public function onEnable() {
        $this->getLogger()->info("ItemCooker is now enabled.");
        $this->registerPermissions();
    }
    
    public function onDisable() {
        $this->getLogger()->info("ItemCooker is now disabled.");
    }
    
    private function cookItemFromPlayer(Player $player) : bool {
        $item = $player->getInventory()->getItemInHand();
        if ($item == null || $item->getId() == Item::AIR) {
            return false;
        }
        $result = null;
        foreach ($this->getServer()->getCraftingManager()->getFurnaceRecipes() as $meta => $recipe) {
            if ($recipe->getInput()->equals($item)) {
                $cookedItem = $recipe->getResult();
                $id = $cookedItem->getId();
                $count = $item->getCount();
                $result = new Item($id, $meta, $count);
                break;
            }
        }
        if ($result == null) {
            return false;
        }
        $player->getInventory()->setItemInHand($result);
        return true;
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command->getName()) {
            case "cook":
                if ($sender->hasPermission("itemcooker.command.cook")) {
                    if (!($sender instanceof Player)) {
                        $sender->sendMessage(TextFormat::AQUA . "You must be a player to use this command.");
                        return false;
                    }
                    if ($this->cookItemFromPlayer($sender)) {
                        $sender->getLevel()->addSound(new AnvilFallSound($sender->getLocation()));
                        $sender->sendMessage(TextFormat::GREEN . "That item has been cooked successfully.");
                    } else {
                        $sender->sendMessage(TextFormat::GOLD . "That item cannot be cooked.");
                        return false;
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You don't have enough acces to do that.");
                }
                return true;
        }
        return false;
    }
}