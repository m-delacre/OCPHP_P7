<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ClientController extends AbstractController
{
    #[Route('/api/{company}/clients', name: 'app_client')]
    public function getAllClientsOfCompany(string $company, CompanyRepository $companyRepository, ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $companyName = strtoupper($company);
        $company = $companyRepository->findOneBy(["name"=>$companyName]);

        $user = $this->getUser();
        $userRoles = $user->getRoles();
        $roleToFind = "ROLE_" . $companyName;

        if (in_array($roleToFind, $userRoles) === false) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        $clientList = $clientRepository->findBy(["company"=>$company]);

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonClientList = $serializer->serialize($clientList, 'json', $context);

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }
}
