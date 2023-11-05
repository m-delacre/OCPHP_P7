<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\EventSubscriber\ExceptionSubscriber;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class ClientController extends AbstractController
{
    /**
     * Retourne la liste des clients de l'utilisateur connecté.
     *
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des clients de l'utilisateur.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['getClients']))
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Vous devez être connecté ou JWT Token invalide."
    )]
    #[OA\Tag(name: 'Clients')]
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

    /**
     * retourn les détails d'un client.
     * 
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[OA\Response(
        response: 200,
        description: "Retourne les détails d'un client.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['getSingleClient']))
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Le client demandé n'existe pas.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ExceptionSubscriber::class))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: "L'id du client dont vous voulez les détails.",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Clients')]
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

    /**
     * Créer un nouveau client.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[OA\Response(
        response: 201,
        description: "Le client a bien été ajouté.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['getSingleClient']))
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Veuillez vérifier le body de votre requête."
    )]
    #[OA\RequestBody(
        description: "Les champs pour créer un client :",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstName', type: 'string'),
                new OA\Property(property: 'lastName', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phoneNumber', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
            ]
        )
    )]
    #[OA\Tag(name: 'Clients')]
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

    /**
     * Met à jour les informations d'un client.
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Client $currentClient
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[OA\Response(
        response: 204,
        description: "Le client a été mit à jour",
    )]
    #[OA\Response(
        response: 400,
        description: "Veuillez vérifier le body de votre requête."
    )]
    #[OA\Response(
        response: 401,
        description: "Vous n'êtes pas autorisé à modifier ce client.",
    )]
    #[OA\Response(
        response: 404,
        description: "Le client demandé n'existe pas.",
    )]
    #[OA\RequestBody(
        description: "Les champs pour mettre à jour un client :",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'int'),
                new OA\Property(property: 'firstName', type: 'string'),
                new OA\Property(property: 'lastName', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phoneNumber', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: "L'id du client que vous souhaitez modifier",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Clients')]
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

    /**
     * Supprimer un client.
     * 
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[OA\Response(
        response: 204,
        description: "Le client a été supprimé.",
    )]
    #[OA\Response(
        response: 401,
        description: "Vous n'êtes pas autorisé à supprimer ce client.",
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        description: "L'id du client que vous souhaitez supprimer.",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Clients')]
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
