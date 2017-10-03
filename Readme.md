# How To Use

## Requirement

1. Install `PHP`, with 5.6 or newer version

1. Install `xcode command line tool`, you should already have it if you have setup your iOS development environment correctly.

1. Install `fir-cli`, see https://github.com/FIRHQ/fir-cli

## Config

`cd config`

`cp config.env.example config.env`

Then edit the `config.env`

Also notice that you need to edit `config/AdHoc_exportOptions.plist` and `config/AppStore_exportOptions.plist`.

Normally you will only need to put your `teamID` into the file.

See http://www.matrixprojects.net/p/xcodebuild-export-options-plist/ for details.

## Execute Command

`php application.php`

You can grant `execute` permission to `application.php`, and run it directly if you like.

You can even soft link `application.php` to your `PATH` to make a shortcut command.

## The Build Flow

1. Clean the project

1. Archive the project, archive file is `tmp/{$scheme}.xcarchive`

1. Export AppStore .ipa File, the exported file is `tmp/{$scheme}_AppStore/{$scheme}.ipa`

1. Export AdHoc .ipa File, the exported file is `tmp/{$scheme}_AdHoc/{$scheme}.ipa`

1. Upload To TestFlight

1. Upload To FIR