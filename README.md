# EasterEggs
An easy way to add nice looking eggs to your server!

[![discord](https://img.shields.io/discord/692324167281934386?color=informational&label=discord)](https://discord.gg/2pADFQW)
[![downloads](https://poggit.pmmp.io/shield.dl.total/EasterEggs)](https://poggit.pmmp.io/p/EasterEggs)

# Config
**IMPORTANT:** If you use an older version of this plugin, make sure to update the config to get all new features!
```yaml
# if you want your players to find eggs, set this to true
egg finding: true

# how often the player can "find" the egg
egg findings: 1

# the action when a player finds an egg
# only gets executed when egg finding is enabled
# available actions are:
#   award (sends the player a message)
#   command (executes a command)
# allowed placeholders are:
#   {PLAYER} => player's name
action:
  - award:
      message: "You've just found an egg!"
  - command:
      command: "say {PLAYER} just found an egg!"
      execute as player: true
```

# Commands
|Command|SubCommand|Description|Permission|Alias|
|:---:|:---:|:---:|:---:|:---:|
|eastereggs|-|Manage your easter eggs!|eastereggs.command|egg|
| |spawn [type: string]|Spawn an egg|-|add|
| |remove [all]|Remove an egg/s|-|delete|

# Eggs
For now, a rainbow egg is the only one, Sorry!  

**BUT: If you know how BlockBench works, you can create your own textures and save them in _plugin_data/EasterEggs/egg.TYPE.png_**