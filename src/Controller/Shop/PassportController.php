<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Controller\Shop;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

final class PassportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function show(int $id): Response
    {
        $passport = $this->entityManager
            ->getRepository(DigitalProductPassport::class)
            ->find($id);

        if ($passport === null || !$passport->isPublished()) {
            throw new NotFoundHttpException('Passport not found');
        }

        return $this->render('@TmonierSyliusDigitalProductPassportPlugin/shop/passport/show.html.twig', [
            'passport' => $passport,
        ]);
    }
}
