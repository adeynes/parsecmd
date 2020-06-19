# parsecmd

### This README is outdated for 2.0 and up!
### Current stable version: 3.1.3

**parsecmd** is a powerful virion for parsing commands in PocketMine-MP. It parses argument lists and flags (much like shell flags) into a data structure that can be easily queried.

Throughout this README, our example command will be `/rawtell`, which sends a message, popup, or title to a player without the `[Steve -> Alex]` introduction. The usage is `/rawtell <player> <message> [-nomessage|-nom] [-popup|-p] [-title|-t] [-password <password>]`. By default, the command sends a chat message.<br>
The `nomessage` tag does not send the message through chat and its alias is `nom`.<br>
The `popup` tag sends a popup and its alias is `p`.<br>
The `title` tag sends a title and its alias is `t`.<br>
The `password` tag accepts a 3-argument password. The correct password is `bush baked beans`. If the given password matches, the specified player will receive a special message.

### The `PCMDCommand` class
The `PCMDCommand` class extends `pocketmine\command\Command` and reduces boilerplate code for command classes.<br>
The `execute()` method is implemented and does permission testing and verifies that at least a minimum specified amount of arguments is supplied. It should not be overridden; instead, write your code in `_execute()`.

#### `Constructor`
```php
protected function __construct(Plugin $plugin, string $name, string $permission = null, string $description = '',
                               int $min_args = 0, string $usage = null, array $tags = []);
```

**`$plugin`:** the plugin main class. Returned in `PCMDCommand::getPlugin()`.<br>
**`$name`:** the name of the command (no slash!), e.g. `rawtell`.<br>
**`$permission`:** the permission node for the command, e.g. `myplugin.command.rawtell`.<br>
**`$description`:** the command's description, e.g. `Send a raw message to a player`.<br>
**`$min_args`:** The minimum amount of arguments for the command. In our case, we want 2: the player name and a 1-word-minimum message.<br>
**`$usage`:** The usage message for the command. In our case, `/rawtell <player> <message> [-nomessage] [-popup] [-title] [-password <password>]`.<br>
**`$tags`:** An array of tags for this command, in the form `[tag name => number of arguments that make up its value]`. In our case, we want: `['nomessage' => 0, 'popup' => 0, 'title' => 0, 'password' => 3]`. Neither of the first three tags accept any arguments, and the password is 3 arguments long.

#### `getPlugin()`
This returns the `Plugin` instance passed in the constructor.
```php
public function getPlugin(): Plugin;
```
You may want to override this in your commands and add a more specific return type in the PHPDoc so that your IDE can fully work its completion magic when you call `getPlugin()`:
```php
/**
 * @return MyPlugin
 */
public function getPlugin(): Plugin
{
    return parent::getPlugin();
}
```

#### `getTags()`
```php
public function getTags(): array;
```
This returns the array of tags with the name as the key and the number of arguments as its value.

#### `getTag()`
```php
public function getTag(string $tag): ?int;
```
This returns the number of arguments in the specified tag, or `null` if it doesn't exist. e.g. for `title` it would return 0, for `password` 3, and for `beans` `null`.

#### `execute()`
```php
public function execute(CommandSender $sender, string $label, array $args): bool;
```
This is the method that PocketMine executes when it runs the command. You should not call it yourself or override it. Instead, write the code for your command in `_execute()` It contains boilerplate code like permission testing and minimum argument amount checking.

#### `_execute()`
```php
abstract public function _execute(CommandSender $sender, PCMDCommand $command): bool;
```
This method is called by `execute()` and you should implement it in your concrete command classes and write the code for your commands there.

### The `CommandParser` class
The `CommandParser` class is essentially a utils class that contains static methods to parse commands.

#### `parse()`
```php
public static function parse(PCMDCommand $command, array $args): ParsedCommand;
```
This accepts an instance of `PCMDCommand` and an array of arguments (the one passed to `PCMDCommand::execute()`) and returns an instance of ParsedCommand, with the tags removed from the argument list and placed in an array of tags.

#### `parseDuration()`
```php
public static function parseDuration(string $duration): int;
```
This accepts a duration string in the form `AyBMCwDdEhFm` and returns a UNIX timestamp with the specified duration added to the current time.

* `y`: year
* `M`: month
* `w`: week
* `d`: day
* `h`: hour
* `m`: minute

For instance, 1 year and 3 months is `1y3M` or `15M`, 1 week and 4 days is `1w4d` or `11d`, and 1 hour and 45 minutes is `1h45m` or `105m`.

### The `ParsedCommand` class
The `ParsedCommand` class allows the developer to query tags and arguments in a flexible way.

#### `getName()`
```php
public function getName(): string
```
This returns the command's name; `CommandParser::parse($command, $args)->getName()` is the same as `$command->getName()`.

#### `get()`
```php
public function get(array $queries): array;
```
This queries the argument list and returns the specified arguments. A single integer value in the queries array will retrive the argument at that value. A pair of two integers will implode the arguments starting at the index given by the first element with the second element as the length. Negative values are allowed and start at the end of the array (-1 is the last element, -2 the penultimate, etc).<br>
Given the argument list `steve hey steve you are cool`, the queries `[0, [1, -1]]` would return `['steve', 'hey steve you are cool']`.<br>
Given the argument list `my favorite beans are baked beans from bush`, the queries `[-1, [4, 2], 3, [0, 2]]` would return `['bush', 'baked beans', 'are', 'my favorite']`. `implode`-ing that would then return `bush baked beans are my favorite`.

#### `getArgs()`
```php
public function getArgs(): array;
```
This returns the argument list.

#### `getTags()`
```php
public function getTags(): array;
```
This returns the tag list.

#### `getTag()`
```php
public function getTag(string $tag): ?string;
```
This returns the value of the given tag, or `false` if it doesn't exist.

## Example
We will be implementing the `/rawtell` command, which sends a message, popup, or title to a player without the `[Steve -> Alex]` introduction. The usage is `/rawtell <player> <message> [-nomessage] [-popup] [-title] [-password <password>]`. By default, the command sends a chat message. The `nomessage` tag does not send the message through chat. The `popup` tag sends a popup. The `title` tag sends a title. The `password` tag accepts a 3-argument password. The correct password is `bush baked beans`. If the given password matches, the specified player will receive a special message.

```php
<?php
declare(strict_types=1);

namespace MyPlugin\command;

use adeynes\parsecmd\ParsedCommand;
use adeynes\parsecmd\PCMDCommand;
use MyPlugin\MyPlugin; // MyPlugin extends PluginBase
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RawtellCommand extends PCMDCommand
{
    
    protected const PASSWORD = 'bush baked beans';

    
    public function __construct(MyPlugin $plugin)
    {
        parent::__construct(
            $plugin,
            'rawtell',
            'myplugin.command.rawtell',
            'Send a raw message to a player',
            3,
            '/rawtell <player> <message> [-nomessage] [-popup] [-title] [-password <password]',
            ['nomessage' => 0, 'popup' => 0, 'title' => 0, 'password' => 3]
        );
    }
    
    public function _execute(CommandSender $sender, ParsedCommand $command): bool {
        [$target_name, $message] = $command->get([0, [1, -1]]);
        $message = TextFormat::colorize($message);
        
        $target = $this->getPlugin()->getServer()->getPlayer($target_name);
        if (!($target && $target instanceof Player && $target->isOnline())) {
            $sender->sendMessage(TextFormat::colorize("&b$target_name &cis not online!"));
            return false;
        }
        
        // We have to use is_null for the tags instead of checking for truthiness because if there are no arguments
        // to the tag it's an empty string which is falsy. Even with arguments, the value can still be falsy
        
        // If it's null it means that we do send the message (default behavior)
        if (is_null($command->getTag('nomessage'))) {
            $target->sendMessage($message);
        }
        
        if (!is_null($command->getTag('popup'))) {
            $target->sendPopup($message);
        }
        
        if (!is_null($command->getTag('title'))) {
            $target->addSubtitle($message); // title is too big, especially for phones
        }
        
        if (!is_null($password = $command->getTag('password')) && $password === self::PASSWORD) {
            $target->addTitle(TextFormat::colorize('&9BUSH &6BAKED &eBEANS'));
        }
        
        return true;
    }
}
```