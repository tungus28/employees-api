<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractFOSRestController
{
    /**
     * Get a list of categories
     *
     * @OA\Get(
     *   security={ },
     *   description= "Get a list of categories"
     * )
     * @OA\Tag(name="Categories")
     *
     * @Route("/categories", name="categories_get", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function getCategories(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        CategoryRepository $categoryRepository
    ): Response
    {
       $categories = $categoryRepository->findAll();

        $response = [];
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $response[] = array(
                    'identifier' => $category->getId(),
                    'name' => $category->getName()
                );
            }
        }

        $json = $serializer->serialize($response, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], []));

        return new JsonResponse($json, Response::HTTP_OK, [], true);

    }

    /**
     *
     * Create a Category
     *
     * @OA\RequestBody(
     *   description= "Create a Category",
     *   required= true,
     *   @OA\JsonContent(
     *      type="object",
     *       @OA\Property(property="name", type="string")
     *   )
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Returns empty body with 201 status code"
     * )
     *
     * @OA\Tag(name="Categories")
     * @Security(name="Bearer")
     *
     * @Route("/categories", name="categories_post", methods={"POST"})
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function postCategory(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response
    {
        try {
            $category = $serializer->deserialize($request->getContent(), Category::class, 'json', []);
            //$employee = $serializer->deserialize($request->getContent(), null, 'json', ['category'=> new Category()]);
        } catch (NotEncodableValueException $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid Json');
        }

        $errors = $validator->validate($category);

        if (count($errors) > 0) {
            $json = $serializer->serialize($errors, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], []));

            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($category);
        $em->flush();

        return new Response(null, Response::HTTP_CREATED);
    }
}