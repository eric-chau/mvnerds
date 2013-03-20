<?php

namespace MVNerds\CommentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommentCountFromResponseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('comment:update-comment-count')
            ->setDescription('Mettre à jour le nombre de commentaire des objets commentables pour prendre en compte les réponses à des commentaires')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('------ Début de la procédure de la mise à jour');
        
		$responses = \MVNerds\CoreBundle\Model\CommentResponseQuery::create()
			->joinWith('Comment')
		->find();
		
		foreach ($responses as $response) {
			$comment = $response->getComment();
			$objectQuery = $comment->getObjectNamespace() . 'Query';
			$object = $objectQuery::create()->findOneById($comment->getObjectId());
			
			$object->setCommentCount($object->getCommentCount() + 1);
			if (method_exists($object, 'keepUpdateDateUnchanged')) 
			{
				$object->keepUpdateDateUnchanged();
			}

			// Finally
			$object->save();
		}
		
		$output->writeln('Fin de la procédure de la mise à jour --------');
	}
}
