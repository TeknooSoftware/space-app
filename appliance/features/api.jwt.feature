@api
Feature: API endpoints to create and refresh new JWT token
  In order to manage api authentication
  As a user of an account
  I want to manage refresh my jwt token

  Background:
    Given a Space app instance

  Scenario: From the API, generate a jwt token in the past and test to connect with expired token
    Given the time goes back 800 days
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the time passes by 800 days
    When the API is called to get user's settings
    Then get a JSON response
    And an 401 error about "Expired JWT Token"

  Scenario: From the API, create a new jwt token via API with a form url encoded body
    Given a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"
    When the API is called to get a new JWT token
    Then get a JSON response
    And a new JWT token is returned
    When the API client switch to new JWT token
    And the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, create a new jwt token via API via a request with a json body
    Given a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"
    When the API is called to get a new JWT token with a json body
    Then get a JSON response
    And a new JWT token is returned
    When the API client switch to new JWT token
    And the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"
