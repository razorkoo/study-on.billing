<?php

namespace App\Controller;

use App\Entity\BillingUser;
use App\Repository\BillingUserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
    public function register(Request $request,ValidatorInterface $validator, JWTTokenManagerInterface $JWTManager, UserPasswordEncoderInterface $encoder)
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
            $manager = $this->getDoctrine()->getManager();
            $userRepository = $manager->getRepository(BillingUser::class);
            $checkUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if(!$checkUser) {
                $manager->persist($user);
                $manager->flush();
                $response->setContent(json_encode(['userToken' => $JWTManager->create($user), 'roles' => $user->getRoles()]));
                $response->setStatusCode(Response::HTTP_CREATED);
            } else {
                $message = "The Same user is already exist";
                $response->setContent(json_encode(['errors' => $message]));
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }
        return $response;

    }
}
