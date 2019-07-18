<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use App\DataFixtures\AppFixtures;
use App\Tests\AbstractTest;


class ApiControllerTest extends AbstractTest
{



    public function getFixtures(): array
    {
        return [AppFixtures::class];
    }
    /*
    public function testSomething()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
    public function executeFixtures()
    {
        $encoder = self::$kernel->getContainer()->get('security.password_encoder');
        $fixture = new AppFixtures($encoder);
        $manager = $this->getDoctrine()->getManager();
        $fixture->load($manager);
    }*/
    public function auth($user,$password)
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>$user,'password'=>$password]));
        $response = json_decode($client->getResponse()->getContent(),true);
        return $response;
    }
    public function testRegisterNewUser()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'user@test.com','password'=>'aaaaaa']));
        $this->assertSame(201,$client->getResponse()->getStatusCode());
        $this->assertContains('"userToken"', $client->getResponse()->getContent());
        $this->assertContains('"roles"', $client->getResponse()->getContent());
    }
    public function testExistingUser()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'testUser@test.com','password'=>'aaaaaa']));
        $this->assertSame(400,$client->getResponse()->getStatusCode());
        $this->assertContains('{"errors":"The Same user is already exist"}', $client->getResponse()->getContent());
    }
    public function testBlankData()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'','password'=>'']));
        $this->assertSame(400,$client->getResponse()->getStatusCode());
        $this->assertContains('{"errors":["Blank email","Blank password"]}', $client->getResponse()->getContent());
    }
    public function testInvalidData()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'testUser.com','password'=>'aa']));
        $this->assertSame(400,$client->getResponse()->getStatusCode());
        $this->assertContains('{"errors":["Wrong email format","Password must be at least 6 symbols"]}', $client->getResponse()->getContent());
    }
    public function testNotFoundPage()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/notfound',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'testUser.com','password'=>'aa']));
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
    public function testInternalError()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'aaaaaaaaa'],
            "");
        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }
    public  function testLogin()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $this->assertSame(200,$client->getResponse()->getStatusCode());
        $this->assertContains('"token"', $client->getResponse()->getContent());
        $this->assertContains('"roles"', $client->getResponse()->getContent());
    }
    public function testAdmin()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser2@test.com','password'=>'password']));
        $this->assertSame(200,$client->getResponse()->getStatusCode());
        $this->assertContains('"token"', $client->getResponse()->getContent());

    }
    public function testWrongLoginOrPassword()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser23@test.com','password'=>'password']));
        $this->assertSame(401,$client->getResponse()->getStatusCode());
        $this->assertContains('{"code":401,"message":"Bad credentials"}', $client->getResponse()->getContent());
    }
    public function testCurrentUser()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/users/current', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"balance"', $client->getResponse()->getContent());
    }
    public function testCurrentUserWrongToken()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/users/current', [], [], ['HTTP_AUTHORIZATION' => 'Bearer fsaf7gvzxuya7fas8f']);
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testBuyCourse()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/courses/test-kurs-arenda/pay', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"success"', $client->getResponse()->getContent());
    }
    public function testBuyCourseNoMoney()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'nullbalance@gmail.com','password'=>'aaaaaa']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/courses/test-kurs-arenda/pay', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"message"', $client->getResponse()->getContent());
    }
    public function testBuyCourseWrongToken()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/courses/test-kurs-arenda/pay', [], [], ['HTTP_AUTHORIZATION' => 'Bearer sdf8vzxsad9vzx8']);
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testGetTransactionsNoFilters()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"id"', $client->getResponse()->getContent());
    }
    public function testGetTransactionsSkipExpired()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions?skip_expired=1', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"id"', $client->getResponse()->getContent());
    }

    public function testGetTransactionsCodeFilter()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions?course_code=test-kurs-arenda', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"id"', $client->getResponse()->getContent());
    }
    public function testGetTransactionsFiltersTypeFilter()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions?type=deposit', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"id"', $client->getResponse()->getContent());
    }
    public function testGetTransactionsTypeFilterPayment()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions?type=payment', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']]);
        $this->assertContains('"id"', $client->getResponse()->getContent());
    }
    public function testGetTransactionsNoFiltersWrongToken()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $response = json_decode($client->getResponse()->getContent(),true);
        $client->request('GET', '/api/v1/transactions', [], [], ['HTTP_AUTHORIZATION' => 'Bearer svz8xva9vz0']);
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testGetAllCourses()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/courses', [], [], []);
        $this->assertContains('"code"', $client->getResponse()->getContent());
    }
    public function testGetCourseByCode()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/courses/test-kurs-arenda', [], [], []);
        $this->assertContains('"code"', $client->getResponse()->getContent());
    }
    public function testGetCourseByWrongCode()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/courses/test-kurs-arenda-noviy', [], [], []);
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
    public function testCreateCourse()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/add', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'new-course','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"success"', $client->getResponse()->getContent());
    }
    public function testCreateSameCourse()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/add', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'test-kurs-arenda','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
    public function testCreateWrongType()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/add', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'new-course','type' => 'rent2', 'price' => '100.5']));
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
    public function testCreateCourseWrongToken()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/add', [], [], ['HTTP_AUTHORIZATION' => 'Bearer sfv8vxz9asf9'], json_encode(['code' => 'new-course','type' => 'rent2', 'price' => '100.5']));
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testEditCourse()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/test-kurs-arenda', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'test-kurs-arenda2','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"success"', $client->getResponse()->getContent());
    }
    public function testEditWrongType()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/test-kurs-arenda', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'new-course','type' => 'rent2', 'price' => '100.5']));
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
    public function testEditCourseWrongToken()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/test-kurs-arenda', [], [], ['HTTP_AUTHORIZATION' => 'Bearer sfv8vxz9asf9'], json_encode(['code' => 'new-course','type' => 'rent2', 'price' => '100.5']));
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testEditCourseNotFound()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('POST', '/api/v1/courses/test-kurs-arenda3', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'test-kurs-arenda2','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
    public function testDeleteCourse()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('DELETE', '/api/v1/courses/test-kurs', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'test-kurs-arenda2','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"success"', $client->getResponse()->getContent());
    }

    public function testDeleteCourseWrongToken()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('DELETE', '/api/v1/courses/test-kurs-arenda', [], [], ['HTTP_AUTHORIZATION' => 'Bearer sfv8vxz9asf9'], json_encode(['code' => 'new-course','type' => 'rent2', 'price' => '100.5']));
        $this->assertResponseCode(401, $client->getResponse());
    }
    public function testDeleteCourseNotFound()
    {
        $response = $this->auth('testUser2@test.com','password');
        $client = static::createClient();
        $client->request('DELETE', '/api/v1/courses/test-kurs-arenda3', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']], json_encode(['code' => 'test-kurs-arenda2','type' => 'rent', 'price' => '100.5']));
        $this->assertContains('"errors"', $client->getResponse()->getContent());
    }
}
