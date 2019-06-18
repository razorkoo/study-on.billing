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
    public function testRegisterNewUser()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['email'=>'user@test.com','password'=>'aaaaaa']));
        $this->assertSame(201,$client->getResponse()->getStatusCode());
        $this->assertContains('"userToken"',$client->getResponse()->getContent());
        $this->assertContains('"roles"',$client->getResponse()->getContent());
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
        $this->assertSame(404,$client->getResponse()->getStatusCode());
    }
    public function testInternalError()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/register',[],[],['CONTENT_TYPE'=>'aaaaaaaaa'],
            "");
        $this->assertSame(500,$client->getResponse()->getStatusCode());
    }
    public  function testLogin()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser@test.com','password'=>'password']));
        $this->assertSame(200,$client->getResponse()->getStatusCode());
        $this->assertContains('"token"',$client->getResponse()->getContent());
        $this->assertContains('"roles"', $client->getResponse()->getContent());
    }
    public function testAdmin()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser2@test.com','password'=>'password']));
        $this->assertSame(200,$client->getResponse()->getStatusCode());
        $this->assertContains('"token"',$client->getResponse()->getContent());
        $this->assertContains('ROLE_SUPER_ADMIN',$client->getResponse()->getContent());
    }
    public function testWrongLoginOrPassword()
    {
        $client = static::createClient();
        $client->request('POST','/api/v1/login',[],[],['CONTENT_TYPE'=>'application/json'],
            json_encode(['username'=>'testUser23@test.com','password'=>'password']));
        $this->assertSame(401,$client->getResponse()->getStatusCode());
        $this->assertContains('{"code":401,"message":"Bad credentials"}',$client->getResponse()->getContent());
    }

}
