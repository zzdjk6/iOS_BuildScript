<?php

namespace commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildBetaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build-ios-beta')
            ->setDescription('Build and publish a new iOS beta version to TestFlight and FIR')
            ->addOption('changelog', 'c', InputOption::VALUE_REQUIRED, 'The changelog for this beta build', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            $this->getDescription(),
            '=====',
            '',
        ]);

        $changelog = trim($input->getOption('changelog'));
        if (!$changelog) {
            $output->writeln('<error>Changelog is required</error>');
            exit(0);
        }

        // Default Path
        $xcode_build_bin_path = BASE_PATH . '/bin/xcbuild-safe.sh';
        $tmp_path             = BASE_PATH . '/tmp';
        $config_path          = BASE_PATH . '/config';

        $project_path = getenv('PROJECT_PATH');
        if (!$project_path || !file_exists($project_path) || !is_dir($project_path)) {
            $output->writeln('<error>PROJECT_PATH is not valid</error>');
            exit(0);
        }

        $workspace = getenv('WORKSPACE');
        if (!$workspace || !file_exists("{$project_path}/{$workspace}") || !is_file("{$project_path}/{$workspace}")) {
            $output->writeln('<error>WORKSPACE is not valid</error>');
            exit(0);
        }

        $scheme = getenv('SCHEME');
        if (!$scheme) {
            $output->writeln('<error>SCHEME is not valid</error>');
            exit(0);
        }

        $altool_path = getenv('ALTOOL_PATH');
        if (!$altool_path) {
            $output->writeln('<error>ALTOOL_PATH is not valid</error>');
            exit(0);
        }

        $appstore_username = getenv('APPSTORE_USERNAME');
        $appstore_password = getenv('APPSTORE_PASSWORD');
        if (!$appstore_username || !$appstore_password) {
            $output->writeln('<error>APPSTORE_USERNAME and/or APPSTORE_PASSWORD is not valid</error>');
            exit(0);
        }

        $fir_token = getenv('FIR_TOKEN');
        if (!$fir_token) {
            $output->writeln('<error>FIR_TOKEN is not valid</error>');
            exit(0);
        }

        $commands = [];

        // Change Working Directory
        $commands[] = <<<CMD
cd {$project_path}
CMD;

        // Clean Build
        $commands[] = <<<CMD
{$xcode_build_bin_path} \
clean -workspace {$workspace} -scheme {$scheme} -configuration Release
CMD;

        // Archive Build
        $commands[] = <<<CMD
{$xcode_build_bin_path} \
archive -workspace {$workspace} -scheme {$scheme} -archivePath {$tmp_path}/{$scheme}.xcarchive
CMD;

        // Export AppStore .ipa File
        $commands[] = <<<CMD
{$xcode_build_bin_path} \
-exportArchive \
-archivePath {$tmp_path}/{$scheme}.xcarchive \
-exportPath {$tmp_path}/{$scheme}_AppStore \
-exportOptionsPlist {$config_path}/AppStore_exportOptions.plist
CMD;

        // Export AdHoc .ipa File
        $commands[] = <<<CMD
{$xcode_build_bin_path} \
-exportArchive \
-archivePath {$tmp_path}/{$scheme}.xcarchive \
-exportPath {$tmp_path}/{$scheme}_AdHoc \
-exportOptionsPlist {$project_path}/AdHoc_exportOptions.plist
CMD;

        // Upload To TestFlight
        $commands[] = <<<CMD
{$altool_path} \
--upload-app \
-f {$tmp_path}/{$scheme}_AppStore/{$scheme}.ipa \
-u {$appstore_username} \
-p {$appstore_password}
CMD;

        // Upload To FIR
        $commands[] = <<<CMD
fir publish {$tmp_path}/{$scheme}_AdHoc/{$scheme}.ipa \
--token {$fir_token} \
--changelog '{$changelog}'
CMD;

        // Execute Commands
        $command = join("\n\n", $commands);
        system($command);
    }
}