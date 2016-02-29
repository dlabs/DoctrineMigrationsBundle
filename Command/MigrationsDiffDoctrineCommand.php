<?php

/*
 * This file is part of the Doctrine MigrationsBundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\MigrationsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;

/**
 * Command for generate migration classes by comparing your current database schema
 * to your mapping information.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class MigrationsDiffDoctrineCommand extends DiffCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:migrations:diff')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command.')
        ;
    }

    protected $excludeParameterName = 'doctrine_migrations.exclude_entity_managers';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManagers = $this->getEntityManagers();

        //this is a hack as migrations for other DBs are done in access-layer
        // we need bof2 to handle only platform DB/domain
        $defaultEntityManager = isset($entityManagers['default']) ? $entityManagers['default'] : null;
        $entityManagers = ['default' => $defaultEntityManager];

        foreach ($entityManagers as $entityManagerName=>$service) {

            $input->setOption('em', $entityManagerName);

            $this->resetMigrationConfiguration();

            DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $entityManagerName);

            $configuration = $this->getMigrationConfiguration($input, $output);

            DoctrineCommand::configureMigrations($this->getContainer(), $configuration);

            parent::execute($input, $output, $entityManagerName);
        }
    }
}
