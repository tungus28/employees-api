Feature: List of categories
  Scenario: I want a list of categories
    Given I am an unauthenticated user
    When I request a list of categories from "http://localhost:80/api/categories"
    Then The results should include a category with name "Board"

