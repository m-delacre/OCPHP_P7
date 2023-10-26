<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'api_phones')]
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $phoneList = $phoneRepository->findAll();

        $jsonPhoneList = $serializer->serialize($phoneList, 'json', ["groups" => "getPhones"]);

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/phones/{id}', name: 'api_phone_details')]
    public function getPhoneDetails(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        if ($phone) {
            $jsonPhone = $serializer->serialize($phone, 'json');
            return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
