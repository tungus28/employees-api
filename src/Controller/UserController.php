<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\SerializerInterface;



/**
 * User
 */
class UserController extends AbstractFOSRestController
{
    /**
     * Get Users
     *
     * @OA\Get(
     *   description= "Get Users"
     * )
     * @Security(name="Bearer")
     * @Route("/users/", name="users_get", methods={"GET"})
     * @OA\Tag(name="Users")
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getUsers(EntityManagerInterface $em, UserRepository $userRepository, SerializerInterface $serializer): Response
    {
        $users = $userRepository->findAll();

        $response = [];
        if (count($users) > 0) {
            foreach ($users as $user) {
                $response[] = array(
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                );
            }
        }

        $json = $serializer->serialize($response, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], []));

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

}
