<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class PhoneController extends AbstractController
{
    /**
     * Retourne la liste des téléphones disponible chez BilMo.
     *
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des téléphones.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: ['getPhones']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: "Le numéro de la page de début.",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: "Combien d'éléments seront retournés.",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Phones')]
    #[Route('/api/phones', name: 'api_phones', methods: ['GET'])]
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);



        $phoneList = $phoneRepository->findAllWithPagination($page, $limit);

        $context = SerializationContext::create()->setGroups(['getPhones']);
        $jsonPhoneList = $serializer->serialize($phoneList, 'json', $context);

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne les détails d'un téléphone.
     * 
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[OA\Response(
        response: 200,
        description: "Retourne les détails d'un téléphone.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: ['getSinglePhone']))
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Le téléphone demandé n'existe pas.",
    )]
    #[OA\Tag(name: 'Phones')]
    #[Route('/api/phones/{id}', name: 'api_phone_details', methods: ['GET'])]
    public function getPhoneDetails(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        if ($phone) {
            $context = SerializationContext::create()->setGroups(['getSinglePhone']);
            $jsonPhone = $serializer->serialize($phone, 'json', $context);
            return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
