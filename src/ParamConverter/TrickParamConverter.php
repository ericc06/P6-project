<?php
// src/ParamConverter/TrickParamConverter.php

namespace App\ParamConverter;

use App\Entity\Trick;
use App\Service\TrickManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TrickParamConverter implements ParamConverterInterface
{
    private $entMan;
    private $trickManager;

    public function __construct(EntityManagerInterface $entMan, TrickManager $trickManager)
    {
        $this->trickManager = $trickManager;
        $this->entMan = $entMan;
    }

    public function supports(ParamConverter $configuration)
    {
        // If the controller parameter name is different from 'trick',
        // we don't use this converter
        if ('trick' !== $configuration->getName()) {
            return false;
        }

        return true;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        // On récupère la valeur actuelle de l'attribut
        $trick = $this->entMan->getRepository(Trick::class)
        ->find($request->attributes->get('id'));

        $mediasCollection = $this->trickManager
            ->getMediasCollectionByTrickId($request->attributes->get('id'));

        $trick->setMedias($mediasCollection);

        // On met à jour la nouvelle valeur de l'attribut
        $request->attributes->set('trick', $trick);
    }
}
