<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Employee;
use App\Repository\CategoryRepository;
use App\Repository\EmployeeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeController extends AbstractFOSRestController
{

    /**
     * Get a list of employees sorted by SubordinatesCount
     *
     * @OA\Get(
     *   security={ },
     *   description= "Get a list of employees sorted by SubordinatesCount"
     * )
     * @OA\Parameter(
     *     name="category",
     *     in="query",
     *     description="The field used to filter the list of Employees by Category",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Employees")
     *
     * @Route("/employees", name="employees_get", methods={"GET"})
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getEmployees(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer
    ): Response
    {
        $repo = $em->getRepository('App\Entity\Employee');

        //$arrayTree = $repo->childrenHierarchy();

        $filterCategory = $request->query->get('category');

        //dump($filterCategory); die();

        if($filterCategory !== null) {
            $employees = $repo->findBy(['category' => $em->getRepository('App\Entity\Category')
                ->findOneByCategory($filterCategory)]);
        } else {
            $employees = $repo->findAll();
        }

        $response = [];
        if (count($employees) > 0) {
            foreach ($employees  as $employee) {
                    $response[] = [
                        'identifier' => $employee->getId(),
                        'firstName' => $employee->getFirstName(),
                        'lastName' => $employee->getLastName(),
                        'email' => $employee->getEmail(),
                        'categoryName' => $employee->getCategory()->getName(),
                        'categoryIdentifier' => $employee->getCategory()->getId(),
                        'subordinatesCount' => count($repo->getChildren($employee)),
                    ];
            }
        }

        usort($response, function($a, $b) {
            return $a['subordinatesCount'] - $b['subordinatesCount'];
        });

        $json = $serializer->serialize($response, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], []));

        return new JsonResponse($json, Response::HTTP_OK, [], true);

    }

    /**
     *
     * Create an Employee with the certain attributes including Category (automatically created if needed)
     *
     * @OA\RequestBody(
     *   description= "Create an Employee with the certain attributes including Category (automatically created if needed)",
     *   required= true,
     *   @OA\JsonContent(
     *      type="object",
     *       @OA\Property(property="firstName", type="string"),
     *       @OA\Property(property="lastName", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(
     *          property="category", type="object",
     *          @OA\Property(property="name", type="string"),
     *       ),
     *       @OA\Property(property="parentEmail", type="string")
     *    )
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Returns empty body with 201 status code"
     * )
     *
     * @OA\Tag(name="Employees")
     * @Security(name="Bearer")
     *
     * @Route("/employees", name="employee_post", methods={"POST"})
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function postEmployee(
        EntityManagerInterface $em,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response
    {
        try {
            $employee = $serializer->deserialize($request->getContent(), Employee::class, 'json', []);
            //$employee = $serializer->deserialize($request->getContent(), null, 'json', ['category'=> new Category()]);
        } catch (NotEncodableValueException $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid Json');
        }

        $errors = $validator->validate($employee);

        if (count($errors) > 0) {
            $json = $serializer->serialize($errors, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], []));

            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $categoryRepo = $em->getRepository('App\Entity\Category');
        $employeeRepo = $em->getRepository('App\Entity\Employee');

        $category = $categoryRepo->findOneByCategory($employee->getCategory()->getName());

        if( $category === null ) {
            $category = new Category();
            $category->setName($employee->getCategory()->getName());

            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                $json = $serializer->serialize($errors, 'json', array_merge([
                    'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                ], []));

                return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
            }
            $em->persist($category);
            $em->flush();
        }

        $employee->setCategory($category);
        $employee->setParent($employeeRepo->findOneByEmail($employee->getParentEmail()));

        $em->persist($employee);
        $em->flush();

        return new Response(null, Response::HTTP_CREATED);
    }
}