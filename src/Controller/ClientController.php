<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_client')]
    public function getAllClientsOfCompany(SerializerInterface $serializer): JsonResponse
    {
        /**
         * @var Company
         */
        $user = $this->getUser();

        $clientList = $user->getClients();

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonClientList = $serializer->serialize($clientList, 'json', $context);

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }
}
