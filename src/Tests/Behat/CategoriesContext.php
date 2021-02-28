<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Defines application features from the specific context.
 */
class CategoriesContext implements Context
{
    private $client;
    private $response;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Given I am an unauthenticated user
     */
    public function iAmAnUnauthenticatedUser()
    {
        $this->response = $this->client->request(
            'GET',
            'http://localhost:9200/api/doc'
        );

        $responseCode = $this->response->getStatusCode();

        if($responseCode != 200) {
            throw new PendingException('Not able to access main page!');
        }

        return true;
    }

    /**
     * @When I request a list of categories from :uri
     */
    public function iRequestAListOfCategoriesFrom($uri)
    {
        $this->response = $this->client->request(
            'GET',
            $uri
        );

        $responseCode = $this->response->getStatusCode();

        if($responseCode != 200) {
            throw new PendingException("Not able to access $uri!");
        }

        return true;
    }

    /**
     * @Then The results should include a category with name :name
     */
    public function theResultsShouldIncludeACategoryWithName($name)
    {
        $categories = json_decode($this->response->getContent(), true);

        foreach ($categories as $category) {
            if($category['name'] == $name) {
                return true;
            }
        }

        throw new PendingException("Expected to find category '" . $name . "'. But didn't!");
    }
}
