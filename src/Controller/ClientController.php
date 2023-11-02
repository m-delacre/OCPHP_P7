<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/api/clients/{id}', name: 'api_client_details', methods: ['GET'])]
    public function getClientDetailst(Client $client, SerializerInterface $serializer): JsonResponse
    {
        /**
         * @var Company
         */
        $user = $this->getUser();

        if ($client) {
            if ($client->getCompany() === $user) {
                $context = SerializationContext::create()->setGroups(['getSingleClient']);
                $jsonClient = $serializer->serialize($client, 'json', $context);
                return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
            } else {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients', name: 'api_client_create', methods: ['POST'])]
    public function createClient(UrlGeneratorInterface $urlGenerator, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, "json");
        $client->setCompany($this->getUser());

        $errors = $validator->validate($client);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($client);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getSingleClient']);
        $jsonClient = $serializer->serialize($client, 'json', $context);

        $location = $urlGenerator->generate('api_client_details', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/clients/{id}', name: 'api_client_update', methods: ['PUT'])]
    public function updateClient(Request $request, SerializerInterface $serializer, Client $currentClient, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        /**
         * @var Company
         */
        $user = $this->getUser();
        if ($currentClient->getCompany() === $user) {
            $newClient = $serializer->deserialize($request->getContent(), Client::class, 'json');

            $errors = $validator->validate($newClient);
            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }

            $currentClient->setAddress($newClient->getAddress());
            $currentClient->setEmail($newClient->getEmail());
            $currentClient->setFirstName($newClient->getFirstName());
            $currentClient->setLastName($newClient->getLastName());
            $currentClient->setPhoneNumber($newClient->getPhoneNumber());

            $em->persist($currentClient);
            $em->flush();
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        } else {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients/{id}', name: 'api_client_delete', methods: ['DELETE'])]
    public function deleteClient(Client $client, EntityManagerInterface $em): JsonResponse
    {
        /**
         * @var Company
         */
        $user = $this->getUser();
        if ($client->getCompany() === $user) {
            $em->remove($client);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }
}
