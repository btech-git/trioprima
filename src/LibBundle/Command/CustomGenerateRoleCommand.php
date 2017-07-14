<?php

namespace LibBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

class CustomGenerateRoleCommand extends ContainerAwareCommand
{
    private $entityClass = '';
    private $entityProperties = array();
    private $em = null;
    private $newReferenceRoles = array();
    private $newUserRoles = array();
    private $oldReferenceRoles = array();
    private $oldUserRoles = array();
    private $count = 0;
    
    protected function configure()
    {
        parent::configure();

        $this->setName('custom:role:generate');
        $this->setAliases(array('custom:generate:role'));
        $this->setDescription('Generates user roles based on the security configuration.');
        $this->addOption('entity', null, InputOption::VALUE_REQUIRED,
            'The entity class name to initialize (shortcut notation)', 'AppBundle:UserRole'
        );
        $this->addOption('attributes', null, InputOption::VALUE_REQUIRED,
            'The entity attributes', array('name', 'parent', 'level', 'ordinal')
        );
        $this->addOption('dump', null, InputOption::VALUE_NONE,
            'Display the generated role hierarchy to the screen.'
        );
        $this->addOption('sync', null, InputOption::VALUE_NONE,
            'Save the generated role hierarchy to your database.'
        );
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        
        $entity = Validators::validateEntityName($input->getOption('entity'));
        $entityName = str_replace('/', '\\', $entity);
        $pos = strpos($entityName, ':');
        $this->entityClass = $doctrine->getAliasNamespace(substr($entityName, 0, $pos)) . '\\' . substr($entityName, $pos + 1);
        
        list($attributes['name'], $attributes['parent'], $attributes['level'], $attributes['ordinal']) = $input->getOption('attributes');
        foreach ($attributes as $label => $attribute) {
            $this->entityProperties['get'][$label] = 'get' . ucfirst($attribute);
            $this->entityProperties['set'][$label] = 'set' . ucfirst($attribute);
        }
        
        $this->em = $doctrine->getManager();
        
        $repository = $this->em->getRepository($this->entityClass);
        
        $dbRoles = $repository->findBy(array(), array('ordinal' => 'ASC'));
        $configRoles = $container->getParameter('security.role_hierarchy.roles');
        $this->makeRoleItems($dbRoles, $configRoles);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dump')) {
            $this->displayRoles($output, 'database', $this->oldReferenceRoles, $this->oldUserRoles);
            $output->writeln('');
            $this->displayRoles($output, 'configuration', $this->newReferenceRoles, $this->newUserRoles);
        }
        if ($input->getOption('sync')) {
            if ($this->scheduleRoles()) {
                $output->writeln('Updating data...');
                $this->em->flush();
                $output->writeln('Data updated successfully!');
            } else {
                $output->writeln('Nothing to update - your data is already in sync with the configuration.');
            }
        }
    }
    
    private function checkRoles(array $userRoles)
    {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        foreach ($userRoles as $userRole) {
            if ($uow->isEntityScheduled($userRole)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function scheduleRoles()
    {
        $userRoles = array();
        foreach ($this->oldUserRoles as $oldUserRole) {
            if (!in_array($oldUserRole, $this->newUserRoles)) {
                $this->em->remove($oldUserRole);
            }
            if (!in_array($oldUserRole, $userRoles)) {
                $userRoles[] = $oldUserRole;
            }
        }
        foreach ($this->newUserRoles as $newUserRole) {
            if (!in_array($newUserRole, $this->oldUserRoles)) {
                $this->em->persist($newUserRole);
            }
            if (!in_array($newUserRole, $userRoles)) {
                $userRoles[] = $newUserRole;
            }
        }
        
        return $this->checkRoles($userRoles);
    }
    
    private function displayRoles(OutputInterface $output, $source, array $referenceRoles, array $userRoles)
    {
        $rolesCount = count($userRoles);
        $output->writeln(sprintf('<fg=green;options=underscore>Found %s item%s in the %s</>', ($rolesCount === 0) ? 'no' : $rolesCount, ($rolesCount === 1) ? '' : 's', $source));
        
        $refs = array();
        foreach ($userRoles as $userRole) {
            list($name, $level) = array($userRole->{$this->entityProperties['get']['name']}(), $userRole->{$this->entityProperties['get']['level']}());
            $refs[$level] = $name;
            $str = '';
            for ($i = 1; $i < $level; $i++) {
                $count = isset($refs[$i]) ? count($referenceRoles[$refs[$i]]) : 0;
                $s = ($count === 1) ? '└─ ' : '├─ ';
                $st = ($level - 1 > $i) ? '│  ' : $s;
                $str .= ($count === 0) ? '   ' : $st;
            }
            foreach ($referenceRoles as &$roleChildren) {
                if (($key = array_search($name, $roleChildren)) !== false) {
                    unset($roleChildren[$key]);
                }
            }
            $output->writeln(sprintf(' <fg=white>%s</><fg=%s>%s</>', $str, ($source === 'database') ? 'cyan' : 'yellow', $name));
        }
    }
    
    private function findUserRoleByName($name)
    {
        $object = null;
        foreach ($this->oldUserRoles as $oldUserRole) {
            if ($name === $oldUserRole->{$this->entityProperties['get']['name']}()) {
                $object = $oldUserRole;
                break;
            }
        }
        
        return $object;
    }
    
    private function makeNewUserRoles($parent = null, $level = 0)
    {
        $level++;
        $parentName = ($parent === null) ? '' : $parent->{$this->entityProperties['get']['name']}();
        $list = isset($this->newReferenceRoles[$parentName]) ? $this->newReferenceRoles[$parentName] : array();
        foreach ($list as $childName) {
            if (!isset($this->newUserRoles[$childName])) {
                $object = $this->findUserRoleByName($childName);
                $child = ($object === null) ? new $this->entityClass : $object;
                $child->{$this->entityProperties['set']['name']}($childName);
                $child->{$this->entityProperties['set']['parent']}($parent);
                $child->{$this->entityProperties['set']['level']}($level);
                $child->{$this->entityProperties['set']['ordinal']}(++$this->count);
                $this->newUserRoles[] = $child;
                $this->makeNewUserRoles($child, $level);
            }
        }
    }

    private function makeRoleItems(array $dbRoles, array $configRoles)
    {
        $props = $this->entityProperties;
        
        $this->oldUserRoles = $dbRoles;
        foreach ($this->oldUserRoles as $oldUserRole) {
            $childName = $oldUserRole->{$props['get']['name']}();
            $parentName = ($oldUserRole->{$props['get']['parent']}() === null) ? '' : $oldUserRole->{$props['get']['parent']}()->{$props['get']['name']}();
            $this->oldReferenceRoles[$parentName][] = $childName;
        }
        
        $transform = function($name) { return ucwords(strtolower(trim(str_replace(array('ROLE', '_'), ' ', $name)))); };
        foreach ($configRoles as $parentName => $children) {
            foreach ($children as $childName) {
                list($childName, $parentName) = array($transform($childName), $transform($parentName));
                $this->newReferenceRoles[$parentName][] = $childName;
            }
        }
        
        list($children, $roots) = array(array(), array());
        array_walk_recursive($this->newReferenceRoles, function($name) use (&$children) { $children[$name] = true; });
        $parents = array_keys($this->newReferenceRoles);
        array_walk($parents, function($name) use (&$roots, $children) { if (!isset($children[$name])) { $roots[] = $name; } });
        
        $this->newReferenceRoles[null] = $roots;

        $this->makeNewUserRoles();
    }
}
