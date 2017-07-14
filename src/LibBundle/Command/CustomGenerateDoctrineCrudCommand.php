<?php

namespace LibBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LibBundle\Generator\CustomDoctrineCrudGenerator;
use LibBundle\Generator\CustomDoctrineGridGenerator;
use LibBundle\Generator\CustomDoctrineFormGenerator;

class CustomGenerateDoctrineCrudCommand extends GenerateDoctrineCrudCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('custom:doctrine:generate:crud');
        $this->setAliases(array('custom:generate:doctrine:crud'));
        $this->setDescription('Generates a custom CRUD based on a Doctrine entity');
    }

    protected function createGenerator($bundle = null)
    {
        return new CustomDoctrineCrudGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->getParameter('kernel.root_dir')
        );
    }

    protected function getGridGenerator($bundle = null)
    {
        $gridGenerator = new CustomDoctrineGridGenerator($this->getContainer()->get('filesystem'));
        $gridGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));

        return $gridGenerator;
    }

    protected function getFormGenerator($bundle = null)
    {
        $formGenerator = new CustomDoctrineFormGenerator($this->getContainer()->get('filesystem'));
        $formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));

        return $formGenerator;
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: str_replace('\\', '/', $entity);
        $prefix = trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', '_'), $prefix)), '_');
        $prefix = str_replace('/_', '/', $prefix);

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');

        $questionHelper->writeSection($output, 'CRUD generation');

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. Create it with the "doctrine:generate:entity" command and then execute this command again.', $entity, $bundle));
        }

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        $this->getGridGenerator($bundle)->generate($bundle, $entity, $metadata[0], $forceOverwrite);
        $output->writeln('Generating the Grid code: <info>OK</info>');

        // form
        if ($withWrite) {
            $this->generateForm($bundle, $entity, $metadata, $forceOverwrite);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        // routing
        $output->write('Updating the routing: ');
        if ('annotation' != $format) {
            $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format, $entity, $prefix));
        } else {
            $runner($this->updateAnnotationRouting($bundle, $entity, $prefix));
        }

        $questionHelper->writeGeneratorSummary($output, $errors);
    }
}
