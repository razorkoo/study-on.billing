<?php

namespace App\Controller;

use App\DTO\CourseFromJson;
use App\Entity\BillingUser;
use App\Entity\Course;
use App\Entity\Transaction;
use App\Repository\CourseRepository;
use App\Repository\BillingUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\PaymentService;
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
     *                  property="userToken",
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
     *     @SWG\Post(
     *     path="/api/v1/token/refresh",
     *     summary="Refresh token",
     *     tags={"Refresh Token"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="refresh_token",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Token refreshed",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="token",
     *                  type="string"
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
     *     )
     * )
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
        $allCoursesDataBase = $this->getDoctrine()->getRepository(Course::class)->findAll();
        $allCourses = [];
        $types = ["rent","full","free"];

        foreach($allCoursesDataBase as $course) {
            $course->getSlug();
            if ($course->getType()<3) {
                $allCourses[] = ['code'=>$course->getSlug(),'type'=>$types[$course->getType()-1], 'price' => $course->getPrice()];
            } else {
                $allCourses[] = ['code'=>$course->getSlug(),'type'=>$types[$course->getType()-1]];
            }
        }
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
        $types = ["rent","full","free"];
        $course = $this->getDoctrine()->getRepository(Course::class)->findOneBy(['slug'=>$code]);
        if($course) {
            if ($course->getType() < 3) {
                $response->setContent(json_encode(['code' => $course->getSlug(), 'type' => $types[$course->getType() - 1], 'price' => $course->getPrice()]));
            } else {
                $response->setContent(json_encode(['code' => $course->getSlug(), 'type' => $types[$course->getType() - 1]]));
            }
        } else {
            $response->setContent(json_encode(['errors'=>"Course doesn't exist"]));
        }

        //$response->setContent($serializer->serialize($course, 'json'));
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("api/v1/courses/add", name="course_add", methods={"POST"})
     * @SWG\Post(
     *     path="/api/v1/courses/add",
     *     summary="Добавить новый курс",
     *     tags={"Создать курс"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="string"
     *              ),
     *
     *              @SWG\Property(
     *                  property="type",
     *                  type="int"
     *              ),
     *              @SWG\Property(
     *                  property="price",
     *                  type="float"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Курс создан",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=400,
     *          description="Bad request",
     *          @SWG\Schema(
     *
     *              @SWG\Property(
     *                  property="errors",
     *                  type="string"
     *              )
     *          )
     *     )
     *
     * )
     */
    public function addCourse(Request $request, ValidatorInterface $validator)
    {
            $serializer = SerializerBuilder::create()->build();
            $courseDto = $serializer->deserialize($request->getContent(), CourseFromJson::class, 'json');
            $errors = $validator->validate($courseDto);
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            if (count($errors)>0) {
                $allErrors  = [];
                foreach($errors as $error) {
                    $allErrors[]=$error->getMessage();
                }
                $response->setContent(json_encode(['errors' => $allErrors]));
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            } else {
                $course = new Course();
                $course->setSlug($courseDto->code);
                $course->setType($courseDto->type);
                if ($courseDto->type < 3) {
                    $course->setPrice($courseDto->price);
                }
                $manager = $this->getDoctrine()->getManager();
                $userRepository = $manager->getRepository(Course::class);
                $checkCourse = $userRepository->findOneBy(['slug' => $course->getSlug()]);
                if(!$checkCourse) {
                    $manager->persist($course);
                    $manager->flush();
                    $response->setContent(json_encode(['success'=>true]));
                    $response->setStatusCode(Response::HTTP_CREATED);
                } else {
                    $message = "The Same course is already exist";
                    $response->setContent(json_encode(['errors' => $message]));
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                }
            }

        return $response;
    }
    /**
     * @Route("api/v1/courses/{code}", name="course_delete", methods={"DELETE"})
     * @SWG\Delete(
     *    path="/api/v1/courses/{code}",
     *    summary="Удалить курс",
     *    tags={"Удалить курс"},
     *    produces={"application/json"},
     *    @SWG\Response(
     *        response=200,
     *        description="Курс успешно удален",
     *        @SWG\Schema(
     *             @SWG\Property(
     *                 property="success",
     *                 type="boolean"
     *             )
     *          )
     *     ),
     *    @SWG\Response(
     *          response=400,
     *          description="Bad request",
     *          @SWG\Schema(
     *
     *              @SWG\Property(
     *                  property="errors",
     *                  type="string"
     *              )
     *          )
     *     ),
     * )
     */
    public function deleteCourse($code, Request $request, ValidatorInterface $validator)
    {
        $response = new Response();
        $course = $this->getDoctrine()->getRepository(Course::class)->findOneBy(['slug' => $code]);
        if (!$course) {
            $response->setContent(json_encode(['errors' => "Course doesn't exist"]));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        if ($this->getDoctrine()->getRepository(Transaction::class)->findOneBy(['course' => $course])) {
            $response->setContent(json_encode(['errors' => 'This course exists in any transactions']));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($course);
            $entityManager->flush();
            $response->setContent(json_encode(['success' => true]));
            $response->setStatusCode(Response::HTTP_OK);
        }
        return $response;
    }
    /**
     * @Route("/api/v1/courses/{code}", name="course_update", methods={"POST"})
     * @SWG\Post(
     *     path="/api/v1/courses/{code}",
     *     summary="Редактирование курса",
     *     tags={"Редактирование курса"},
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="price",
     *                  type="floats"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Курс отредактирован",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=400,
     *          description="Bad request",
     *          @SWG\Schema(
     *
     *              @SWG\Property(
     *                  property="errors",
     *                  type="string"
     *              )
     *          )
     *     )
     *
     * )
     */
    public function editCourse($code, Request $request, ValidatorInterface $validator)
    {
        $serializer = SerializerBuilder::create()->build();
        $courseDto = $serializer->deserialize($request->getContent(), CourseFromJson::class, 'json');
        $errors = $validator->validate($courseDto);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if (count($errors)>0) {
            $allErrors  = [];
            foreach($errors as $error) {
                $allErrors[]=$error->getMessage();
            }
            $response->setContent(json_encode(['errors' => $allErrors]));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $manager = $this->getDoctrine()->getManager();
            $userRepository = $manager->getRepository(Course::class);
            $course = $userRepository->findOneBy(['slug' => $code]);
            if($course) {
                $course->setSlug($courseDto->code);
                $course->setPrice($courseDto->price);
                $course->setType($courseDto->type);
                $manager->persist($course);
                $manager->flush();
                $response->setContent(json_encode(['success'=>true]));
                $response->setStatusCode(Response::HTTP_CREATED);
            } else {
                $message = "Course doesn't exist";
                $response->setContent(json_encode(['errors' => $message]));
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }
        return $response;
    }
    /**
     * @Route("/api/v1/courses/{code}/pay", name="course_pay", methods={"GET"})
     * @Security(name="Bearer")
     */
    public function payCourse($code, Request $request, ValidatorInterface $validator)
    {

        $manager = $this->getDoctrine()->getManager();
        $userCourse = $manager->getRepository(Course::class)->findOneBy(['slug'=>$code]);
        $paymentService = new PaymentService($manager);
        $response = new Response();
        if (!$userCourse) {
            $response->setStatusCode(404);
            $response->setContent(json_encode(['code'=>404,'message'=>"Course doesn't exist"]));
            return $response;
        } else {

            try {
                $user = $this->getUser();
                $transaction = $paymentService->buyCourse($user->getEmail(),$userCourse);
                $response->setStatusCode(200);
            } catch (Exception $e) {
                $response->setStatusCode($e->getCode());
                $response->setContent(json_encode(['errors'=>$e->getMessage()]));
                return $response;
            }
            if ($userCourse->getTypeAsString($userCourse->getType()) === 'rent') {
                $response->setContent(json_encode(['success'=>true,'type'=>$userCourse->getTypeAsString($userCourse->getType()), 'expired_at'=>$transaction->getExpiredat()]));

            } else {
                $response->setContent(json_encode(['success'=>true,'type'=>$userCourse->getTypeAsString($userCourse->getType())]));
            }

        }
        return $response;
    }
    /**
     * @Route("/api/v1/transactions", name="transactions", methods={"GET"})
     * *@SWG\Get(
     *     tags={"user"},
     *     summary="Get transactions for user",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          type="boolean",
     *          description="filter skip_expired"
     *      ),
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          type="string",
     *          description="filter type"
     *      ),
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          type="string",
     *          description="filter course_code"
     *      ),
     *     @SWG\Response(
     *          response=200,
     *          description="Success request",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="courses",
     *                  type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="amount",
     *                          type="number",
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="course_code",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="expired_at",
     *                          type="string"
     *                      )
     *                  )
     *              )
     *          )
     *     ),
     *     @SWG\Response(
     *          response=401,
     *          description="Invalid JWT token",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="messsage",
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function transactions(Request $request)
    {
        $response = new Response();
        $data = [];
        $filterType = $request->query->get('type');
        $filterDate = $request->query->get('skip_expired');
        $filterCourse = $request->query->get('course_code');
        if ($filterType) {
            switch ($filterType) {
                case 'deposit':
                    $filterType = Transaction::TYPE_DEPOSIT;
                    break;
                case 'payment':
                    $filterType = Transaction::TYPE_PAYMENT;
                    break;
            }
        }
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($filterCourse) {
            $course = $entityManager->getRepository(Course::class)->findOneBy(['slug' => $filterCourse])->getId();
        }
        else $course = null;
        $transactions = $entityManager->getRepository(Transaction::class)->findWithFilters($user->getId(), $filterType, $filterDate, $course);
        foreach ($transactions as $transaction) {
            $postData = ['id' => $transaction->getId(), 'amount' => $transaction->getValue()];
            if ($transaction->getType() == Transaction::TYPE_DEPOSIT) {
                $postData['type'] = 'deposit';
            } else {
                $postData['course_code'] = $transaction->getCourse()->getSlug();
                $postData['type'] = 'payment';
                if ($transaction->getCourse()->getType() == Course::TYPE_RENT) {
                    $postData['expired_at'] = $transaction->getExpiredat();
                }
            }
            $data[] = $postData;
        }
        if(count($data) > 0) {
            $response->setContent(json_encode($data));
        } else {
            $response->setContent(json_encode(['message' => 'У вас нет транзакций']));
        }

        $response->setStatusCode(200);
        return $response;
    }


}
