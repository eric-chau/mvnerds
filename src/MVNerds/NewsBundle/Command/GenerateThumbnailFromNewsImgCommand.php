<?php

namespace MVNerds\NewsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateThumbnailFromNewsImgCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('news:generate-thumbnail')
            ->setDescription('Génère un thumbnail pour chaque image associées à une news pour accélèrer le chargement en page d\'accueil du site')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('------ Début de la procédure de génération de thumbnail');
        
		$news = \MVNerds\CoreBundle\Model\NewsQuery::create()->find();
		
		foreach ($news as $oneNews) {
			$oneNews->setImageName(str_replace('/medias/images/news/', '', $oneNews->getImageName()));
			$imageName = $oneNews->getImageName();
			$destination = __DIR__ . '/../../../../web/medias/images/news/';
			$extension = explode('.', $imageName);
			$extension = $extension[count($extension) - 1];
			
			//Création de la miniature
			$source = null;
			if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'jpe' || $extension == 'jfif') {
				$source = imagecreatefromjpeg($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagejpeg($miniature, $destination . "mini_" . $imageName);
			} elseif ($extension == 'gif') {
				$source = imagecreatefromgif($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagegif($miniature, $destination . "mini_" . $imageName);
			} elseif ($extension == 'png') {
				$source = imagecreatefrompng($destination . $imageName);
				$miniature = $this->generateResampled($source, $imageName, $extension, $destination);
				imagepng($miniature, $destination . "mini_" . $imageName);
			}
			
			// Finally
			$oneNews->save();
		}
		
		$output->writeln('Fin de la procédure de génération de thumbnail --------');
	}
	
	private function generateResampled($source)
	{
		$largeur_source = imagesx($source);
		$hauteur_source = imagesy($source);

		$maxHeight = 160;
		$maxWidth = 300;
		
		$x = $maxWidth;
		$y = $maxWidth * $hauteur_source / $largeur_source;
		if ($y > $maxHeight) {
			$y = $maxHeight;
			$x = $largeur_source * $maxHeight / $hauteur_source;
		}
		
		// On crée la miniature vide
		$miniature = imagecreatetruecolor($x, $y); 
		$largeur_miniature = imagesx($miniature);
		$hauteur_miniature = imagesy($miniature);

		// On crée la miniature
		imagecopyresampled($miniature, $source, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur_source, $hauteur_source);
		
		return $miniature;
	}
}
