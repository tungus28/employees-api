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
class EmployeesContext implements Context
{
    private $client;
    private $response;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @When I request a list of employees from :uri
     */
    public function iRequestAListOfEmployeesFrom($uri)
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
     * @Then The results should include an employee with First name :firstName and Last name :lastName and Subordinates count :subordinatesCount
     */
    public function theResultsShouldIncludeAnEmployeeWithFirstNameAndLastNameAndSubordinatesCount2($firstName, $lastName, $subordinatesCount)
    {
        $employees = json_decode($this->response->getContent(), true);


        foreach ($employees as $employee) {
            if($employee['firstName'] == $firstName
                && $employee['lastName'] == $lastName
                && $employee['subordinatesCount'] == $subordinatesCount
            ) {
                return true;
            }
        }

        throw new PendingException("Expected to find employee '" . $firstName . " " . $lastName  . "' with "
            . $subordinatesCount . " subordinates. But didn't!");
    }


}
