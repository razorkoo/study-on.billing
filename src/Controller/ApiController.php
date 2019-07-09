<?php

namespace App\Controller;

use App\Entity\BillingUser;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\BillingUserRepository;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use PHPUnit\Runner\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializerBuilder;
use App\DTO\BillingUserFromModel;
use Swagger\Annotations as SWG;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;


class ApiController extends AbstractController
{

    /**
     * @Route("/api/v1/login", name="login", methods={"POST"} )
     *     @SWG\Post(
     *     path="/api/v1/login",
     *     summary="User authorization",
     *     tags={"Authorization"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="username",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Login successful",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="token",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="roles",
     *                  type="array",
     *                  @SWG\Items(type="string")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=400,
     *          description="Bad request",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="message",
     *                  type="array",
     *                  @SWG\Items(type="string")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=401,
     *          description="Bad credentionals",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=404,
     *          description="Page not found",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     */
    public function login()
    {
    }

    /**
     * @Route("/api/v1/register", name="register",  methods={"POST"})
     *     @SWG\Post(
     *     path="/api/v1/register",
     *     summary="Register user",
     *     tags={"Registration"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Register successful",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="token",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="roles",
     *                  type="array",
     *                  @SWG\Items(type="string")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=400,
     *          description="Bad request/The same user is already exist",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(type="string")
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=500,
     *          description="Invalid JSON",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=404,
     *          description="Page not found",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     */
    public function register(Request $request,ValidatorInterface $validator, JWTTokenManagerInterface $JWTManager, UserPasswordEncoderInterface $encoder, RefreshTokenManagerInterface $refreshTokenManager)
    {
        $message = "";
        $response = new Response();
        $serializer = SerializerBuilder::create()->build();
        $response->headers->set('Content-Type', 'application/json');
        $em = $this->getDoctrine()->getManager();
        $userDto = $serializer->deserialize($request->getContent(), BillingUserFromModel::class, 'json');
        $errors = $validator->validate($userDto);
        $dtoErrors = $validator->validate($userDto);
        if(count($dtoErrors) > 0) {
            $allErrors  = [];
            foreach($errors as $error) {
                $allErrors[]=$error->getMessage();
            }
            $response->setContent(json_encode(['errors' => $allErrors]));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $user = new BillingUser();


            $user->setEmail($userDto->email);
            $user->setPassword($encoder->encodePassword($user,$userDto->password));
            $user->setRoles(['ROLE_USER']);
            $user->setBalance(0.0);
            $refreshToken = $refreshTokenManager->create();
            $refreshToken->setUsername($user->getEmail());
            $refreshToken->setRefreshToken();
            $refreshToken->setValid((new \DateTime())->modify('+1 month'));
            $refreshTokenManager->save($refreshToken);
            $manager = $this->getDoctrine()->getManager();
            $userRepository = $manager->getRepository(BillingUser::class);
            $checkUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if(!$checkUser) {
                $manager->persist($user);
                $manager->flush();
                $response->setContent(json_encode(['userToken' => $JWTManager->create($user), 'roles' => $user->getRoles(), 'refresh_token' => $refreshToken->getRefreshToken()]));
                $response->setStatusCode(Response::HTTP_CREATED);
            } else {
                $message = "The Same user is already exist";
                $response->setContent(json_encode(['errors' => $message]));
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }
        return $response;

    }
    /**
     * @Route("/api/v1/token/refresh", name="refresh", methods={"POST"})
     */
    public function refresh(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
    /**
     * @Route("api/v1/users/current", name="current_user", methods={"GET"})
     * @SWG\Get(
     *    path="/api/v1/users/current",
     *    summary="Get current user info",
     *    tags={"Current User"},
     *    produces={"application/json"},
     *    @SWG\Response(
     *        response=200,
     *        description="Successful fetch user object",
     *        @SWG\Schema(
     *             @SWG\Property(
     *                 property="username",
     *                 type="string"
     *             ),
     *             @SWG\Property(
     *                 property="roles",
     *                 type="array",
     *                 @SWG\Items(type="string")
     *             ),
     *              @SWG\Property(
     *                 property="balance",
     *                 type="number"
     *             )
     *          )
     *      ),
     *    @SWG\Response(
     *        response="401",
     *        description="Unauthorized user",
     *    ),
     * )
     *    @Security(name="Bearer")
     */
    public function currentUser()
    {
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(["username" => $user->getUsername(), "roles" => $user->getRoles(), "balance" => $user->getBalance()]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
    /**
     * @Route("api/v1/courses", name="courses", methods={"GET"})
     * @SWG\Get(
     *    path="/api/v1/courses",
     *    summary="Получить курсы",
     *    tags={"Courses"},
     *    produces={"application/json"},
     *    @SWG\Response(
     *        response=200,
     *        description="Курсы успешно получены",
     *        @SWG\Schema(
     *             @SWG\Property(
     *                 property="code",
     *                 type="string"
     *             ),
     *             @SWG\Property(
     *                 property="type",
     *                 type="string"
     *             ),
     *             @SWG\Property(
     *                 property="price",
     *                 type="number"
     *             )
     *          )
     *    )
     * )
     */
    public function courses(Request $request, ValidatorInterface $validator)
    {
        $serializer = SerializerBuilder::create()->build();
        $response = new Response();
        $allCourses = $this->getDoctrine()->getRepository(Course::class)->findAll();
        $response->setContent($serializer->serialize($allCourses, 'json'));
        $response->setStatusCode(200);
        return $response;
    }
    /**
     * @Route("api/v1/courses/{code}", name="course", methods={"GET"})
     * @SWG\Get(
     *    path="/api/v1/courses/{code}",
     *    summary="Получить курс",
     *    tags={"Course"},
     *    produces={"application/json"},
     *    @SWG\Response(
     *        response=200,
     *        description="Курс успешно получен",
     *        @SWG\Schema(
     *             @SWG\Property(
     *                 property="code",
     *                 type="string"
     *             ),
     *             @SWG\Property(
     *                 property="type",
     *                 type="string"
     *             ),
     *             @SWG\Property(
     *                 property="price",
     *                 type="number"
     *             )
     *          )
     *       )
     * )
     */
    public function course($code)
    {
        $serializer = SerializerBuilder::create()->build();
        $response = new Response();

        $course = $this->getDoctrine()->getRepository(Course::class)->findOneBy($code);

        $response->setContent($serializer->serialize($course, 'json'));
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/api/v1/courses/add", name="course_add", methods={"POST"})
     */
    public function addCourse(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
    /**
     * @Route("/api/v1/courses/delete", name="course_delete", methods={"POST"})
     */
    public function deleteCourse(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
    /**
     * @Route("/api/v1/courses/edit", name="course_edit", methods={"POST"})
     */
    public function editCourse(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
}
