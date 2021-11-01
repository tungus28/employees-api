<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Defines application features from the specific context.
 */
class NewEmployeeContext implements Context
{
    private $client;
    private $response;
    private $token;
    private $payload;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Given I am an authenticated user
     */
    public function iAmAnAuthenticatedUser()
    {
        $this->response = $this->client->request(
            'POST',
            'http://localhost:80/api/login_check',
            ['json' => [
                'username' => 'admin@test.com',
                'password' => '12345'
                ]
            ]
        );

        try {
            $decodedPayload = $this->response->toArray();
        } catch (PendingException $e) {
            throw new PendingException($e);
        }

        $this->token = $decodedPayload['token'];

        if( !isset($this->token) ) {
            throw new PendingException('Not able to get jwt!');
        }

        return true;
    }

    /**
     * @Given I have the payload:
     */
    public function iHaveThePayload(PyStringNode $string)
    {
        $this->payload = (string) $string;
    }

    /**
     * @When I request POST :uri
     */
    public function iRequestPost($uri)
    {

        $this->response = $this->client->request('POST', $uri, [
            'body' => $this->payload,
            'headers' => [
                "Authorization" => "Bearer {$this->token}",
                "Content-Type" => "application/json",
            ],
        ]);

    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe($code)
    {
        $responseCode = $this->response->getStatusCode();

        if($responseCode != $code) {
            throw new PendingException("Not able to add a new employee! "
                . json_decode($this->response->getContent(false), true)['detail']);
        }

        return true;
    }


}
