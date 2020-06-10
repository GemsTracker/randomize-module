# Stratified block randomzation module for GemsTracker

This module adds block randomization to GemsTracker if you add it to the project composer file and register it in your projects escort.

## Installation
1. Add to composer.json of project, including adding the repository
2. composer update
3. Register your module in your Projects Escort by adding the following static property:
```PHP
    public static $modules = [
        'RandomizerModule' => \GemsRandomizer\ModuleSettings::class,
    ];
```
4. Your Project should now have a menu Item "Sample Module Test", which is added from this Module and refers to the controller in controller/ModuleTestController
