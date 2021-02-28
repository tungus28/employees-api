Feature: Create a new employee
  Scenario: I want a new employee to be created
    Given I am an authenticated user
    Given I have the payload:
    """
    {
      "firstName": "Developer",
      "lastName": "Test last name",
      "email": "test@test.com",
      "category": {
        "name": "Development"
      },
      "parentEmail": "head.b@example.com"
    }
    """
    When I request POST "http://localhost:9200/api/employees"
    Then the response status code should be 201
