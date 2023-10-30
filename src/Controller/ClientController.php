<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_clients', methods: ['GET'])]
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

    #[Route('/api/clients', name: 'api_client_create', methods: ['POST'])]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, "json");
        $client->setCompany($this->getUser());
        $em->persist($client);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getSingleClient']);
        $jsonClient = $serializer->serialize($client, 'json', $context);

        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }
}
