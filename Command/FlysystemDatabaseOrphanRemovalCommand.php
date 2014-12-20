<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 23:27
 */
namespace Nemo64\DatabaseFlysystemBundle\Command;

use League\Flysystem\File;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlysystemDatabaseOrphanRemovalCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('flysystem:database:orphan:removal');
        $this->setDescription("Checks if there are orphans which need to be removed from the database.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystemManager = $this->getContainer()->get('database_flysystem.filesystem_manager');

        $filesystems = $filesystemManager->getAllOrphanRemovalFilesystems();
        foreach ($filesystems as $name => $filesystem) {

            $output->write("Gather files from '$name' filesystem.");
            $this->executeForFilesystem($name, $filesystem, $input, $output);
        }
    }

    protected function executeForFilesystem($name, FilesystemInterface $filesystem, InputInterface $input, OutputInterface $output)
    {
        $fileManager = $this->getContainer()->get('database_flysystem.file_manager');
        $files = $this->getFilesOfFilesystem($output, $filesystem);

        $numFiles = count($files);
        $output->write("\nFound <info>$numFiles</info> file(s).");
        if ($numFiles <= 0) {
            $output->writeln('');
            return;
        }

        $output->write(" Now check existence in the database... ");
        $removableFiles = $fileManager->filterRemovableFiles($files);
        $numRemovableFiles = count($removableFiles);

        $output->writeln("\nFound <info>$numRemovableFiles</info> file(s) that can be removed" . ($numRemovableFiles > 0 ? ':' : '.'));
        $this->outputFiles($output, $removableFiles);
        if ($numRemovableFiles <= 0) {
            return;
        }

        $question = "<question>remove $numRemovableFiles file(s) from '$name'? (y/n)</question>";
        if ($input->isInteractive() && !$this->getHelper('dialog')->askConfirmation($output, $question)) {
            return;
        }

        $output->write("removing");
        foreach ($removableFiles as $removableFile) {
            $removableFile->delete();
            $output->write(".");
        }
        $output->writeln(" done!");
    }

    /**
     * @param OutputInterface $output
     * @param File[] $removableFiles
     */
    protected function outputFiles(OutputInterface $output, $removableFiles)
    {
        foreach ($removableFiles as $removableFile) {
            $path = $removableFile->getPath();
            $size = $removableFile->getSize();
            $output->writeln(" - <info>$path</info> ($size byte(s))");
        }
    }

    /**
     * @param OutputInterface $output
     * @param FilesystemInterface $filesystem
     * @return \League\Flysystem\File[]
     */
    private function getFilesOfFilesystem(OutputInterface $output, FilesystemInterface $filesystem)
    {
        $files = array();

        foreach ($filesystem->listPaths('', true) as $path) {
            $file = $filesystem->get($path);

            if (!$file instanceof File) {
                continue;
            }

            $files[] = $file;
            $output->write(".");
        }

        return $files;
    }
}