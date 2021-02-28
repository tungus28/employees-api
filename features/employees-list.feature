Feature: List of employees
  Scenario: I want a list of employees
    Given I am an unauthenticated user
    When I request a list of employees from "http://localhost:9200/api/employees"
    Then The results should include an employee with First name "Head" and Last name "A" and Subordinates count "5"

