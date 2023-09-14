<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\GithubUserProvider;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\Exception\LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GithubUserProviderTest extends TestCase
{
    private ?MockObject $client = null;
    private ?MockObject $serializer = null;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $response = new Response();
        $this->client
            ->expects($this->once())
            ->method('get')->willReturn($response);

        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testLoadUserByUsernameReturningAUser()
    {
        $userData = [
            'login' => 'login',
            'name' => 'test',
            'email' => 'email',
            'avatar_url' => 'avatar_url',
            'html_url' => 'html_url',
        ];

        $client = $this->client;
        $serializer = $this->serializer;
        $serializer->expects($this->once())->method('deserialize')->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($client, $serializer);

        $user = new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );
        $userReturn = $githubUserProvider->loadUserByUsername($userData['name']);
        $this->assertEquals($user, $userReturn);
        $this->assertSame('App\Entity\User', get_class($userReturn));
    }

    public function testloadUserByUsernameErrors()
    {
        $client = $this->client;
        $serializer = $this->serializer;
        $serializer->method('deserialize')->willReturn(null);
        $githubUserProvider = new GithubUserProvider($client, $serializer);
        $this->expectException(LogicException::class);
        $githubUserProvider->loadUserByUsername('name');
    }


    public function tearDown(): void
    {
        $this->client = null;
        $this->serializer = null;
        parent::tearDown();
    }
}