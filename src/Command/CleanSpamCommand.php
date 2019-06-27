<?php

namespace App\Command;

use App\Entity\User;
use App\Utils;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanSpamCommand extends Command
{
    protected static $defaultName = 'app:clean-spam';

    private $container;


    public function __construct($name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete spam user every day')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $em = $this->container->get('doctrine')->getManager();

        $users = $em->getRepository(User::class)->findBy([
            'active'=>0
        ]);
        //creo el objeto de redis
        $redis = new Utils\Redis();
        $robj=$redis->redis(4);


        foreach ($users as $u){
            $p=$u->getPerson();

            if(!$robj->exists($p->getToken())){
                $em->remove($u);
                $em->flush();
            }
        }

        $robj->disconnect();


        $io->success('Genial, hemos limpiado la base de datos de gañanes.');




    }

}
